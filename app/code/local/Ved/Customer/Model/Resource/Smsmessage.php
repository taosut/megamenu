<?php

class Ved_Customer_Model_Resource_Smsmessage extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init("ved_customer/smsmessage", "entity_id");
    }
}