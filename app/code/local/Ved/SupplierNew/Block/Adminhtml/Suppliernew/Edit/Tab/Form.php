<?php

class Ved_SupplierNew_Block_Adminhtml_Suppliernew_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
//    $form = new Varien_Data_Form();
//    $this->setForm($form);
//    $fieldset = $form->addFieldset("supplier_form", array("legend" => Mage::helper("supplier")->__("Supplier information")));
//
//
//    $fieldset->addField("supplier_code", "text", array(
//      "label" => Mage::helper("supplier")->__("Code"),
//      "class" => "required-entry",
//      "required" => true,
//      "name" => "supplier_code",
//    ));
//
//    $fieldset->addField("supplier_name", "text", array(
//      "label" => Mage::helper("supplier")->__("Name"),
//      "class" => "required-entry",
//      "required" => true,
//      "name" => "supplier_name",
//    ));
//
//    $fieldset->addField("supplier_mobile", "text", array(
//      "label" => Mage::helper("supplier")->__("Mobile"),
//      "name" => "supplier_mobile",
//    ));
//
//    $fieldset->addField("supplier_address", "textarea", array(
//      "label" => Mage::helper("supplier")->__("Address"),
//      "name" => "supplier_address",
//    ));
//
//    $fieldset->addField("supplier_contact", "text", array(
//      "label" => Mage::helper("supplier")->__("Contact Name"),
//      "name" => "supplier_contact",
//    ));
//
//    $fieldset->addField("supplier_province", "text", array(
//      "label" => Mage::helper("supplier")->__("Province"),
//      "name" => "supplier_province",
//    ));
//
//    $fieldset->addField("supplier_district", "text", array(
//      "label" => Mage::helper("supplier")->__("District"),
//      "name" => "supplier_district",
//    ));
//
//    $fieldset->addField("note", "textarea", array(
//      "label" => Mage::helper("supplier")->__("Note"),
//      "name" => "note",
//    ));
//
//
//    if (Mage::getSingleton("adminhtml/session")->getSupplierData()) {
//      $form->setValues(Mage::getSingleton("adminhtml/session")->getSupplierData());
//      Mage::getSingleton("adminhtml/session")->setSupplierData(null);
//    } elseif (Mage::registry("supplier_data")) {
//      $form->setValues(Mage::registry("supplier_data")->getData());
//    }
    return parent::_prepareForm();
  }
}
