<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/15/2016
 * Time: 11:00 AM
 */

class Ved_SupplierNew_Block_Catalog_Renderer_SupplierSKU extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $product_id = $row->getData($this->getColumn()->getIndex());
        $product=Mage::getModel('catalog/product')->load($product_id);
        return $product->getData('supplier_sku');
    }
} 