<?php

class TEKShop_PaymentOnline_Model_Resource_Transaction_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('TEKShop_PaymentOnline/transaction', 'id');
    }
}