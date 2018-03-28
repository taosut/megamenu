<?php
/**
 * @author hoangpt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Adminhtml_Pvfault_LogController extends Phongvu_Fault_Controller_Abstract
{
	protected $_title = 'Not found pages';
	protected $_modelName = 'log';
	protected $_moduleAlias = 'pvfault';

	public function editAction()
	{
		$id    = (int) $this->getRequest()->getParam('id');
		$model = Mage::getModel('pvfault/' . $this->_modelName)->load($id);

		if ($model->hasRedirect()) {
			Mage::getSingleton('adminhtml/session')->addWarning(Mage::helper('pvfault'
				)->__('This url has already have redirect.')
			);
			$this->_redirect('*/*/');

			return;
		}

		if ($id && !$model->getId()) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pvfault')->__('Record does not exist'));
			$this->_redirect('*/*/');

			return;
		}

		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		} else {
			$model->setPage(Mage::helper($this->_moduleAlias)->getUrlPath($model->getUrl()));
		}

		Mage::register('pvfault_' . $this->_modelName, $model);

		$this->loadLayout();
		$this->_setActiveMenu('report/pvfault');
		$this->_addContent($this->getLayout()->createBlock('pvfault/adminhtml_' . $this->_modelName . '_edit'));
		$this->renderLayout();
	}

	public function saveAction()
	{
		$id    = $this->getRequest()->getParam('id', 0);
		$type  = $this->getRequest()->getParam('type', 0);
		$model = Mage::getModel('pvfault/' . $this->_modelName)->load($id);
		$data  = $this->getRequest()->getParams();

		if ($id && $data && $model->getId()) {
			try {
				$page   = $this->getTargetPath($data['page']);
				$idPath = Mage::helper($this->_moduleAlias)->getUrlPath($model->getUrl());

				//$rewrite = Mage::getModel('core/url_rewrite')->load($id);
				$rewrite = Mage::getModel('core/url_rewrite');
				$rewrite->setIdPath('am-' . md5($idPath))
					->setTargetPath($page)
					->setOptions('RP')
					->setStoreId($model->getStoreId())
					->setIsSystem(0)
					->setDescription(Mage::helper('pvfault')->__('Generated for a wrong URL `%s`', $idPath))
					->setRequestPath($model->getRequestPath())
					->save();

				switch ($type) {
					case Phongvu_Fault_Helper_Data::TYPE_THIS:
						$model->delete();
						break;

					default:
						break;
				}

				Mage::getSingleton('adminhtml/session')->setFormData(false);

				$msg = Mage::helper($this->_moduleAlias
				)->__('Redirect has been successfully saved. See Catalog > Url Rewrites');
				Mage::getSingleton('adminhtml/session')->addSuccess($msg);
				$this->_redirect('*/*');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $id));
			}

			return;
		}

		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pvfault'
			)->__('Unable to find a record to save')
		);
		$this->_redirect('*/*');
	}

	private function getTargetPath($url)
	{
		$allStores  = Mage::app()->getStores();
		foreach ($allStores as $k => $v) {
			$code = preg_quote(Mage::app()->getStore($k)->getCode());
			$url = preg_replace("/^$code\//", '', $url);
		}

		return $url;
	}

	public function massDeleteAction()
	{
		$logIds = $this->getRequest()->getParam('log');

		if (!is_array($logIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select logs'));
		} else {
			try {
				foreach ($logIds as $logId) {
					$Log = Mage::getModel($this->_moduleAlias . '/' . $this->_modelName)->load($logId);
					$Log->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($logIds))
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}
}