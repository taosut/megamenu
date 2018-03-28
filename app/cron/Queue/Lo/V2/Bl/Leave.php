<?php

class Queue_Lo_V2_Bl_Leave extends ProcessQueue
{
    public function process($message)
    {
        $data = json_decode($message, true);
        $order = $this->loadOrderOrFail($data['BL']['orderId']);
        if ($data['BL']['requestType'] != 'SALE_ORDER')
            throw new Exception('requestType Not SALE_ORDER');
        $comment = '';
        $status = 'processing';

        $shipper = $data['transport']['shipper'];
        if ($shipper) {
            if ($shipper['type'] === "ktv") {
                $comment .= "Vận chuyển bởi KTV: " . $shipper['name'] . "<br />";
                $status = 'gcafe_ship';
            } else if ($shipper['type'] === "direct") {
                $comment .= "Giao trực tiếp tại điểm bán <br />";
                $status = 'direct_delivery';
            } else {
                $comment .= "Vận chuyển bởi: " . $shipper['type'] . " - " . $shipper['name'] . "<br />";
                $status = 'third_party_ship';
            }
        }
        $comment .= "<b>Danh sách sản phẩm vận chuyển: </b>";
        foreach ($data['transport']['items'] as $item) {
            $comment .= <<<HTML
                <br > {$item['quantity']} x {$item['desc']}
HTML;
        }

        switch ($order->getState()) {
            case 'canceled':
                $order->addStatusHistoryComment("Đã tạo vận chuyển cho đơn hàng hủy </br>" . $comment, false);
                $order->save();
                break;
            case 'complete':
                break;
            case 'closed':
                break;
            default:
                if ($order->getStatus() != 'delivered' && $order->getStatus() != 'partial_delivered') {
                    $order->setState('processing', $status, $comment);
                } else {
                    $order->addStatusHistoryComment($comment, false);
                }
                $order->save();
                break;
        }
    }
}