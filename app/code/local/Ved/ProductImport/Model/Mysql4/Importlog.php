<?php
class Ved_ProductImport_Model_Mysql4_Importlog extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("productimport/importlog", "id");
    }
}