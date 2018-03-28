<?php

class Teko_Amp_Adminhtml_Amp_ConfigController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function updateAction()
    {
        $input = $this->getRequest()->get('config');
        foreach ($input as $key => $value) {
            Mage::getModel('teko_amp/config')
                ->load($key)
                ->setData('status', $value)
                ->save();
        }
        Mage::unregister('amp_config');
        $this->_getSession()->addSuccess(Mage::helper('sales')->__('The config has been updated.'));
        $this->_redirect('*/*/index');
    }
}