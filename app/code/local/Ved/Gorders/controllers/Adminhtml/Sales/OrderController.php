<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ved_Gorders_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('view', 'index');

    /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Sales');
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
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
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    /**
     * Orders grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders'));
        $this->_initAction();
        $this->loadLayout();
        //  var_dump(Mage::app()->getLayout()->getUpdate()->getHandles());
        //   echo $this->getLayout()->getXmlString();
        //var_dump(array_keys($this->getLayout()->getAllBlocks()));
//        if ($block = $this->getLayout()->getBlock('adminhtml_sales_order.grid')) {
//            echo $block->toHtml();
//        }
        // die();
        $this->renderLayout();

    }

    /**
     * Order grid
     */
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * View order detale
     */
    public function viewAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders'));

        $order = $this->_initOrder();
        if ($order) {
            Mage::helper('ved_gorders')->cloneParent($order->getRelationParentId(), $order->getId());

            $isActionsNotPermitted = $order->getActionFlag(
                Mage_Sales_Model_Order::ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
            );
            if ($isActionsNotPermitted) {
                $this->_getSession()->addError($this->__('You don\'t have permissions to manage this order because of one or more products are not permitted for your website.'));
            }

            $this->_initAction();

            $this->_addContent($this->getLayout()->createBlock('ved_gorders/adminhtml_sales_order_view_items'));

            $this->_title(sprintf("#%s", $order->getRealOrderId()));

            $this->renderLayout();
        }
    }

    /**
     * Notify user
     */
    public function emailAction()
    {
        $userAdmin = Mage::getSingleton('admin/session')->getUser()->getUsername();
        if ($order = $this->_initOrder()) {
            try {
                $order->sendNewOrderEmail();
                $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                    ->getUnnotifiedForInstance($order, Mage_Sales_Model_Order::HISTORY_ENTITY_NAME);
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }
                $order->addStatusHistoryComment('<b>' . $userAdmin . '</b> gửi email<br/>');
                $order->save();
                $this->_getSession()->addSuccess($this->__('The order email has been sent.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Failed to send the order email.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    /**
     * Cancel order
     */
    public function cancelAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                /**
                 * @var Ved_Gorders_Helper_Data $helper
                 */
                $helper = Mage::helper('ved_gorders');
                $helper->cancelOrder($order);
                $order->cancel()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('The order has been cancelled.')
                );
                Mage::dispatchEvent('update_instock_product_order_cancel_after', ['order' => $order]);
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been cancelled.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Hold order
     */
    public function holdAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->hold()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('The order has been put on hold.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order was not put on hold.'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Unhold order
     */
    public function unholdAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->unhold()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('The order has been released from holding status.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order was not unheld.'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Manage payment state
     *
     * Either denies or approves a payment that is in "review" state
     */
    public function reviewPaymentAction()
    {
        try {
            if (!$order = $this->_initOrder()) {
                return;
            }
            $action = $this->getRequest()->getParam('action', '');
            switch ($action) {
                case 'accept':
                    $order->getPayment()->accept();
                    $message = $this->__('The payment has been accepted.');
                    break;
                case 'deny':
                    $order->getPayment()->deny();
                    $message = $this->__('The payment has been denied.');
                    break;
                case 'update':
                    $order->getPayment()
                        ->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_UPDATE, true);
                    $message = $this->__('Payment update has been made.');
                    break;
                default:
                    throw new Exception(sprintf('Action "%s" is not supported.', $action));
            }
            $order->save();
            $this->_getSession()->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Failed to update the payment.'));
            Mage::logException($e);
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    /**
     * Add order comment action
     */
    public function addCommentAction()
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        if ($order = $this->_initOrder()) {
            try {
                $data = $this->getRequest()->getPost('history');
                if ($order->getStatus() == 'canceled') $data['status'] = $order->getStatus();
                if (!$order->getIsSendQueue() && $this->getRequest()->getPost('order_estimate_delivery')) {
                    $date = DateTime::createFromFormat('d/m/Y H:i:s',
                        $this->getRequest()->getPost('order_estimate_delivery'),
                        new DateTimeZone('Asia/Ho_Chi_Minh'));
                    $order->setEstimateDelivery($date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
                }
                $admin_user_session = Mage::getSingleton('admin/session');
                $adminUserName = $admin_user_session->getUser()->getUsername();
                //end
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;
                if ($data['assign_province'] == '') {
                    $order->addStatusHistoryComment('<b>' . $adminUserName . ':</b><br />' . $data['comment'], $data['status'])
                        ->setIsVisibleOnFront($visible)
                        ->setIsCustomerNotified($notify);
                } else {
                    $order->addStatusHistoryComment('<b>' . $adminUserName . ':</b> Chuyển khu vực xử lý sang ' . $data['assign_province'] . '<br />' . $data['comment'], $data['status'])
                        ->setIsVisibleOnFront($visible)
                        ->setIsCustomerNotified($notify);
                    $order->setAssignProvince($data['assign_province']);
                }
                if ($data['status'] == 'telephone_confirm' && !$order->getIsSendQueue()) {
                    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $connection->beginTransaction();
                    try {
                        Mage::dispatchEvent('order_telephone_confirm_before', ['order' => $order]);
                        $connection->commit();
                    } catch (Exception $e) {
                        $connection->rollback();
                        throw $e;
                    }
                }
                $order->save();
                $this->_getSession()->addSuccess("Trạng thái đơn hàng đã được cập nhật.");
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    /**
     * Generate invoices grid for ajax request
     */
    public function invoicesAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_invoices')->toHtml()
        );
    }

    /**
     * Generate shipments grid for ajax request
     */
    public function shipmentsAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_shipments')->toHtml()
        );
    }

    /**
     * Generate creditmemos grid for ajax request
     */
    public function creditmemosAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_creditmemos')->toHtml()
        );
    }

    /**
     * Generate order history for ajax request
     */
    public function commentsHistoryAction()
    {
        $this->_initOrder();
        $html = $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_history')->toHtml();
        /* @var $translate Mage_Core_Model_Translate_Inline */
        $translate = Mage::getModel('core/translate_inline');
        if ($translate->isAllowed()) {
            $translate->processResponseBody($html);
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Cancel selected orders
     */
    public function massCancelAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canCancel()) {
                $order->cancel()
                    ->save();
                $countCancelOrder++;
            } else {
                $countNonCancelOrder++;
            }
        }
        if ($countNonCancelOrder) {
            if ($countCancelOrder) {
                $this->_getSession()->addError($this->__('%s order(s) cannot be canceled', $countNonCancelOrder));
            } else {
                $this->_getSession()->addError($this->__('The order(s) cannot be canceled'));
            }
        }
        if ($countCancelOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been canceled.', $countCancelOrder));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Hold selected orders
     */
    public function massHoldAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countHoldOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canHold()) {
                $order->hold()
                    ->save();
                $countHoldOrder++;
            }
        }

        $countNonHoldOrder = count($orderIds) - $countHoldOrder;

        if ($countNonHoldOrder) {
            if ($countHoldOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not put on hold.', $countNonHoldOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were put on hold.'));
            }
        }
        if ($countHoldOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been put on hold.', $countHoldOrder));
        }

        $this->_redirect('*/*/');
    }

    /**
     * Unhold selected orders
     */
    public function massUnholdAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countUnholdOrder = 0;
        $countNonUnholdOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canUnhold()) {
                $order->unhold()
                    ->save();
                $countUnholdOrder++;
            } else {
                $countNonUnholdOrder++;
            }
        }
        if ($countNonUnholdOrder) {
            if ($countUnholdOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not released from holding status.', $countNonUnholdOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were released from holding status.'));
            }
        }
        if ($countUnholdOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been released from holding status.', $countUnholdOrder));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Change status for selected orders
     */
    public function massStatusAction()
    {

    }

    /**
     * Print documents for selected orders
     */
    public function massPrintAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $document = $this->getRequest()->getPost('document');
    }

    /**
     * Print invoices for selected orders
     */
    public function pdfinvoicesAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($invoices->getSize() > 0) {
                    $flag = true;
                    if (!isset($pdf)) {
                        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'invoice' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Print shipments for selected orders
     */
    public function pdfshipmentsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($shipments->getSize()) {
                    $flag = true;
                    if (!isset($pdf)) {
                        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'packingslip' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Print creditmemos for selected orders
     */
    public function pdfcreditmemosAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($creditmemos->getSize()) {
                    $flag = true;
                    if (!isset($pdf)) {
                        $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                        $pdf->pages = array_merge($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'creditmemo' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Print all documents for selected orders
     */
    public function pdfdocsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($invoices->getSize()) {
                    $flag = true;
                    if (!isset($pdf)) {
                        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge($pdf->pages, $pages->pages);
                    }
                }

                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($shipments->getSize()) {
                    $flag = true;
                    if (!isset($pdf)) {
                        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge($pdf->pages, $pages->pages);
                    }
                }

                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($creditmemos->getSize()) {
                    $flag = true;
                    if (!isset($pdf)) {
                        $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                        $pdf->pages = array_merge($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'docs' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf',
                    $pdf->render(), 'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Atempt to void the order payment
     */
    public function voidPaymentAction()
    {
        if (!$order = $this->_initOrder()) {
            return;
        }
        try {
            $order->getPayment()->void(
                new Varien_Object() // workaround for backwards compatibility
            );
            $order->save();
            $this->_getSession()->addSuccess($this->__('The payment has been voided.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Failed to void the payment.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*/view', array('order_id' => $order->getId()));
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'hold':
                $aclResource = 'sales/order/actions/hold';
                break;
            case 'unhold':
                $aclResource = 'sales/order/actions/unhold';
                break;
            case 'email':
                $aclResource = 'sales/order/actions/email';
                break;
            case 'cancel':
                $aclResource = 'sales/order/actions/cancel';
                break;
            case 'view':
                $aclResource = 'sales/order/actions/view';
                break;
            case 'addcomment':
                $aclResource = 'sales/order/actions/comment';
                break;
            case 'creditmemos':
                $aclResource = 'sales/order/actions/creditmemo';
                break;
            case 'reviewpayment':
                $aclResource = 'sales/order/actions/review_payment';
                break;
            default:
                $aclResource = 'sales/order';
                break;

        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'orders.csv';
        $grid = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName = 'orders.xml';
        $grid = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    /**
     * Order transactions grid ajax action
     *
     */
    public function transactionsAction()
    {
        $this->_initOrder();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Edit order address form
     */
    public function addressAction()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        $address = Mage::getModel('sales/order_address')
            ->getCollection()
            ->addFilter('entity_id', $addressId)
            ->getItemById($addressId);
        if ($address) {
            Mage::register('order_address', $address);
            $this->loadLayout();
            // Do not display VAT validation button on edit order address form
            $addressFormContainer = $this->getLayout()->getBlock('sales_order_address.form.container');
            if ($addressFormContainer) {
                $addressFormContainer->getChild('form')->setDisplayVatValidationButton(false);
            }

            $this->renderLayout();
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save order address
     */
    public function addressSaveAction()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        /**
         * @var Mage_Sales_Model_Order_Address $address
         */
        $address = Mage::getModel('sales/order_address')->load($addressId);
        $data = $this->getRequest()->getPost();
        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                if ($address->getOrder()->getIsSendQueue())
                    throw new Exception('Not allow change order address');
                $address->implodeStreetAddress()
                    ->save();
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The order address has been updated.'));
                $this->_redirect('*/*/view', array('order_id' => $address->getParentId()));
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('sales')->__('An error occurred while updating the order address. The address has not been changed.')
                );
            }
            $this->_redirect('*/*/address', array('address_id' => $address->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }


    public function purchaseAction()
    {
        try {
            $this->loadLayout();
            $this->renderLayout();
        } catch (Exception $e) {
            echo "<pre>";
            var_dump($e->getTraceAsString());
            die;
        }
    }

    public function purchaseWarehouseAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParam('data');

            $product = Mage::getModel('sales/order_item')->load($this->getRequest()->get('product_id'));

            $order = Mage::getModel('sales/order')->load($this->getRequest()->get('order_id'));

            Mage::getSingleton('core/resource')->getConnection('core_write')->beginTransaction();
            try {
//                if (Mage::helper('ved_gorders')->hasPurchase($order, $product)) {
//                    throw new Exception('Error');
//                }

                if (!($data['warehouse']['quantity'] >= 0)) {
                    throw new Exception('Số lượng yêu cầu < 0');
                } else if ($data['warehouse']['quantity'] > 0 && $data['warehouse']['quantity'] > $data['warehouse']['can_order']) {
                    throw new Exception('Tồn kho không đủ hoặc số lượng yêu cầu lớn hơn số lượng trong đơn hàng');
                }
                if (!$data['supplier']['store_id'] && $data['supplier']['quantity'] > 0) {
                    throw new Exception('Bạn chưa chọn kho để nhập hàng từ NCC');
                }
                if (!$data['warehouse']['store_id'] && !$data['supplier']['store_id']) {
                    throw new Exception('Lựa chọn kho giữ/nhập hàng');
                }

                if (isset($data['warehouse']['store_id']) &&
                    $data['warehouse']['store_id'] &&
                    $data['warehouse']['quantity']
                ) {
                    if (isset($data['warehouse']['old_stock_id']) && $data['warehouse']['old_stock_id']) {
                        $this->updateWarehouse($product, $order, $data['warehouse']);
                    } else $this->saveWarehouse($product, $order, $data['warehouse']);
                }
                if (isset($data['supplier']['store_id']) &&
                    $data['supplier']['store_id'] &&
                    $data['supplier']['quantity']
                ) {
                    if (isset($data['supplier']['old_request_id']) && $data['supplier']['old_request_id']) {
                        $this->updateSupplier($product, $order, $data['supplier']);
                    } else $this->saveSupplier($product, $order, $data['supplier']);
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());

                Mage::getSingleton('core/resource')->getConnection('core_write')->rollBack();

                return $this->getResponse()->setRedirect(Mage::helper('core/http')->getHttpReferer());

            }
            Mage::getSingleton('core/resource')->getConnection('core_write')->commit();

            $this->_getSession()->addSuccess(Mage::helper('sales')->__('The order has been updated.'));

            return $this->getResponse()->setRedirect($this->getUrl('*/*/view', array('order_id' => $order->getId())));

        }

        return $this->getResponse()->setRedirect(Mage::helper('core/http')->getHttpReferer());
    }

    public function assignAction()
    {
        try {
            $order = Mage::getModel('sales/order')->load($this->getRequest()->get('order_id'));

            $region = $order->getShippingAddress()->getRegion();

            $store = $this->getStoreForOrder($region);

            if ($store['parent']) {
                $order->setData('assign_province', $store['parent']['default_province']);

                $order->save();
            }
            $this->_getSession()->addSuccess(Mage::helper('sales')->__('The order has been updated.'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());

        }
        return $this->getResponse()->setRedirect(Mage::helper('core/http')->getHttpReferer());
    }

    /**
     * @param Mage_Sales_Model_Order_Item $product
     * @param Mage_Sales_Model_Order $order
     * @param array $data
     * @return void
     * @throws Exception
     */
    private function saveWarehouse($product, $order, $data)
    {
        if (!$order->isExistHoldItem($data['store_id'], $product->getProductId())) {
            throw new Exception('Đơn hàng đã lấy hàng từ kho khác. Nếu muốn lấy hàng vui lòng tách đơn');
        }
        /**
         * @var Ved_Gorders_Helper_Data $helper
         */
        if ($data['quantity'] > (int)$product->getQtyOrdered())
            throw new Exception('Không thể lấy nhiều hơn số lượng yêu cầu');
        Mage::getModel('ved_gorders/orderitemstock')
            ->setData(array_merge($data, array(
                'product_id' => $product->getProductId(),
                'order_id' => $order->getId(),
                'sku' => $product->getSku(),
                'order_increment_id' => $order->getIncrementId(),
                'order_item_id' => $product->getItemId(),
                'created_by' => Mage::getSingleton('admin/session')->getUser()->getUserId(),
                'created_at' => now(),
                'standard_product_id' => $product->getProduct()->getStandardProductId(),
            )))
            ->save();
    }

    private function updateWarehouse($product, $order, $data)
    {
        if (!$order->isExistHoldItem($data['store_id'], $product->getProductId())) {
            throw new Exception('Đơn hàng đã lấy hàng từ kho khác. Nếu muốn lấy hàng vui lòng tách đơn');
        }
        /**
         * @var Ved_Gorders_Helper_Data $helper
         */
        if ($data['quantity'] > (int)$product->getQtyOrdered())
            throw new Exception('Không thể lấy nhiều hơn số lượng yêu cầu');
        $oldStock = Mage::getModel('ved_gorders/orderitemstock')->load($data['old_stock_id']);
        $oldStock->addData($data)->save();
    }

    private function saveSupplier($product, $order, $data)
    {
        $storeMagento = Mage::getModel('core/store')->load($order->getStoreId());
        $supplier = $this->getSupplierForPurchase($product, $data['supplier_id']);
        Mage::getModel('ved_gorders/purchaserequestitem')
            ->setData(array_merge($data, array(
                'product_id' => $order->getItemById($product->getId())->getProductId(),
                'order_id' => $order->getId(),
                'sku' => $product->getProduct()->getWarehouseSku(),
                'product_name' => $product->getName(),
                'price' => 0,
                'original_price' => $product->getOriginalPrice(),
                'order_qty' => $order->getItemById($product->getId())->getQtyOrdered(),
                'order_increment_id' => $order->getIncrementId(),
                'website_id' => $storeMagento->getWebsiteId(),
                'created_by' => Mage::getSingleton('admin/session')->getUser()->getUserId(),
                'supplier_info' => json_encode($supplier, JSON_UNESCAPED_UNICODE),
                'order_item_id' => $product->getItemId(),
                'created_at' => now(),
                'standard_product_id' => $product->getProduct()->getStandardProductId(),
                'pre_status' => 0,
                'note_purchase' => json_encode([$data['note_purchase']], JSON_UNESCAPED_UNICODE)
            )))
            ->save();
    }

    private function updateSupplier($product, $order, $data)
    {
        $storeMagento = Mage::getModel('core/store')->load($order->getStoreId());
        $supplier = $this->getSupplierForPurchase($product, $data['supplier_id']);
        $request = Mage::getModel('ved_gorders/purchaserequestitem')->load($data['old_request_id']);
        $note = json_decode($request->getNotePurchase());
        array_push($note, $data['note_purchase'] ? $data['note_purchase'] : "");
        $request->addData(array_merge($data, array(
            'updated_by' => Mage::getSingleton('admin/session')->getUser()->getUserId(),
            'supplier_info' => json_encode($supplier, JSON_UNESCAPED_UNICODE),
            'website_id' => $storeMagento->getWebsiteId(),
            'pre_status' => 0,
            'note_purchase' => json_encode($note, JSON_UNESCAPED_UNICODE)
        )))->save();
    }

    /**
     * @deprecated
     * @param $product
     * @param Mage_Sales_Model_Order $order
     * @param $data
     * @throws Exception
     */
    private function saveTransfer($product, Mage_Sales_Model_Order $order, $data)
    {
        $storeMagento = Mage::getModel('core/store')->load($order->getStoreId());

        $store = $this->getStoreWarehouse($product, $order);

        if (isset($store['parent']) && $store['parent']) {
            if ($store['parent']['quantity'] > 0) {
                Mage::getModel('ved_gorders/transfer')
                    ->setData(array_merge($data, array(
                        'order_id' => $order->getId(),
                        'product_name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'price' => $product->getPrice(),
                        'order_qty' => $product->getQtyOrdered(),
                        'order_increment_id' => $order->getIncrementId(),
                        'created_by' => Mage::getSingleton('admin/session')->getUser()->getUserId(),
                        'product_id' => $product->getProductId(),
                        'website_id' => $storeMagento->getWebsiteId(),
                        'order_item_id' => $product->getItemId(),
                        'created_at' => now(),
                        'store_id' => $store['parent']['id'],
                        'store_name' => $store['parent']['name'],
                        'request_store_name' => $store['name'],
                        'request_store_id' => $store['id'],
                        'quantity' => min($data['quantity'], $store['parent']['quantity'])
                    )))
                    ->save();

                if ($store['parent']['quantity'] < $data['quantity']) {
                    Mage::getModel('ved_gorders/purchaserequestitem')
                        ->setData(array_merge($data, array(
                            'product_id' => $order->getItemById($product->getId())->getProductId(),
                            'order_id' => $order->getId(),
                            'sku' => $product->getSku(),
                            'product_name' => $product->getName(),
                            'price' => "",
                            'order_qty' => $product->getQtyOrdered(),
                            'order_increment_id' => $order->getIncrementId(),
                            'supplier_name' => "",
                            'store_id' => $store['parent']['id'],
                            'website_id' => $storeMagento->getWebsiteId(),
                            'created_by' => Mage::getSingleton('admin/session')->getUser()->getUserId(),
                            'supplier_info' => "",
                            'order_item_id' => $product->getItemId(),
                            'created_at' => now(),
                            'quantity' => $data['quantity'] - $store['parent']['quantity'],
                            'store_name' => $store['parent']['name'],
                        )))
                        ->save();
                }
            }
        } else {
            throw new Exception('Don\'t  has parent store');
        }
    }

    private function getSupplierForPurchase($product, $id)
    {
        $productId = $product->getProductId();

        $path = 'products/suppliers?' . http_build_query(array('entity_id' => array($productId)));

        $api = file_get_contents(Mage::helper('ved_gorders')->getApiUrlWarehouse($path));

        $json = json_decode($api, true);

        foreach ($json['data'][$productId] as $supplier) {
            if ($supplier['id'] == $id) return $supplier;
        }

        return array();
    }

    private function getStoreWarehouse($product, $order)
    {
        $province_name = $order->getAssignProvince() ? $order->getAssignProvince() : $order->getShippingAddress()->getRegion();
        $path = 'products/in-stock-quantity-by-province-entity?' . http_build_query(array(
                'entity_id' => $product->getProductId(),
                'province_name' => $province_name,
            ));

        $api = file_get_contents(Mage::helper('ved_gorders')->getApiUrlWarehouse($path));

        $json = json_decode($api, true);

        if ($json['status'] == 2) {
            throw new Exception('Call api fail');
        }

        return $json['data'];
    }

    private function validationStore($product, $id, $quantity)
    {
        $store = $this->getStoreForPurchase($product, $id);

        if ($store['quantity'] < $quantity) {
            throw  new Exception("Hàng trong kho không đủ");
        }

        return isset($store['store']) ? $store['store'] : array();
    }

    private function getStoreForPurchase($product, $id)
    {
        $productId = $product->getProductId();

        $path = 'products/in-stock-quantity?' . http_build_query(array('entity_id' => array($productId)));

        $api = file_get_contents(Mage::helper('ved_gorders')->getApiUrlWarehouse($path));

        $json = json_decode($api, true);

        foreach ($json['data'][$productId] as $store) {
            if ($store['store']['id'] == $id) return $store;
        }

        return array();
    }

    public function getStoreForOrder($name)
    {
        $path = 'orders/get_store?' . http_build_query(array(
                'order_province' => $name,
            ));

        $api = file_get_contents(Mage::helper('ved_gorders')->getApiUrlWarehouse($path));

        $json = json_decode($api, true);

        if (isset($json['status']) && $json['status'] == 2) {
            throw new Exception('Call api fail');
        }

        return $json['data'];
    }

    /**
     * Update Deposit
     * @return Mage_Core_Controller_Response_Http
     *
     */
    public function updateDepositAction()
    {
        try {
            if (!$this->getRequest()->isGet()) throw new Exception('Method not allow');
            /**
             * @var Mage_Sales_Model_Order $order ;
             */
            $order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('id'));
            $order->updateDepositAmount($this->getRequest()->get('deposit_amount'));
            $this->_getSession()->addSuccess(Mage::helper('sales')->__('The order has been updated.'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        return $this->getResponse()->setRedirect($this->getUrl('*/*/view', ['order_id' => $this->getRequest()->getParam('id')]));
    }

    private function callMessageQueue(Mage_Sales_Model_Order &$order)
    {
        if ($order->notSendQueue()) {
            Mage::callMessageQueue($order->getDataMessageQueue(), 'sale.order.create');
            $order->setIsSendQueue(true);
        }
    }

    private function saveOutputRequest(Mage_Sales_Model_Order $order)
    {
        if ($order && $order->getIncrementId() && $order->notSendQueue()) {
            try {
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                $connection->beginTransaction();
                $storeMagento = Mage::getModel('core/store')->load($order->getStoreId());

                $region = Mage::getModel('directory/region')->loadByName($order->getShippingAddress()->getRegion(), 'VN');

                $items = $order->getAllItems();

                foreach ($items as $item) {
                    if ($item->getProductType() == 'simple') {
                        $product = Mage::getModel('catalog/product')->load($item->getProductId());

                        Mage::getModel('ved_gorders/outputrequest')
                            ->setData(array(
                                'order_id' => $order->getId(),
                                'name' => $item->getName(),
                                'sku' => $item->getSku(),
                                'warehouse_sku' => $product->getWarehouseSku(),
                                'price' => $item->getPrice(),
                                'quantity' => $item->getQtyOrdered(),
                                'increment_id' => $order->getIncrementId(),
                                'created_by' => Mage::getSingleton('admin/session')->getUser()->getUserId(),
                                'product_id' => $product->getStandardProductId(),
                                'website_id' => $storeMagento->getWebsiteId(),
                                'item_id' => $item->getItemId(),
                                'created_at' => now(),
                                'store_id' => $order->getStoreId(),
                                'province_name' => $order->getShippingAddress()->getRegion(),
                                'province_code' => $region ? $region->getProvinceCode() : ''
                            ))
                            ->save();
                    }
                }

                $connection->commit();

            } catch (Exception $e) {
                $connection->rollback();
                throw new Exception('Cannot save output request');
            }
        }
    }

    /**
     * Edit order affiliate code form
     */
    public function affiliateAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

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
    public function affiliateSaveAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $data = $this->getRequest()->getPost();

        if ($data && $order->getId()) {
            try {
                // Remove the comment to disable edit affiliate code when order is sent
                // if ($order->getIsSendQueue())
                //     throw new Exception('Not allow change order affiliate');

                // Save order affiliate
                $order->setAffiliateCode($data['affiliate']);
                $order->save();

                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The order affiliate code has been updated.'));
                $this->_redirect('*/*/view', array('order_id' => $orderId));
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('sales')->__('An error occurred while updating the order affiliate code. The affiliate code has not been changed.')
                );
            }
            $this->_redirect('*/*/affiliate', array('order_id' => $orderId));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Edit order affiliate code form
     */
    public function depositAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
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
    public function depositSaveAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = Mage::getModel('sales/order')->load($orderId);
        $data = $this->getRequest()->getPost();
        if ($data && $order->getId() && $order->allowUpdateDeposit()) {
            try {
                $admin_user_session = Mage::getSingleton('admin/session');
                $adminUserName = $admin_user_session->getUser()->getUsername();
                $order->addData(array_intersect_key($data, ['deposit_amount' => "", 'deposit_method' => "", 'deposit_description' => '']));
                $order->addStatusHistoryComment(
                    "<strong>{$adminUserName}</strong>" .
                    '<br>Đặt cọc số tiền: ' . number_format($data['deposit_amount'], 0, ',', '.') .
                    '<br>Phương thức: ' . ($data['deposit_method'] == 'COD' ? 'Thu tiền mặt' : 'Chuyển khoản ngân hàng')

                    , false);
                $order->save();
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The deposit info has been updated.'));
                $this->_redirect('*/*/view', array('order_id' => $orderId));
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('sales')->__('An error occurred while updating the order affiliate code. The affiliate code has not been changed.')
                );
            }
        }
        $this->_redirect('*/*/view', array('order_id' => $orderId));
    }
}
