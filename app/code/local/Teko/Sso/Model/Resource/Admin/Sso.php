<?php

class Teko_Sso_Model_Resource_Admin_Sso extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('teko_sso/admin_sso', 'sso_id');
    }
}