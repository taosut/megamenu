<?php

class Ved_Purchase_Model_Observer
{
    public function orderTelephoneConfirmBefore($observer)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllItems();
        /** @var Mage_Sales_Model_Order_Item[] $items */
        foreach ($items as $item) {
            if ($item->getProductType() != 'simple') continue;
            /** @var  Ved_Gorders_Model_Resource_Orderitemstock_Collection $itemStockCollection */
            $itemStockCollection = Mage::getResourceModel('ved_gorders/orderitemstock_collection');
            $stockQty = $itemStockCollection->getQuantityByOrderItem($item->getProductId(), $order->getId());
            if ($stockQty >= $item->getQtyOrdered()) continue;

            /** @var Ved_Gorders_Model_Resource_Purchaserequestitem_Collection $purchaseRequestCollection */
            $purchaseRequestCollection = Mage::getResourceModel('ved_gorders/purchaserequestitem_collection');
            $itemPurchaseQty = $purchaseRequestCollection->getQuantityByOrderItem($item->getProductId(), $order->getId());

            if ($itemPurchaseQty + $stockQty >= $item->getQtyOrdered()) {
                continue;
            } else {
                throw new Exception('Không thể xác nhận đơn hàng do có sản phẩm chưa đủ hàng');
            }
        }
    }
}