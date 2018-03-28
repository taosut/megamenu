<?php

class Ved_Agent_Block_Adminhtml_Info_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_info';

        $this->_updateButton('delete', 'label', Mage::helper('agent')->__('Delete Agent'));
    }

    public function getHeaderText()
    {
        if (Mage::registry('agent_data') && Mage::registry('agent_data')->getId()) {
            return Mage::helper('agent')->__('Edit Agent #%s', Mage::registry('agent_data')->getId());
        } else {
            return Mage::helper('agent')->__('Add New Agent');
        }
    }
}
