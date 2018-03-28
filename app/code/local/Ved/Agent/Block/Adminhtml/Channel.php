<?php
class Ved_Agent_Block_Adminhtml_Channel extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('agent')->__('Agent Channels List');

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_channel';
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
