<?php

class Ved_AdminApi_Model_Resource_Transaction_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ved_adminapi/transaction');
    }
}