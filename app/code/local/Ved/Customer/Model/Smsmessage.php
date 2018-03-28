<?php

class Ved_Customer_Model_Smsmessage extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init("ved_customer/smsmessage");
    }
}