<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11/28/16
 * Time: 11:30
 */

class Ved_Banner_Block_Adminhtml_Catalog_Renderer_BannerImage extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $image =  $row->getData($this->getColumn()->getIndex());
        $imgFilePath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/banner/' . $image;
        $html = '<ul class="enlarge"> ';
        $html .= '<li><img width="100px" height="100px" src="' . $imgFilePath . '"/> 
            <span><img width="400px" src="' . $imgFilePath . '"/> </span> </li>';
        $html .= '</ul> ';
        return $html;
    }

}