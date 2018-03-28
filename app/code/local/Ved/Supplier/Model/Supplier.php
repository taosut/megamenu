<?php

class Ved_Supplier_Model_Supplier extends Mage_Core_Model_Abstract
{
  protected function _construct()
  {
    $this->_init("supplier/supplier");
    $read  = Mage::getSingleton( 'core/resource' )-> getConnection( 'core_read' ); // To read from the database
        $write = Mage::getSingleton( 'core/resource' )-> getConnection( 'core_write' ); // To write to the database
        $this->read = $read;
        $this->write = $write;
  }
  
  public function saveDistrictforSupplierDistribution($data){
    $query = "INSERT INTO supplier_by_district(suplier_id, city_id, created_at, updated_at, createdby) 
              VALUE ('{$data['suplier_id']}', '{$data['city_id']}', NOW(), NOW(),'{$data['createdby']}')" ;
    $result = $this->read->query( $query );
    return true;       
  }
  
  public function removeDistrictOfSupplierCurrently($supplier_id){
     $query = "DELETE FROM supplier_by_district WHERE suplier_id = '$supplier_id'" ;
    $result = $this->read->query( $query );
    return $result;       
  }
}