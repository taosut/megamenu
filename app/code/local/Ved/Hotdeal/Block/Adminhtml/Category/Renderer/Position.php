<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 3/31/2017
 * Time: 3:59 PM
 */

class Ved_Hotdeal_Block_Adminhtml_Category_Renderer_Position   extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    /* Render Grid Column*/
    public function render(Varien_Object $row) {
        $position =  $row->getPosition();
        switch ($position) {
            case 1:
                return Mage::helper('cms')->__('Top');
            case 2:
                return Mage::helper('cms')->__('Bottom');
            case 3:
                return Mage::helper('cms')->__('Left');
            default:
                return Mage::helper('cms')->__('Right');
        }
    }
}
