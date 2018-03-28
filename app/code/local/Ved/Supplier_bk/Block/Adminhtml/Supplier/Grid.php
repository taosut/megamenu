<?php

class Ved_Supplier_Block_Adminhtml_Supplier_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

  public function __construct()
  {
    parent::__construct();
    $this->setId("supplierGrid");
    $this->setUseAjax(true);
    $this->setDefaultSort("supplier_id");
    $this->setDefaultDir("DESC");
    $this->setSaveParametersInSession(true);
  }

  public function getRowUrl($row)
  {
    return $this->getUrl("*/*/edit", array("id" => $row->getId()));
  }

  public function getAllCity(){
    $dataRegions = Mage::getResourceModel('directory/region_collection')->addCountryFilter('VN')->load()->getData();
    $arrRegions = array();
    foreach ($dataRegions as $key => $value) {
      $arrRegions[$value['default_name']] = $value['default_name'];
    }
    $collator = new Collator('vi_VN');
    $collator->sort($arrRegions);

    return array_combine($arrRegions, $arrRegions);
  }

  protected function _prepareCollection()
  {
    $collection = Mage::getModel("supplier/supplier")->getCollection();

    $this->setCollection($collection);

    return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $this->addColumn("supplier_id", array(
      "header" => Mage::helper("supplier")->__("ID"),
      "align" => "right",
      "width" => "50px",
      "type" => "number",
      "index" => "supplier_id",
    ));

    $this->addColumn("supplier_code", array(
      "header" => Mage::helper("supplier")->__("Supplier Code"),
      "index" => "supplier_code",
    ));
    $this->addColumn("supplier_name", array(
      "header" => Mage::helper("supplier")->__("Supplier Name"),
      "index" => "supplier_name",
    ));
    $this->addColumn("supplier_mobile", array(
      "header" => Mage::helper("supplier")->__("Mobile"),
      "index" => "supplier_mobile",
    ));
    $this->addColumn("supplier_contact", array(
      "header" => Mage::helper("supplier")->__("Contact Name"),
      "index" => "supplier_contact",
    ));
    $this->addColumn("supplier_province", array(
      "header" => Mage::helper("supplier")->__("Province"),
      "index" => "supplier_province",
      'width' => '100',
      'type'=> 'options',
      'options'=> $this->getAllCity()
    ));
    $this->addColumn("supplier_district", array(
      "header" => Mage::helper("supplier")->__("District"),
      "index" => "supplier_district",
    ));
    $this->addColumn('created_at', array(
      'header' => Mage::helper('supplier')->__('Created On'),
      'index' => 'created_at',
      'type' => 'datetime',
      'width' => '100px',
    ));
    $this->addColumn('updated_at', array(
      'header' => Mage::helper('supplier')->__('Updated On'),
      'index' => 'updated_at',
      'type' => 'datetime',
      'width' => '100px',
    ));
    $this->addExportType('*/*/exportCsv', Mage::helper('supplier')->__('CSV'));
    $this->addExportType('*/*/exportExcel', Mage::helper('supplier')->__('Excel'));

    return parent::_prepareColumns();
  }

  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('supplier_id');
    $this->getMassactionBlock()->setFormFieldName('supplier_ids');
    $this->getMassactionBlock()->setUseSelectAll(true);
//    $this->getMassactionBlock()->addItem('remove_supplier', array(
//      'label' => Mage::helper('supplier')->__('Remove Supplier'),
//      'url' => $this->getUrl('*/adminhtml_supplier/massRemove'),
//      'confirm' => Mage::helper('supplier')->__('Are you sure?')
//    ));
    return $this;
  }
}