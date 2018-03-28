<?php
class Ved_Agent_Block_Adminhtml_Gift extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('agent')->__('Redemption Gift List');

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_gift';
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
