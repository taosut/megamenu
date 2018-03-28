<?php


class Ved_ProductImport_Block_Adminhtml_Importlog extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_importlog";
	$this->_blockGroup = "productimport";
	$this->_headerText = Mage::helper("productimport")->__("Importlog Manager");
	$this->_addButtonLabel = Mage::helper("productimport")->__("Add New Item");
	parent::__construct();
	$this->_removeButton('add');
	}

}