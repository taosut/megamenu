<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 3/18/2016
 * Time: 12:56 AM
 */

class Ved_Checkout_Block_Footercart extends Mage_Catalog_Block_Product_Abstract {

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/view/footer_cart.phtml');
    }
}