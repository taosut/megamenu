<?php

class TEKShop_PaymentOnline_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('TEKShop_PaymentOnline/log', 'id');
    }
}