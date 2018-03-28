<?php
class Ved_ProductImport_Helper_Data extends Mage_Core_Helper_Abstract
{

  public function getAttributeSet(){
    $attrSet = array();
    $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')
      ->setEntityTypeFilter('4')->load();
    foreach ($attributeSetCollection as $id=>$attributeSet) {
      $attrSet[] = array("id"=>$attributeSet->getId(),"name"=>$attributeSet->getAttributeSetName());
    }
    return $attrSet;
  }
  //get all attribute set & attribute of product
  public function getAttribute($setId){
    $attr = array();
    $attr[] = array("code"=>"entity_id","name"=>"Entity ID","unique"=>1);
    $attributes = Mage::getModel('catalog/product')->getResource()
      ->loadAllAttributes()
      ->getSortedAttributes($setId);
    foreach ($attributes as $attribute) {
      if ($attribute->getId() && $attribute->getFrontendLabel()) {
        $attr[] = array("code"=>$attribute->getAttributeCode(),"name"=>$attribute->getFrontendLabel(),
          "unique"=>$attribute->getIsUnique());
      }
    }
    usort($attr,array($this,"sortByName"));
    return $attr;
  }

  //get all category tree
  public function getCategories(){
    $cats = array();
    $categories = Mage::getModel('catalog/category')->getCollection()
      ->addAttributeToSelect('id')
      ->addAttributeToSelect('name')
      ->addAttributeToSelect('parent_id')
      ->addAttributeToSelect('is_active')
      ->setOrder("path");
    foreach ($categories as $cat){
      if($cat->level == 0) continue;
      $name = "";
      for($i=1;$i<$cat->getLevel();$i++){
        $name .= "--";
      }
      $name .= $cat->getIsActive()?"":"(".$this->__("Inactive").") ";
      $name .= $cat->getName();

      $cats[] = array("id"=>$cat->getId(),"parent_id"=>$cat->getParentId(),"name"=>$name);
    }
    return $cats;
  }

  public function getSheetNames($file){
    $objPHPExcel = $this->readExcelFile($file);
    $sheets = array();
    foreach ($objPHPExcel->getAllSheets() as $index=>$sheet) {
      $sheets[] = array("id"=>$index,"name"=>$sheet->getTitle());
    }
    return $sheets;
  }

  public function getSheetData($file,$sheet,$ignore){
    $objPHPExcel = $this->readExcelFile($file);
    $activeSheet = $objPHPExcel->setActiveSheetIndex($sheet);
    $highestCol = $activeSheet->getHighestColumn();
    $header = $activeSheet->rangeToArray("A1:$highestCol"."1","" ,true, true, true);
    $data = $activeSheet->rangeToArray("A".($ignore+1).":".$highestCol.($ignore+10),"", true, true, true);
    return array("row_num"=>$activeSheet->getHighestRow(),"headers"=>$header,"sample_data"=>$data);
  }

  public function importData($input){
    $objPHPExcel = $this->readExcelFile($input->file);
    $activeSheet = $objPHPExcel->setActiveSheetIndex($input->sheet);
    $highestCol = $activeSheet->getHighestColumn();
    $highestRow = $activeSheet->getHighestRow();
    if($highestRow<$input->from){
      return array("done"=>1);
    }
    $to = $input->from+9<$highestRow?$input->from+9:$highestRow;
    $rows = $activeSheet->rangeToArray("A".$input->from.":".$highestCol.$to,"", true, true, true);
    $products = array();
    foreach($rows as $rowNo=>$row){
      $row = array_filter($row);
      if(count($row)==0) continue;
      $product = array();
      foreach ($input->attrs as $attr) {
        $code = $attr["matched"];
        $col = $attr["col"];
        $product[$code] = $row[$col];
      }
      $products[$rowNo] = array_filter($product);
    }

    $result = $this->saveProduct($products,$input);
    $result->done = 0;
    $result->total = count($rows);

    return $result;
  }

  public function saveProduct($products,$input){
    $result = (object) array("insert"=>0,"update"=>0,"detail"=>array(),"fail"=>array());
    //ensure option
    $attrs = array();
    Mage::log("Ensure select option" ,null,"import.log",true);
    foreach ($input->attrs as $attr) {
      $code = $attr["matched"];
      $attr = Mage::getModel('catalog/product')->getResource()->getAttribute($code);
      if(!$attr->getId()) continue;

      $attrs[$code] = $attr;
      $inputType = $attr->getFrontendInput();
      if($inputType == "select"){
        //get all option exist
        $temp = $attr->getSource()->getAllOptions(true, true);
        $existOption = array();
        foreach ($temp as $option) {
          $label = trim(strtolower($option["label"]));
          $existOption[$label] = $option["value"];
        }

        //check all value from products
        foreach ($products as $row=>$product) {
          $value = trim(strtolower($product[$code]));
          if(isset($existOption[$value])){
            $products[$row][$code] = $existOption[$value];
          }else{
            //fail, remove this row
            $result->fail[] = "Row $row col ".$this->findColumn($input,$code).": option ".$product[$code]." of attribute $code is not exist";
            unset($products[$row]);
          }
        }
      }else if($inputType == "media_image"){
        foreach ($products as $row=>$product) {
          if(!isset($product[$code])) continue;
          //download image
          $value = $this->downloadImage($product[$code]);
          if($value){
            $products[$row][$code] = $value;
          }else{
            //fail, remove this row
            $result->fail[] = "Row $row col ".$this->findColumn($input,$code).": can not download image ".$product[$code];
            unset($products[$row]);
          }
        }
      }
    }

    $category = Mage::getModel('catalog/category')->load($input->category);
    $categoryIds = $category->getPathIds();
    $stores = Mage::getModel('core/store')->getCollection()->loadByCategoryIds($categoryIds);
    $websiteIds = array_unique($stores->getColumnValues('website_id'));
    Mage::log("Finish gather info" ,null,"import.log",true);

    foreach ($products as $row=>$product) {
      $productModel = Mage::getModel('catalog/product');
      //default value for new product
      $productModel->setStatus(2)
                    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
                    ->setMediaGallery (array('images'=>array (), 'values'=>array ()))
                    ->setTaxClassId(0);
      if($input->updater){
        Mage::log("Row $row find product by $input->updater: ".$product[$input->updater] ,null,"import.log",true);
        //find product by updater
        $collection = Mage::getModel('catalog/product')->getResourceCollection()
          ->addAttributeToSelect('*')
          ->addAttributeToFilter($input->updater, $product[$input->updater])
          ->setPageSize(2);
        if($collection->getSize()>1){
          $result->fail[] = "Row $row col ".$this->findColumn($input,$input->updater).": More than 1 product with value ".$product[$input->updater]." of attribute $input->updater";
        }else if($collection->getSize() == 1){
          $productModel = $collection->getFirstItem();
        }
      }
      $id = $productModel->getId();
      $productModel->setTypeId('simple')
        ->setWebsiteIds($websiteIds)
        ->setCategoryIds(array($input->category))
        ->setAttributeSetId($input->attr_set);

      unset($product["entity_id"]);
      foreach ($product as $code=>$item) {
        $attr = $attrs[$code];
        $inputType = $attr?$attr->getFrontendInput():"";
        //process image
        if($inputType == "media_image"){
          $productModel->addImageToMediaGallery($product[$code],array('image','thumbnail','small_image'), false, false);
        }
        $productModel->setData($code,$product[$code]);
      }

      //set product not visible and disable if it don't have price
      if(!$productModel->getPrice() || !$productModel->getSku()){
        $productModel->setStatus(2)
          ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
      }
      try{

        Mage::log("Row $row saving product $id" ,null,"import.log",true);
        $productModel->save();
        Mage::log("Row $row saved product ".$productModel->getId() ,null,"import.log",true);
        if($id) {
          $result->update++;
          $result->detail[] = "Row $row: Update product with id $id";
        }else {
          $result->insert++;
          $result->detail[] = "Row $row: Insert new product with id ".$productModel->getId();
        }
      }catch(Exception $e){
        $result->fail[] = "Row $row : Can not save product $productModel->getId() because ".$e->getMessage()." Error code ".$e->getCode();
      }
    }
    return $result;
  }

  private function findColumn($input,$code){
    foreach ($input->attrs as $attr) {
      if($code == $attr["matched"]){
        return $attr["col"];
      }
    }
    return "Unknown";
  }

  private function readExcelFile($file){
    $includePath = Mage::getBaseDir(). "/lib/productimport/";
    set_include_path(get_include_path() . PS . $includePath. PS . $includePath. 'PhpExcel/');
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    return $objReader->load($file);
  }

  private function sortByName($arr1,$arr2){
    return strcmp($arr1["name"],$arr2["name"]);
  }

  private function downloadImage($url){
    $path = pathinfo($url);
    if(!in_array($path["extension"],array("png","jpg","gif","jpeg")))
      return false;
    if(substr($url,0,4) != 'http'){
      $url = Mage::getBaseUrl()+$url;
    }
    //create folder
    $mediaDir = Mage::getBaseDir().DS;
    $folderPath = "media/catalog/product/import/".date("Ymd")."/";
    $imagePath = $folderPath.$path["filename"].date("His").".".$path["extension"];
    if(@is_dir($mediaDir.$folderPath) || @mkdir($mediaDir.$folderPath,0777,true)){
        chmod($mediaDir.$folderPath,0777);
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $raw=curl_exec($ch);
        curl_close ($ch);
        file_put_contents($mediaDir.$imagePath,$raw);
        Mage::log("Download $url save to $mediaDir$imagePath return $imagePath" ,null,"import.log",true);
        return $imagePath;
    }
    return false;
  }
}
	 