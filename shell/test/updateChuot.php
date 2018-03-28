<?php
/**
 * Created by PhpStorm.
 * User: tranlinh
 * Date: 9/5/2016
 * Time: 6:16 PM
 */

require_once str_replace('\\', '/', dirname(__FILE__)) . '/../abstract.php';
class Ved_Create_Products  extends Mage_Shell_Abstract
{
    public function run()
    {
        $this->createProducts();
    }
    public function createProducts(){

        $new_db_resource = Mage::getSingleton('core/resource');
        $connection = $new_db_resource->getConnection('test_database');
        $query = 'select `id`, `code`, `category`, `name`, `label`, `manufacture`, `supplier`, `supplier_code`,
  `warranty`, `supplier_price`, `price`, `connection`, `chuotquang`, `dpi`, `colour`, `db`, `tech`, `led`, `cable`,
  `description`, `short_description`, `image1`, `image2`
                        from chuot';
        $products    = $connection->fetchAll($query);
        print "Start Create" . count($products) . " products.\n";
        $update = 0;
        $non_update_test = 0;
        $non_update = 0;
        $already = 0;
        foreach($products as $prod){
            try{

                if($prod['id']){
                    $product = Mage::getModel('catalog/product')->load($prod['id']);
                }
                else{
                    $product = Mage::getModel('catalog/product');
                }

                $attr = $product->getResource()->getAttribute("manufacturer");
                if ($attr->usesSource()) {
                    $manufacturerId = $attr->getSource()->getOptionId($prod['manufacture']);
                }

                $attr = $product->getResource()->getAttribute("supplier");
                if ($attr->usesSource()) {
                    $supplierId = $attr->getSource()->getOptionId($prod['supplier']);
                }

//                $attr = $product->getResource()->getAttribute("keyboard_type");
//                if ($attr->usesSource()) {
//                    $keyboardTypeId = $attr->getSource()->getOptionId($prod['type']);
//                }

                $attr = $product->getResource()->getAttribute("chuotquang");
                if ($attr->usesSource()) {
                    $chuotquangId = $attr->getSource()->getOptionId(trim($prod['chuotquang']));
                }
                

                $attr = $product->getResource()->getAttribute("connection");
                if ($attr->usesSource()) {
                    $connectionId = $attr->getSource()->getOptionId(trim($prod['connection']));
                }
								
								$attr = $product->getResource()->getAttribute("led_chuot");
                if ($attr->usesSource()) {
                    $ledId = $attr->getSource()->getOptionId(trim($prod['led']));
                }
                if($prod['image1']){
                    $image_url  = $prod['image1'];
                    $image_url  = str_replace("https://", "http://", $image_url); // replace https tp http
                    $image_type = substr(strrchr($image_url,"."),1); //find the image extension
                    $filename   = $prod['code'] .'.jpg'; //give a new name, you can modify as per your requirement
                    $filepath   = Mage::getBaseDir('media') . DS . 'import'. DS . $filename; //path for temp storage folder: ./media/import/

                    $curl_handle = curl_init();
                    curl_setopt($curl_handle, CURLOPT_URL,$image_url);
                    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Cirkel');
                    $query = curl_exec($curl_handle);
                    curl_close($curl_handle);

                    // Mage::Log('68. File Path ' . $filepath . ', image url ' . $image_url);

                    file_put_contents($filepath, $query); //store the image from external url to the temp storage folder file_get_contents(trim($image_url))
                    $filepath_to_image1 = $filepath;
                }

                if($prod['image2']){
                    $image_url  = $prod['image2'];
                    $image_url  = str_replace("https://", "http://", $image_url); // replace https tp http
                    $image_type = substr(strrchr($image_url,"."),1); //find the image extension
                    $filename   = $prod['code'] .'.jpg'; //give a new name, you can modify as per your requirement
                    $filepath   = Mage::getBaseDir('media') . DS . 'import'. DS . $filename; //path for temp storage folder: ./media/import/

                    $curl_handle = curl_init();
                    curl_setopt($curl_handle, CURLOPT_URL,$image_url);
                    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Cirkel');
                    $query = curl_exec($curl_handle);
                    curl_close($curl_handle);

                    // Mage::Log('68. File Path ' . $filepath . ', image url ' . $image_url);

                    file_put_contents($filepath, $query); //store the image from external url to the temp storage folder file_get_contents(trim($image_url))
                    $filepath_to_image2 = $filepath;
                }


                $product

//                    ->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
                    ->setAttributeSetId(23) //ID of a attribute set named 'default'
                    ->setTypeId('simple') //product type
                    //->setCreatedAt(strtotime('now')) //product creation time
										->setUpdatedAt(strtotime('now')) //product update time

                    //->setSku($prod['code']) //SKU
                    ->setName($prod['name']) //product name
                    ->setWeight(50)
                   // ->setStatus(1) //product status (1 - enabled, 2 - disabled)
                    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
                    ->setManufacturer($manufacturerId) //manufacturer id
                    ->setSupplier($supplierId)
                    //->setNewsFromDate('26/04/2016') //product set as new from
                    //->setNewsToDate('30/06/2016') //product set as new to
                    ->setWarranty($prod['warranty'])
//                    ->setCountryOfManufacture('AF') //country of manufacture (2-letter country code)

                    //->setPrice((int)$prod['price']) //price in form 11.22
                    //->setCost((int)$prod['supplier_price']) //price in form 11.22
                    //->setSupplierPrice((int)$prod['supplier_price']) //price in form 11.22
 //                   ->setSpecialPrice(00.44) //special price in form 11.22
 //                   ->setSpecialFromDate('06/1/2014') //special price from (MM-DD-YYYY)
 //                   ->setSpecialToDate('06/30/2014') //special price to (MM-DD-YYYY)
 //                   ->setMsrpEnabled(1) //enable MAP
  //                  ->setMsrpDisplayActualPriceType(1) //display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
  //                  ->setMsrp(99.99) //Manufacturer's Suggested Retail Price
                    //->setTaxClassId(0) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)

 //                   ->setMetaTitle('test meta title 2')
 //                   ->setMetaKeyword('test meta keyword 2')
 //                   ->setMetaDescription('test meta description 2')

                   // ->setDescription($prod['short_description'])
                    ->setShortDescription($prod['short_description'])
                    //->setData('keyboard_type',$keyboardTypeId)
                    ->setData('chuotquang',$chuotquangId)
                    ->setData('connection', $connectionId)
										->setData('led_chuot', $ledId)
                    //->setCable($prod['cable'])
                    ->setDpi($prod['dpi'])
                    ->setData('durability',$prod['db'])
                    ->setTechnology($prod['tech'])
                    ->setLed($prod['led'])
                    ->setLabel($prod['label'])
                    ->setMauSac($prod['colour']);


                    // ->addImageToMediaGallery($filepath_to_image, null, false, false) //assigning image, thumb and small image to media gallery
                    //->addImageToMediaGallery(Mage::getBaseDir('media').'/catalog/product/b/a/banphim_roccat_arvo_3.jpg', null, false, false) //assigning image, thumb and small image to media gallery


//                    ->setCategoryIds(array(2, 9)); //assign product to categories
                if(!$prod['id']){
                    if($filepath_to_image1){
                        $product->addImageToMediaGallery($filepath_to_image1, array('image','thumbnail','small_image'), false, false); //assigning image, thumb and small image to media gallery
                }

                    if($filepath_to_image2){
                        $product->addImageToMediaGallery($filepath_to_image2, null, false, false); //assigning image, thumb and small image to media gallery
                    }
                }


                $product->save();
                $update++;

            }catch(Exception $e){
                var_dump($e->getMessage());
                $non_update++;
            }
        }
        print "Save " . $update ." products.\n";
        print "Not save " . $non_update ." products.\n";
        //}
    }

}

$shell = new Ved_Create_Products();
$shell->run();