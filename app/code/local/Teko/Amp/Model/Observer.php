<?php

class Teko_Amp_Model_Observer
{
    public function orderTelephoneConfirmBefore($observer)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $observer->getEvent()->getOrder();
        if ($order->notSendQueue()) {
            Mage::callMessageQueue($order->getDataMessageQueue(), 'sale.order.create');
            $order->setIsSendQueue(true);
        }
    }
}