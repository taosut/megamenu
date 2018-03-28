<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/20/2016
 * Time: 2:08 PM
 */
require_once '../abstract.php';
class Ved_Product_Update extends Mage_Shell_Abstract{
    public function run()
    {
        $this->getAllProductID();
    }
    public function updateProductInfo(){
        print "Load all products.\n";
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*') // select all attributes
            ->setPageSize(5000) // limit number of results returned
            ->setCurPage(1); // set the offset (useful for pagination)

        // we iterate through the list of products to get attribute values
        foreach ($collection as $product) {
            $sku =  $product->getSku();
            $price = $product->getPrice();
            $product->setData('supplier_sku', $sku);
            $product->setData('supplier_price', $price);
            print $product->getSupplier_sku().",".$product->getSupplier_price()."\n";
            $product->save();
        }
    }
    public function getAllProductID(){
        $ids = Mage::getModel('catalog/product')->getCollection()->getAllIds();
        print implode(",",$ids)."\n";
    }
}

$shell = new Ved_Product_Update();
$shell->run();