<?php

class Ved_Instalment_Model_Observer
{
    public function orderTelephoneConfirmBefore($observer)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $observer->getEvent()->getOrder();
        if (!!$order->getData('is_instalment')) {
            $orderInstalment = Mage::getModel('ved_instalment/order_instalment')
                ->load($order->getId(), 'order_id');
            if ($orderInstalment->getData('status') != 1)
                throw new Exception("Đơn này là đơn trả góp. Vui lòng xác nhận trả góp trước.");
        }
    }

    /**
     * @param $observer
     */
    public function saleOrderSendQueueBefore($observer)
    {
        /** @var  Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getData('is_instalment') == '1') {
            /** @var Ved_Instalment_Model_Order_Instalment $instalment */
            $instalment = Mage::getModel('ved_instalment/order_instalment')
                ->getCollection()
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('status', true)
                ->getFirstItem();
            $order->setPaidAmount($order->getPaidAmount()
                + $instalment->getData('amount')
            );
            $order->setUnpaidAmount($order->getUnpaidAmount() - $instalment->getData('amount'));
        }
    }
}