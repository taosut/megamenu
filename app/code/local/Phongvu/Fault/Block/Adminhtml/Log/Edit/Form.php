<?php
/**
 * @author hoangpt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Block_Adminhtml_Log_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		//create form structure
		$form = new Varien_Data_Form(
			array(
				  'id'     => 'edit_form',
				  'action' => $this->getUrl('*/*/save',
						  array('id' => $this->getRequest()->getParam('id'))
					  ),
				  'method' => 'post',
			 )
		);

		$form->setUseContainer(true);
		$this->setForm($form);

		$hlp   = Mage::helper('pvfault');
		$model = Mage::registry('pvfault_log');

		$fldMain = $form->addFieldset('main', array('legend' => $hlp->__('General Information')));

		$fldMain->addField('store', 'note',
			array(
				'label' => $hlp->__('Store'),
				'name'  => 'store',
				'escape' => false
			)
		);

		$fldMain->addField('url', 'label',
			array(
				'label' => $hlp->__('From'),
				'name'  => 'url',
		   )
		);

		$fldMain->addField('page', 'text',
			array(
				'label'    => $hlp->__('To'),
				'name'     => 'page',
				'required' => true,
				'note'     => 'e.g. the-right-page.html',
		   )
		);

		//set form values
		$data = Mage::getSingleton('adminhtml/session')->getFormData();
		if ($data) {
			$form->setValues($data);
			Mage::getSingleton('adminhtml/session')->setFormData(null);
		} elseif ($model) {
			$form->setValues($model->getData());
		}

		$form->getElement('store')->setText($this->_getStoreValue());

		return parent::_prepareForm();
	}

	protected function _getStoreValue()
	{
		$out = '';
		$origStores = array(Mage::registry('pvfault_log')->getStoreId());
		$data = Mage::getSingleton('adminhtml/system_store')->getStoresStructure(false, $origStores);

		foreach ($data as $website) {
			$out .= $website['label'] . '<br/>';
			foreach ($website['children'] as $group) {
				$out .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
				foreach ($group['children'] as $store) {
					$out .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
				}
			}
		}

		return $out;
	}

}