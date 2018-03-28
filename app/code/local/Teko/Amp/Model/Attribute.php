<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 5/11/2017
 * Time: 4:54 PM
 */
class Teko_Amp_Model_Attribute extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('teko_amp/attribute');
    }

    public function getAttribute($name)
    {
        return $this->getCollection()
            ->addFieldToFilter('entity_type_id', 4)
            ->addFieldToFilter('attribute_code', $name)
            ->getFirstItem()->getAttributeId();
    }
}