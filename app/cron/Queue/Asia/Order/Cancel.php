<?php

class Queue_Asia_Order_Cancel
{
    /**
     * @param $message
     * @throws Exception
     */
    public function process($message)
    {
        $data = json_decode($message, true);
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($data['orderId']);
        if ($order->getId())
            $order->setState('canceled', true)->save();
        else
            throw new Exception("Khong tim thay don hang");

    }
}