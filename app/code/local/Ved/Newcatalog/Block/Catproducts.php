<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 11/22/2017
 * Time: 1:59 PM
 */

class Ved_Newcatalog_Block_Catproducts extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/view/cat_products.phtml');
    }
}