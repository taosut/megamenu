<?php

class Teko_Sso_Model_Resource_Admin_Sso_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Teko_Sso_Model_Resource_Admin_Sso_Collection constructor.
     */
    protected function _construct()
    {
        $this->_init('teko_sso/admin_sso');
    }
}