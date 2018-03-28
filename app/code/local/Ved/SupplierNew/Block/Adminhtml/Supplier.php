<?php


class Ved_SupplierNew_Block_Adminhtml_Supplier extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = "adminhtml_suppliernew";
    $this->_blockGroup = "suppliernew";
    $this->_headerText = Mage::helper("suppliernew")->__("Supplier Manager");
    $this->_addButtonLabel = Mage::helper("suppliernew")->__("Add New Supplier");

    parent::__construct();
  }
}