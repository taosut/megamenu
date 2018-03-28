<?php

class Ved_ProductImport_Adminhtml_ImportlogController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("productimport/importlog")->_addBreadcrumb(Mage::helper("adminhtml")->__("Importlog  Manager"),Mage::helper("adminhtml")->__("Importlog Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("ProductImport"));
			    $this->_title($this->__("Manager Importlog"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("ProductImport"));
				$this->_title($this->__("Importlog"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("productimport/importlog")->load($id);
				if ($model->getId()) {
					Mage::register("importlog_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("productimport/importlog");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Importlog Manager"), Mage::helper("adminhtml")->__("Importlog Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Importlog Description"), Mage::helper("adminhtml")->__("Importlog Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("productimport/adminhtml_importlog_edit"))->_addLeft($this->getLayout()->createBlock("productimport/adminhtml_importlog_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("productimport")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("ProductImport"));
		$this->_title($this->__("Importlog"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("productimport/importlog")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("importlog_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("productimport/importlog");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Importlog Manager"), Mage::helper("adminhtml")->__("Importlog Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Importlog Description"), Mage::helper("adminhtml")->__("Importlog Description"));


		$this->_addContent($this->getLayout()->createBlock("productimport/adminhtml_importlog_edit"))->_addLeft($this->getLayout()->createBlock("productimport/adminhtml_importlog_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("productimport/importlog")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Importlog was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setImportlogData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setImportlogData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("productimport/importlog");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'importlog.csv';
			$grid       = $this->getLayout()->createBlock('productimport/adminhtml_importlog_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'importlog.xml';
			$grid       = $this->getLayout()->createBlock('productimport/adminhtml_importlog_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
