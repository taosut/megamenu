<?php

class Ved_Agent_Block_Adminhtml_Renderer_Agent_AvailablePoint extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        $agentInfo = json_decode($value);

        return (string)$agentInfo->available_point;
    }

}
