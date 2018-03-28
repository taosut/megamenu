<?php

class Ved_Stockrequest_Adminhtml_UserstockrequestController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Product list page
     */
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Danh sách yêu cầu hàng từ người dùng'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/catalog');
        $this->_addContent($this->getLayout()->createBlock('ved_stockrequest/adminhtml_catalog_stockrequest'));
        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_stockrequest/adminhtml_catalog_stockrequest_grid')->toHtml()
        );
    }

}