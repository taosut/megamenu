<?php

class Ved_SupplierNew_Model_Pricechange extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init("suppliernew/pricechange");
//        $read = Mage::getSingleton('core/resource')->getConnection('core_read'); // To read from the database
//        $write = Mage::getSingleton('core/resource')->getConnection('core_write'); // To write to the database
//        $this->read = $read;
//        $this->write = $write;
    }
}