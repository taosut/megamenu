<?php

class Queue_Wh_Export_Create extends ProcessQueue
{
    public function process($message)
    {
        $input = json_decode($message, true);
        if (!isset($input['requestType']) || $input['requestType'] != 0) throw  new Exception('Request Type Required');
        foreach ($input['items'] as $item) {
            /**
             * @var Ved_Gorders_Model_Orderitemstock $orderItemStock
             */
            $orderItemStock = Mage::getModel('ved_gorders/orderitemstock')->getCollection()
                ->addFieldToFilter('order_id', $input['orderId'])
                ->addFieldToFilter('standard_product_id', $item['productId'])
                ->getFirstItem();
            if (!$orderItemStock->isEmpty())
                $orderItemStock->setStatus(2)
                    ->save();
        }

        $order = Mage::getModel('sales/order')->load($input['orderId']);

        // Check whether order is exist or not
        if ($order->isEmpty()) throw new Exception('Order id not exist in system');

        // Check whether order state is canceled or not
        if ($order->getState() === 'new') {
            $order->setState('processing', true, '');
        }
        /**
         * @var Mage_Sales_Model_Order $order
         */
        Mage::getModel('core/resource_transaction')
            ->addObject($order)
            ->save();
    }
}