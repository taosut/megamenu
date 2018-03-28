<?php

class Ved_SupplierNew_Model_Mysql4_Supplier extends Mage_Core_Model_Mysql4_Abstract
{
  protected function _construct()
  {
    $this->_init("suppliernew/supplier", "supplier_id");
  }
}