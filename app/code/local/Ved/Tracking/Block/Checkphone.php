<?php

class Ved_Tracking_Block_Checkphone extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Order Detail');
        $this->setTemplate('tracking/order/check.phtml');
    }



}