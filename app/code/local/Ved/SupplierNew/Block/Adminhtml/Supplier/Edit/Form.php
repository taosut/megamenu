<?php

class Ved_Supplier_Block_Adminhtml_Supplier_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

//  public function getCitiesUrl()
//  {
//    return $this->getUrl('*/*/provinceCity');
//  }

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
    //var_dump($data);
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
    $i = 0;
    foreach ($dataRegions as $key => $value) {
      $arrRegions[$value['default_name']] = $value['default_name'];
      $arrIdRegions[$value['default_name']] = $value['region_id'];
      $arrCities_distribution[$i]['value'] = $value['region_id'];
      $arrCities_distribution[$i]['label'] = $value['default_name'];
      $i++;
    }
    $collator = new Collator('vi_VN');
    $collator->sort($arrRegions);

    $fieldset->addField('supplier_province', 'select', array(
      'name'     => 'supplier_province',
      "label" => Mage::helper("supplier")->__("Province"),
      "label" => Mage::helper("supplier")->__("Province"),
      'values'   => array_combine($arrRegions, $arrRegions),
      "required" => true,
      'onchange' => 'supplierDistrict.reloadCityField(this)'
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
      
      $data['districts'] = array();
      if(isset($data['supplier_id'])){
        $query = 'SELECT city_id FROM '.$resource->getTableName('supplier_by_district')." WHERE suplier_id = ".$data['supplier_id'];
        $dataCitiesBySupplier = $readConnection->fetchAll($query);
        foreach($dataCitiesBySupplier as $key=>$value){
           $data['districts'][] = $value['city_id'];
        }
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

    $fieldset->addField('districts', 'multiselect', array(
          'name' => 'districts',
          'label' => Mage::helper('cms')->__('Distribution for districts'),
          'title' => Mage::helper('cms')->__('Distribution for districts'),
          'required' => true,
          'values' => $arrCities_distribution,
          'value' => $data['districts'],
)); 

//$fieldset->addField('multiselect2', 'multiselect', array(
//          'label'     => 'Select Type2',
//          'class'     => 'required-entry',
//          'required'  => true,
//          'name'      => 'title',
//          'onclick' => "return false;",
//          'onchange' => "return false;",
//          'value'  => '4',
//          'values' => array(
//                                '-1'=> array( 'label' => 'Please Select..', 'value' => '-1'),
//                                '1' => array(
//                                                'value'=> array(array('value'=>'2' , 'label' => 'Option2') , array('value'=>'3' , 'label' =>'Option3') ),
//                                                'label' => 'Size'    
//                                           ),
//                                '2' => array(
//                                                'value'=> array(array('value'=>'4' , 'label' => 'Option4') , array('value'=>'5' , 'label' =>'Option5') ),
//                                                'label' => 'Color'   
//                                           ),                                         
//                                  
//                           ),
//          'disabled' => false,
//          'readonly' => false,
//          'after_element_html' => '<small>Comments</small>',
//          'tabindex' => 1
//        ));
        


    if($data) {
      $form->setValues($data);
    }

    $fieldset->addField('supplier_district_select', 'hidden', array(
      'name'  => 'supplier_district_select',
      'value' => isset($data['supplier_district']) ? $data['supplier_district'] : ''
    ));

    return parent::_prepareForm();
  }
}
