<?php

class Ved_AdminApi_Model_Resource_Log extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('ved_adminapi/log', 'id');
    }
}