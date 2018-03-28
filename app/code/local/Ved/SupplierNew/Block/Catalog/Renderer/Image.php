<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/15/2016
 * Time: 11:00 AM
 */

class Ved_SupplierNew_Block_Catalog_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $mediaurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
//        $data = json_encode($row->getData());
//        return $data;
        $value = $row->getData($this->getColumn()->getIndex());
        $fileurl = $mediaurl.'catalog/product'.$value;
        return '<p style="text-align:center;padding-top:10px;"><img src="'.$fileurl.'"  style="width:100px;height:100px;text-align:center;"/></p>';
    }
} 