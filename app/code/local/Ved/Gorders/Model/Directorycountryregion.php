<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 12/7/2016
 * Time: 5:04 PM
 */
class Ved_Gorders_Model_Directorycountryregion extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ved_gorders/directorycountryregion');
    }

    public function loadByAttribute($key, $value)
    {
        return $this->getCollection()
            ->addFieldToFilter($key, $value)
            ->getFirstItem();
    }
}