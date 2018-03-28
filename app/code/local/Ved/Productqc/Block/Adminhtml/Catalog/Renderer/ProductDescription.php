<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11/28/16
 * Time: 11:30
 */

class Ved_Productqc_Block_Adminhtml_Catalog_Renderer_ProductDescription extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $productid =  $row->getData($this->getColumn()->getIndex());
        $product = Mage::getModel('catalog/product')->load($productid);
        $html =  $product->getDescription();
        return $html;


    }

}