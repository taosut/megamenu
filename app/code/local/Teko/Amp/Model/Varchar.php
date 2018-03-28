<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 5/11/2017
 * Time: 4:54 PM
 */
class Teko_Amp_Model_Varchar extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('teko_amp/varchar');
    }

    public function getValueWithKey($key, $entityId)
    {
        return $this->getCollection()
            ->addFieldToFilter('entity_type_id', 4)
            ->addFieldToFilter('store_id', 0)
            ->addFieldToFilter('attribute_id', $key)
            ->addFieldToFilter('entity_id', $entityId)
            ->getFirstItem()
            ->getValue();
    }

    public function getProductName($id)
    {
        return $this->getValueWithKey(Mage::getModel('teko_amp/attribute')->getAttribute('name'), $id);
    }
}