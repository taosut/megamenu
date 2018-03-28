<?php

class Ved_Customer_Block_Sms_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId("HistoryGrid");
        $this->setUseAjax(true);
        $this->setDefaultSort("entity_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("ved_customer/smsmessage")->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("entity_id", array(
            "header" => Mage::helper("ved_customer")->__("ID"),
            "align" => "right",
            "width" => "50px",
            "type" => "number",
            "index" => "entity_id",
        ));

        $this->addColumn("receiver", array(
            "header" => Mage::helper("ved_customer")->__("Receiver"),
            'type' => 'text',
            "index" => "receiver",
            'align' => 'left',
            'width' => '150px',
            'truncate' => 50,
            'escape' => true,
        ));
        $this->addColumn("content", array(
            "header" => Mage::helper("ved_customer")->__("Content"),
            'type' => 'text',
            "index" => "content",
        ));
        $this->addColumn("timetable", array(
            "header" => Mage::helper("ved_customer")->__("Timetable"),
            'type' => 'datetime',
            "index" => "timetable",
        ));
        $this->addColumn("status", array(
            "header" => Mage::helper("ved_customer")->__("Status"),
            'type' => 'options',
            "index" => "status",
            'options' => array(
                'Waiting',
                'Sent'
            ),
        ));
        $this->addColumn("result", array(
            "header" => Mage::helper("ved_customer")->__("Result"),
            'type' => 'text',
            "index" => "result",
        ));
        $this->addColumn("created", array(
            "header" => Mage::helper("ved_customer")->__("Created Date"),
            'type' => 'datetime',
            "index" => "created",
        ));
        $this->addColumn("userid", array(
            "header" => Mage::helper("ved_customer")->__("User"),
            'type' => 'text',
            "index" => "userid",
        ));
//        $this->addExportType('*/*/exportCsv', Mage::helper('suppliernew')->__('CSV'));
//        $this->addExportType('*/*/exportExcel', Mage::helper('suppliernew')->__('Excel'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');
        $this->getMassactionBlock()->setUseSelectAll(true);
//    $this->getMassactionBlock()->addItem('remove_supplier', array(
//      'label' => Mage::helper('supplier')->__('Remove Supplier'),
//      'url' => $this->getUrl('*/adminhtml_supplier/massRemove'),
//      'confirm' => Mage::helper('supplier')->__('Are you sure?')
//    ));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }
}