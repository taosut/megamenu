<?php
class Ved_Agent_Block_Adminhtml_Account extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('agent')->__('Agent Account List');

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_account';

        $this->_removeButton('add');
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
