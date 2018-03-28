<?php

class Ved_Instalment_Model_Resource_Instalment extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('ved_instalment/instalment', 'instalment_id');
    }
}