<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 11/30/2016
 * Time: 4:26 PM
 */
class Ved_UserStoreMapping_Model_Resource_Mapping extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('userstoremapping/mapping', 'id');
    }
}