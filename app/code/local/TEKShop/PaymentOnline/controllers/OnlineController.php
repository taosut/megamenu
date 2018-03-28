<?php

/**
 * Class TEKShop_PaymentOnline_OnlineController
 * @property TEKShop_PaymentOnline_Helper_Data $helper
 */
class TEKShop_PaymentOnline_OnlineController extends Mage_Core_Controller_Front_Action
{
    /**
     * Save Order When Payment Online
     * Route /payment_online/online/save POST or GET
     */
    public function saveAction()
    {
        if ($this->getRequest()->isGet())
            return $this->getResponse()->setRedirect(Mage::getUrl('checkout/payment/index'));
        try {

            $this->helper = Mage::helper('TEKShop_PaymentOnline');
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $this->getResponse()->setRedirect($this->helper->getUrlPayment($quote));
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::logException($e);
            $this->getResponse()->setRedirect(Mage::getUrl('checkout/payment/index'));
        }
    }

    /**
     * Route /payment_online/online/callback
     */
    public function callbackAction()
    {
        try {
            $this->helper = Mage::helper('TEKShop_PaymentOnline');
            /** @var Mage_Checkout_Model_Type_Onepage $oncepage */
            $oncepage = Mage::getSingleton('checkout/type_onepage');
            $oncepage->initCheckout();
            $payment = $this->helper->checkPaymentOnline();
            $shipping_method = 'tablerate_bestway';
            $shipping_address = $oncepage->getQuote()->getShippingAddress();
            $shipping_address->addData(array('shipping_method' => $shipping_method));
            $oncepage->savePayment(array('method' => 'purchaseorder', 'po_number' => $payment->transaction_id));
            $cartId = $oncepage->getQuote()->getId();
            $oncepage->getQuote()->save();
            $oncepage->saveOrder();
            $oncepage->getQuote()->save();
            $order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            $order->setTotalPaid($oncepage->getQuote()->getGrandTotal())
                ->setAffiliateCode($oncepage->getQuote()->getAffiliateCode());
            $order->save();
            $this->getResponse()->setRedirect(Mage::getUrl('checkout/payment/success'));
            Mage::getModel('TEKShop_PaymentOnline/transaction')->addData([
                'order_id' => $order->getId(),
                'quote_id' => $cartId,
                'transaction_no' => $payment->transaction_id,
                'amount' => $payment->amount,
            ])->save();
            $this->helper->submitOrderPaymentOnline($order->getId(), $payment->transaction_id);
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->getResponse()->setRedirect(Mage::getUrl('checkout/payment/index'));
        }
    }

    /**
     * Route /payment_online/online/ipn
     */
    public function ipnAction()
    {
        $this->getResponse()->setHeader('Content-Type', 'application/json', true);
        Mage::getModel('TEKShop_PaymentOnline/log')->addData([
            'content' => $this->getRequest()->getRequestUri(),
            'ip' => Mage::helper('core/http')->getRemoteAddr(),
            'type' => 1,
            'created_at' => now(),
        ])->save();
        try {
            $this->helper = Mage::helper('TEKShop_PaymentOnline');
            $return = $this->helper->ipn($this->getRequest()->getParams());
            $this->getResponse()->setBody(json_encode($return));
        } catch (TEKShop_PaymentOnline_Exception $paymentOnline_Exception) {
            $this->getResponse()->setBody(json_encode([
                'RspCode' => $paymentOnline_Exception->getStatus(),
                'Message' => $paymentOnline_Exception->getMessage(),
            ]));
        } catch (Exception $e) {
            $this->getResponse()->setBody(json_encode([
                'RspCode' => '99',
                'Message' => "Unknown error",
            ]));
        }
    }
}
