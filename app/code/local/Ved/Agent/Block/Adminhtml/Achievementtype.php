<?php
class Ved_Agent_Block_Adminhtml_Achievementtype extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('agent')->__('Agent Achievement Types List');

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_achievementtype';
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
