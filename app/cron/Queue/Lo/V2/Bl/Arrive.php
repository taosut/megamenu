<?php

class Queue_Lo_V2_Bl_Arrive extends ProcessQueue
{
    public function process($getMessage)
    {
        $data = json_decode($getMessage, true);
        if ($data['BL']['requestType'] != 'SALE_ORDER')
            throw new Exception('requestType Not SALE_ORDER');
        $order = $this->loadOrderOrFail($data['BL']['orderId']);
        $comment = '';
        if (isset($data['transport']['to']['type']) && $data['transport']['to']['type'] == 'customer') {
            $comment .= "<b>Danh sách sản phẩm đã giao: </b>";
            $arriveItems = [];
            foreach ($data['transport']['deliveredItems'] as $item) {
                $comment .= <<<HTML
                    <br > {$item['quantity']} x {$item['desc']}
HTML;
                $arriveItems[$item['productId']] = $item['quantity'];
            }
            $check = true;
            foreach ($order->getItemsCollection() as $orderItem) {
                if ($orderItem->getData('product_type') == 'simple') {
                    /**@var Mage_Sales_Model_Order_Item $orderItem */
                    $itemSku = $orderItem->getData('standard_product_id');
                    if (isset($arriveItems[$itemSku])) {
                        $orderItem->setData('qty_arrive',
                            min($arriveItems[$itemSku], $orderItem->getQtyOrdered())
                        );
                        $arriveItems[$itemSku] -= min($arriveItems[$itemSku], $orderItem->getQtyOrdered());
                    }
                    $check &= ($orderItem->getQtyOrdered() == $orderItem->getData('qty_arrive'));
                }
            }
            $order->setTotalCodAmount(min($order->getTotalCodAmount() + $data['transport']['paidCod'],
                $order->getGrandTotal() - $order->getDepositAmount() - $order->getTotalPaid()));
            $order->setTotalCanceled(min($order->getTotalCanceled() + $data['transport']['cod'] - $data['transport']['paidCod'], $order->getGrandTotal()));
            if ($check)
                $status = "delivered";
            else
                $status = 'partial_delivered';
            switch ($order->getState()) {
                case 'canceled':
                    $order->addStatusHistoryComment("Đã tạo vận chuyển cho đơn hàng hủy </br>" . $comment, false);
                    break;
                case 'complete':
                    break;
                case 'closed':
                    break;
                default:
                    $order->setState('processing', $status, $comment);
                    break;
            }
            $order->save();
        }
    }
}