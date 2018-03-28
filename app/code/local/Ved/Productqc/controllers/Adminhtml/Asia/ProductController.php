<?php

class Ved_Productqc_Adminhtml_Asia_ProductController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/catalog/asia_product');
    }

    public function indexAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Cập nhật giá sản phẩm'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/asia_product');
        $this->renderLayout();
    }


    public function runAction()
    {
        $command = 'php' . ' ' . Teko::getDir('command.php') . ' ' . 'UpdatePriceProductAsia' .
            ' >> ' . Teko::getDir('var/log/log_update_price.log' . ' ' . '&');
        if (!file_exists(Teko::getDir('var/queue/UpdatePriceProductAsia.pid')))
            exec($command);

        $this->getResponse()->setRedirect(Mage::getUrl('*/*/success'));

    }

    public function getAction()
    {
        $command = 'php' . ' ' . Teko::getDir('command.php') . ' ' . 'GetPriceProductAsia' .
            ' > ' . Teko::getDir('var/log/log_update_price.log' . ' ' . '&');
        if (!file_exists(Teko::getDir('var/queue/GetPriceProductAsia.pid')))
            exec($command);

        $this->getResponse()->setRedirect(Mage::getUrl('*/*/success'));

    }

    public function successAction()
    {
        include_once 'templates/Log.php';
    }
}