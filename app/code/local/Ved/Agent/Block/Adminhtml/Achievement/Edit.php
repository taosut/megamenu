<?php

class Ved_Agent_Block_Adminhtml_Achievement_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_achievement';

        $this->_updateButton('delete', 'label', Mage::helper('agent')->__('Delete Achievement'));
        $this->_removeButton('save');

        // Add update status buttons
        $achievementId = $this->getRequest()->getParam('id');
        $achievement = Mage::getModel('Ved_Agent/agentachievement')->load($achievementId);

        if ($achievement->getStatus() != Ved_Agent_Model_Agentachievement::VERIFIED) {
            $this->_addButton('verify', array(
                'label' => Mage::helper('agent')->__('Verify'),
                'onclick' => 'verifyAndSave()',
            ));
            $this->_addButton('decline', array(
                'label' => Mage::helper('agent')->__('Decline'),
                'onclick' => 'declineAndSave()',
                'disabled' => $achievement->getStatus() == Ved_Agent_Model_Agentachievement::DECLINED
            ));
            $this->_addButton('require_update', array(
                'label' => Mage::helper('agent')->__('Require Update'),
                'onclick' => 'requireUpdateAndSave()',
                'disabled' => $achievement->getStatus() == Ved_Agent_Model_Agentachievement::REQUIRE_TO_UPDATE
            ));
        }

        $this->_addButton('save', array(
            'label' => Mage::helper('agent')->__('Save'),
            'onclick' => 'nothingAndSave()',
            'class' => 'save'
        ));

    }

    public function getHeaderText()
    {
        if (Mage::registry('agent_data') && Mage::registry('agent_data')->getId()) {
            return Mage::helper('agent')->__('Edit Achievement #%s', Mage::registry('agent_data')->getId());
        }

        return false;
    }
}
