<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 3/25/2016
 * Time: 2:50 PM
 */

class Mage_Adminhtml_Block_Catalog_Product_Renderer_Location extends  Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    public function render(Varien_Object $row){

        $locations = explode(',',$row->getLocation());
        $html = "";
        if(count($locations)>0){
            $html ="<ul>";

            $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'location');

            if ($attribute->usesSource()) {

                $options = $attribute->getSource()->getAllOptions(false);

                foreach($locations as $value) {
                    foreach($options as $option) {
                        // checking if already exists
                        if ($option['value'] == $value) {
                            $html .= "<li>".$option['label']."</li>";
                        }
                    }
                }
            }
            $html .= "</ul>";
        }
      
        return $html ;
    }
}