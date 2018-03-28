<?php
class Ved_Agent_Block_Adminhtml_Gift_Promo extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('agent')->__('Add Shopping Cart Price Rules To Remdemption Gift');

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_gift_promo';

        $this->_removeButton('add');
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
