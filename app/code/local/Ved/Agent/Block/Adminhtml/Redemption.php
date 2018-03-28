<?php
class Ved_Agent_Block_Adminhtml_Redemption extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('agent')->__('Redemption History List');

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_redemption';

        $this->_removeButton('add');
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
