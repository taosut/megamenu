<?php

class Queue_Lo_V2_Bl_Returning extends ProcessQueue
{
    function process($message)
    {
        $data = json_decode($message, true);
        $bl = $data['BL'];
        if ($bl['requestType'] != 'SALE_ORDER')
            throw new Exception('Order Not FOUND');
        $transport = $data['transport'];
        if ($transport['state'] == 'returned')
            throw new Exception('logisticReturning state is returned');
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = Mage::getModel('sales/order')->load($bl['orderId']);

        if (!$order->getId()) throw new Exception('Order Not FOUND');
        try {
            $comment = "<b>Danh sách sản phẩm trả lại: </b>";

            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->beginTransaction();

            foreach ($order->getAllItems() as $item) {
                /**
                 * @var Mage_Sales_Model_Order_Item $item
                 */
                if (!$item->getParentItemId()) {
                    foreach ($transport['items'] as &$itemInput) {
                        /**
                         * @var Mage_Catalog_Model_Product $product
                         */
                        if ($item->getStandardProductId() == $itemInput['productId'] && $itemInput['quantity'] > 0) {
                            $return = min(max($item->getQtyOrdered() - $item->getTotalReturned(), 0), $itemInput['quantity']);
                            $item->setTotalReturned(min($item->getTotalReturned() + $itemInput['quantity'], $item->getQtyOrdered()));
                            $itemInput['quantity'] -= $return;
                            $comment .= <<<HTML
                        <br > {$return} x {$itemInput['desc']}
HTML;
                            $item->save();
                            break;
                        }
                    }
                }
            }

            foreach ($order->getAllItems() as $item) {
                /**
                 * @var Mage_Sales_Model_Order_Item $item
                 */
                if ($item->getParentItemId()) {
                    foreach ($transport['items'] as &$itemInput) {
                        /**
                         * @var Mage_Catalog_Model_Product $product
                         */
                        if ($item->getStandardProductId() == $itemInput['productId'] && $itemInput['quantity'] > 0) {
                            $return = min(max($item->getQtyOrdered() - $item->getTotalReturned(), 0), $itemInput['quantity']);
                            $item->setTotalReturned(min($item->getTotalReturned() + $itemInput['quantity'], $item->getQtyOrdered()));
                            $itemInput['quantity'] -= $return;
                            $comment .= <<<HTML
                        <br > {$return} x {$itemInput['desc']}
HTML;
                            $item->save();
                            break;
                        }
                    }
                }
            }

            if ($bl['state'] == 'returning') {
                $order->setTotalCanceled(min($order->getTotalCanceled() + $transport['cod'] - $transport['paidCod'], $order->getGrandTotal()));
            }

            if ($order->getTotalCanceled() == $order->getGrandTotal() && $order->getState() != 'canceled' && $order->getState() != 'complete')
                $order->setState('processing', 'delivery_failed', $comment);
            else
                $order->addStatusHistoryComment($comment, false);
            $order->save();

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            Mage::log($e->getMessage(), null, 'queue_log');
            throw new Exception($e->getMessage());
        }
    }
}