<?php

class Ved_Supplier_Model_Mysql4_Supplier extends Mage_Core_Model_Mysql4_Abstract
{
  protected function _construct()
  {
    $this->_init("supplier/supplier", "supplier_id");
  }
}