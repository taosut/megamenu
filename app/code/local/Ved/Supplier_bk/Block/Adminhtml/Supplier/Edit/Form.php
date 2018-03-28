<?php

class Ved_Supplier_Block_Adminhtml_Supplier_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
    $form = new Varien_Data_Form(array(
        "id" => "edit_form",
        "action" => $this->getUrl("*/*/save", array("id" => $this->getRequest()->getParam("id"))),
        "method" => "post",
        "enctype" => "multipart/form-data",
      )
    );
    $form->setUseContainer(true);
    $this->setForm($form);

    $data = null;
    if (Mage::getSingleton("adminhtml/session")->getSupplierData()) {
      $data = Mage::getSingleton("adminhtml/session")->getSupplierData();
      Mage::getSingleton("adminhtml/session")->setSupplierData(null);
    } elseif (Mage::registry("supplier_data")) {
      $data = Mage::registry("supplier_data")->getData();
    }

    $fieldset = $form->addFieldset("supplier_form", array("legend" => Mage::helper("supplier")->__("Supplier information")));

    $fieldset->addField("supplier_code", "text", array(
      "label" => Mage::helper("supplier")->__("Supplier Code"),
      "class" => "required-entry",
      "required" => true,
      "name" => "supplier_code",
    ));

    $fieldset->addField("supplier_name", "text", array(
      "label" => Mage::helper("supplier")->__("Supplier Name"),
      "class" => "required-entry",
      "required" => true,
      "name" => "supplier_name",
      "disabled" => ($data && isset($data['supplier_id'])) ? "disabled" : ""
    ));

    $fieldset->addField("supplier_mobile", "text", array(
      "label" => Mage::helper("supplier")->__("Mobile"),
      "name" => "supplier_mobile",
      "required" => true,
    ));

    $fieldset->addField("supplier_contact", "text", array(
      "label" => Mage::helper("supplier")->__("Contact Name"),
      "name" => "supplier_contact",
      "required" => true,
    ));

    $dataRegions = Mage::getResourceModel('directory/region_collection')->addCountryFilter('VN')->load()->getData();
    $arrRegions = array();
    $arrIdRegions = array();
    foreach ($dataRegions as $key => $value) {
      $arrRegions[$value['default_name']] = $value['default_name'];
      $arrIdRegions[$value['default_name']] = $value['region_id'];
    }
    $collator = new Collator('vi_VN');
    $collator->sort($arrRegions);

    $fieldset->addField('supplier_province', 'select', array(
      'name'     => 'supplier_province',
      "label" => Mage::helper("supplier")->__("Province"),
      "label" => Mage::helper("supplier")->__("Province"),
      'values'   => array_combine($arrRegions, $arrRegions),
      "required" => true,
    ));

    if(($data && isset($data['supplier_province']) && isset($arrIdRegions[$data['supplier_province']])) || ($arrRegions && isset($arrRegions[0]) && isset($arrIdRegions[$arrRegions[0]]))) {
      $province = $arrIdRegions[isset($data['supplier_province']) ? $data['supplier_province'] : $arrRegions[0]];
      $resource = Mage::getSingleton('core/resource');
      $readConnection = $resource->getConnection('core_read');
      $query = 'SELECT * FROM '.$resource->getTableName('directory_city')." WHERE region_id = ".$province;
      $dataCities = $readConnection->fetchAll($query);
      $arrCities = array();
      foreach ($dataCities as $key => $value) {
        $arrCities[$value['name']] = $value['name'];
      }
       $collator = new Collator('vi_VN');
      $collator->sort($arrCities);

      $fieldset->addField('supplier_district', 'select', array(
        'name'     => 'supplier_district',
        "label" => Mage::helper("supplier")->__("District"),
        "label" => Mage::helper("supplier")->__("District"),
        'values'   => array_combine($arrCities, $arrCities),
        "required" => true,
      ));

    } else {
      $fieldset->addField("supplier_district", "text", array(
        "label" => Mage::helper("supplier")->__("District"),
        "name" => "supplier_district",
        "required" => true,
      ));
    }

    $fieldset->addField("supplier_address", "textarea", array(
      "label" => Mage::helper("supplier")->__("Address"),
      "name" => "supplier_address",
      "required" => true,
    ));

    $fieldset->addField("note", "textarea", array(
      "label" => Mage::helper("supplier")->__("Note"),
      "name" => "note",
    ));

    if($data) {
      $form->setValues($data);
    }

    return parent::_prepareForm();
  }
}
