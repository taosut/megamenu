<?php

class Ved_Instalment_Adminhtml_Instalment_OrderController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Order
     */
    private function _initOrder()
    {
        return Mage::getModel('sales/order')
            ->load($this->getRequest()->getParam('order_id'), 'entity_id');
    }

    /**
     * @return Mage_Core_Model_Abstract|Ved_Instalment_Model_Order_Instalment
     */
    private function _initOrderInstalment()
    {
        return Mage::getModel('ved_instalment/order_instalment')
            ->load($this->getRequest()->getParam('order_id'), 'order_id');
    }

    public function indexAction()
    {
        $this->_title($this->__('Instalment'))->_title($this->__('New Orders'));
        $this->loadLayout();
        $this->_setActiveMenu('instalments/order');
        $this->_addContent($this->getLayout()->createBlock('ved_instalment/adminhtml_instalment_order_create'));
        $this->renderLayout();
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Orders'), $this->__('Orders'));
        return $this;
    }

    /**
     * Edit order affiliate code form
     */
    public function createAction()
    {
        $orderInstalment = $this->_initOrderInstalment();
        $instalment = $this->_initInstalment();
        if ($orderInstalment->getData('id') && (boolean)$instalment->getData('allow_confirm') || 1) {
            Mage::register('orderInstalment', $orderInstalment);
            Mage::register('instalment', $instalment);
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        try {
            $order = $this->_initOrder();
            $orderInstalment = $this->_initOrderInstalment();
            if ($order->getIsInstalment() != '1')
                throw  new Exception("Đơn này không phải đơn trả góp");
            $instalment = $this->_initInstalment();
            $data = $this->getRequest()->getPost();

            if (($data['prepaid_method'] xor $data['prepaid_amount'])) {
                throw  new Exception("Phải chọn cùng lúc phương thức và số tiền trả trước");
            }
            if ($orderInstalment->getId() && (bool)$instalment->getData('allow_confirm') && !$order->getIsSendQueue()) {
                $orderInstalment->addData([
                    'status' => $data['status'],
                    'transaction_id' => $data['transaction_id'],
                    'prepaid_amount' => $data['prepaid_amount'],
                    'fee' => $data['fee'],
                    'description' => $data['description'],
                    'prepaid_method' => $data['prepaid_method'],
                ])->save();
            } else {
                throw new Exception("Đơn hàng không thể xác nhận trả góp");
            }
            $this->_getSession()->addSuccess(Mage::helper('sales')->__('The Instalment info has been updated.'));
            $this->_redirect('*/sales_order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
        } catch
        (Exception $e) {
            $this->_getSession()->addException(
                $e,
                $e->getMessage()
            );
            $this->_redirect('*/instalment_order/create', array('order_id' => $this->getRequest()->getParam('order_id')));
        }

    }

    public function cancelAction()
    {
        try {
            $order = $this->_initOrder();
            $orderInstalment = $this->_initOrderInstalment();
            $instalment = $this->_initInstalment();
            if ($orderInstalment->getId() &&
                (bool)$instalment->getData('allow_confirm') &&
                !$order->getIsSendQueue()
            )
                $order->setIsInstalment(0)->setStatus('pending')->save();
            $this->_getSession()->addSuccess(Mage::helper('sales')->__('The Instalment info has been updated.'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
    }

    private function _initInstalment()
    {
        return Mage::getModel('ved_instalment/instalment')
            ->load($this->_initOrderInstalment()->getData('instalment_id'));
    }

}