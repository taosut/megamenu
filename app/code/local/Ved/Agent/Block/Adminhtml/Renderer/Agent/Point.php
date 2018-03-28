<?php

class Ved_Agent_Block_Adminhtml_Renderer_Agent_Point extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $type = $row->getType();
        $color = 'green';

        if ($type == Ved_Agent_Model_Agentachievementhistory::REDEMPTION || $type == Ved_Agent_Model_Agentachievementhistory::OTHER_SUB) {
            $color = 'red';
        }

        return '<span style="color: '.$color.'">'.$value.'</span>';
    }
}
