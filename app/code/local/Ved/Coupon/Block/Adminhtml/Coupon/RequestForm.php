<?php

class Ved_Coupon_Block_Adminhtml_Coupon_RequestForm extends  Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_headerText = "Test thoi i ma ";
        $this->_blockGroup = 'Coupon';
        $this->_controller = "adminhtml_coupon";
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        $this->_removeButton('add');
        return parent::_prepareLayout();
    }
}