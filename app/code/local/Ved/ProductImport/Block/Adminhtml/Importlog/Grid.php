<?php

class Ved_ProductImport_Block_Adminhtml_Importlog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("importlogGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("productimport/importlog")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("productimport")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("filename", array(
				"header" => Mage::helper("productimport")->__("Filename"),
				"index" => "filename",
				));
				$this->addColumn("size", array(
				"header" => Mage::helper("productimport")->__("Size"),
				"index" => "size",
				));
				$this->addColumn("imported", array(
				"header" => Mage::helper("productimport")->__("Imported"),
				"index" => "imported",
				));
				$this->addColumn("image_error", array(
				"header" => Mage::helper("productimport")->__("Image error"),
				"index" => "image_error",
				));
				$this->addColumn("failed", array(
				"header" => Mage::helper("productimport")->__("Failed"),
				"index" => "failed",
				));
				$this->addColumn("imported_user", array(
				"header" => Mage::helper("productimport")->__("Imported User"),
				"index" => "imported_user",
				));
				$this->addColumn("imported_date", array(
				"header" => Mage::helper("productimport")->__("Imported Date"),
				"index" => "imported_date",
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return '#';
		}


		

}