<?php

class Teko_VAT_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Orders'), $this->__('Orders'));
        return $this;
    }

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId() || !$order->allowEditVat()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        return $order;
    }

    public function vatAction()
    {
        $order = $this->_initOrder();
        if ($order) {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save order affiliate code
     */
    public function vatSaveAction()
    {
        $order = $this->_initOrder();
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $data = $this->getRequest()->getPost();
        if ($data && $order->getId() && $order->allowEditVat()) {
            try {
                $order->addData(array_intersect_key($data, [
                    'is_vat' => "",
                    'vat_name' => "",
                    'vat_id' => "",
                    'vat_address' => '',
                    'vat_address_to' => ''
                ]));
                $order->save();
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The vat info has been updated.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('sales')->__('An error occurred while updating the order vat  code. The v info has not been changed.')
                );
            }
        } else {
            $this->_getSession()->addError('Đơn hàng không được phép sửa VAT');
        }
        $this->_redirect('*/*/view', array('order_id' => $order->getId()));
    }
}
