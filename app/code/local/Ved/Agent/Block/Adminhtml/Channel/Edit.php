<?php

class Ved_Agent_Block_Adminhtml_Channel_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_channel';

        $this->_updateButton('delete', 'label', Mage::helper('agent')->__('Delete Channel'));
    }

    public function getHeaderText()
    {
        if (Mage::registry('channel_data') && Mage::registry('channel_data')->getId()) {
            return Mage::helper('agent')->__('Edit Channel #%s', Mage::registry('channel_data')->getId());
        } else {
            return Mage::helper('agent')->__('Add New Channel');
        }
    }
}
