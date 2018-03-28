<?php

class Ved_Agent_Block_Adminhtml_Renderer_Datetime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        return $value;
    }

}
