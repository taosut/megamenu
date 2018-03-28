<?php


class Ved_Instalment_Model_Resource_Order_Instalment_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ved_instalment/order_instalment');
    }
}