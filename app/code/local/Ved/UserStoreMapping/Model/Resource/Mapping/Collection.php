<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 11/30/2016
 * Time: 4:40 PM
 */
class Ved_UserStoreMapping_Model_Resource_Mapping_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('userstoremapping/mapping');
    }

    public function delete()
    {
        foreach ($this as $key => $value) {
            $value->delete();

        }

        return $this;
    }
}