<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 3/18/2016
 * Time: 12:56 AM
 */

class Ved_Checkout_Block_Excart extends Mage_Catalog_Block_Product_Abstract {

    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('QC Product');
        $this->setTemplate('catalog/product/view/ex_cart.phtml');
        $this->itemArray = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
    }

    private $itemArray = array();

    public function getItemArray(){
        return $this->itemArray;
    }

    public function getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }

}