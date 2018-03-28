<?php

class Ved_SupplierNew_Model_Mysql4_Pricechange extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init("suppliernew/pricechange","entity_id");
    }
}