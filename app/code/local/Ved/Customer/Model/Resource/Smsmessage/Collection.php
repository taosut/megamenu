<?php

class Ved_Customer_Model_Resource_Smsmessage_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init("ved_customer/smsmessage");
    }
}
	 