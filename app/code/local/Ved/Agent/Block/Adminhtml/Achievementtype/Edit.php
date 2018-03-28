<?php

class Ved_Agent_Block_Adminhtml_Achievementtype_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_achievementtype';

        $this->_updateButton('delete', 'label', Mage::helper('agent')->__('Delete Achievement Type'));
    }

    public function getHeaderText()
    {
        if (Mage::registry('achievement_type_data') && Mage::registry('achievement_type_data')->getId()) {
            return Mage::helper('agent')->__('Edit Achievement Type #%s', Mage::registry('achievement_type_data')->getId());
        } else {
            return Mage::helper('agent')->__('Add New Achievement Type');
        }
    }
}
