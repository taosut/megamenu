<?php

class Ved_Gorders_Model_Resource_Order_Source_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Ved_Gorders_Model_Resource_Order_Source_Collection constructor.
     */
    protected function _construct()
    {
        $this->_init('ved_gorders/order_source');
    }
}