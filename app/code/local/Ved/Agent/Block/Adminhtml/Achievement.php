<?php
class Ved_Agent_Block_Adminhtml_Achievement extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $isVerifyView = $this->getRequest()->getParam('verify_achievement');

        // if ($isVerifyView) {
        //     $this->_headerText = Mage::helper('agent')->__('Agent Un-verified Achievements List');
        // } else {
        //     $this->_headerText = Mage::helper('agent')->__('Agent Verified Achievements List');
        // }

        $this->_headerText = Mage::helper('agent')->__('Agent Achievements List');

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_achievement';

        $this->_removeButton('add');
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
