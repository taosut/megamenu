<?php

class Ved_Gorders_Model_Resource_Order_Source extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $object->setData('created_at', now());
        return parent::_beforeSave($object);
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('ved_gorders/order_source', 'id');
    }
}