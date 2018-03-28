<?php

class Ved_Supplier_Block_Adminhtml_Supplier_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
  public function __construct()
  {
    parent::__construct();

//    $this->setDestElementId("edit_form");
//    $this->setTitle(Mage::helper("supplier")->__("Supplier Information"));

    $this->_objectId = "supplier_id";
    $this->_blockGroup = "supplier";
    $this->_controller = "adminhtml_supplier";
    $this->_updateButton("save", "label", Mage::helper("supplier")->__("Save"));
    $this->_updateButton("delete", "label", Mage::helper("supplier")->__("Delete"));
    $this->removeButton("delete");

    $this->_addButton("saveandcontinue", array(
      "label" => Mage::helper("supplier")->__("Save And Continue Edit"),
      "onclick" => "saveAndContinueEdit()",
      "class" => "save",
    ), -100);

    $this->_formScripts[] = "
      function saveAndContinueEdit(){
        editForm.submit($('edit_form').action+'back/edit/');
      }
    ";
  }

  public function getHeaderText()
  {
    if (Mage::registry("supplier_data") && Mage::registry("supplier_data")->getId()) {
      return Mage::helper("supplier")->__("Edit Supplier '%s'", $this->htmlEscape(Mage::registry("supplier_data")->getSupplierName()));
    } else {
      return Mage::helper("supplier")->__("Add Supplier");
    }
  }
}