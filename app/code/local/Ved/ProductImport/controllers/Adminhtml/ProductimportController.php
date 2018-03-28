<?php

class Ved_ProductImport_Adminhtml_ProductimportController extends Mage_Adminhtml_Controller_Action
{
  public function indexAction()
  {
    $this->loadLayout();
    $this->_title($this->__("Import Product from Excel file"));
    $this->renderLayout();
  }
  public function uploadAction(){
    if (isset($_FILES['file']['name'])) {
      try {
        $date = date("Y-m-d");
        $uploader = new Varien_File_Uploader('file');
        $uploader->setAllowedExtensions(explode(",","jpg,jpeg,png,gif,pdf,doc,docx,key,ppt,pptx,pps,ppsx,odt,xls,xlsx,zip,mp3,m4a,ogg,wav,mp4,m4v,mov,wmv,avi,mpg,ogv,3gp,3g2,flv,mkv")); // or pdf or anything
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $path = Mage::getBaseDir('media') . DS."productimport".DS.$date;
        $result = $uploader->save($path, $_FILES['file']['name']);

        $file = Mage::getBaseDir('media') . DS."productimport".DS.$date.DS.$result['file'];
        $helper = Mage::helper("productimport");
        $re = array("result"=>"ok","msg"=>"",
                    "data"=>array(
                      "filename"=>$result['file'],
                      "sheets"=>$helper->getSheetNames($file),
                      "attr_set"=>$helper->getAttributeSet(),
                      "categories"=>$helper->getCategories(),
                    ));
      } catch (Exception $e) {
        $re = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
      }
    }else{
      $re = array("result"=>"error","msg"=>"no upload file");
    }
    echo json_encode($re);
    die();
  }

  public function matchingAction(){
    $file = $this->getRequest()->getParam('file');
    $attr_set = $this->getRequest()->getParam('attr_set');
    $ignore = $this->getRequest()->getParam('ignore');
    $sheet = $this->getRequest()->getParam('sheet');

    //security
    $filePath = Mage::getBaseDir('media') . DS."productimport".DS.date("Y-m-d").DS.basename($file);
    try {
      $helper = Mage::helper("productimport");
      $sheetData = $helper->getSheetData($filePath,$sheet,$ignore);

      $attrs = $helper->getAttribute($attr_set);
      $headers = $sheetData["headers"][1];
      $attrCode = array(); $attrName = array();$unique = array();
      foreach ($attrs as $key=>$attr) {
        $code = trim(strtolower($attr['code']));
        $name = trim(strtolower($attr['name']));
        $attrCode[$code] = $code;
        $attrName[$name] = $code;
        if($attr["unique"]){
          $unique[] = $code;
        }
      }
      //try to match excel column with attribute
      $cols = array();
      foreach ($headers as $colNo=>$header) {
        $name = trim(strtolower($header));
        $col = array("col"=>$colNo,"name"=>$name);
        if(isset($attrCode[$name])){
          $col["matched"] = $attrCode[$name];
        }else if(isset($attrName[$name])){
          $col["matched"] = $attrName[$name];
        }
        $cols[] = $col;
      }

      $re = array("result"=>"ok","msg"=>"",
                  "data"=>array(
                    "row_num" => $sheetData["row_num"],
                    "unique"=>$unique,
                    "cols" => $cols,
                    "attrs" => $attrs,
                  ));
    }catch (Exception $e) {
      $re = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
    }
    echo json_encode($re);
    die();
  }
  public function importingAction(){
    $file = $this->getRequest()->getParam('file');
    $category = $this->getRequest()->getParam('category');
    $sheet = $this->getRequest()->getParam('sheet');
    $updater = $this->getRequest()->getParam('updater');
    $attrs = $this->getRequest()->getParam('attr');
    $attr_set = $this->getRequest()->getParam('attr_set');
    $from = $this->getRequest()->getParam('from');


    $filePath = Mage::getBaseDir('media') . DS."productimport".DS.date("Y-m-d").DS.basename($file);
    $helper = Mage::helper("productimport");
    $input = array("file"=>$filePath,"sheet"=>$sheet,"from"=>$from,"attrs"=>$attrs,"updater"=>$updater,"attr_set"=>$attr_set,"category"=>$category);
    $rs = $helper->importData((object)$input);
    echo json_encode($rs);
    die();
  }
}