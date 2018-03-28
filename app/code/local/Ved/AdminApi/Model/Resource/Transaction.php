<?php

class Ved_AdminApi_Model_Resource_Transaction extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('ved_adminapi/transaction', 'id');
    }
}