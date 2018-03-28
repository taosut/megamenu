<?php


class Ved_Coupon_Model_Resource_Coupon_Request_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ved_coupon/coupon_request');
    }
}