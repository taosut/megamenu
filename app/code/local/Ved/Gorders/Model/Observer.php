<?php

class Ved_Gorders_Model_Observer
{
    /**
     * Event before save Order
     * Set source create Order
     * Uses Table order source
     * @param $observer
     */
    public function salesOrderSaveBefore($observer)
    {
        try {
            /**
             * @var Mage_Sales_Model_Order $order
             */
            $order = $observer->getEvent()->getOrder();
            if ($order->isObjectNew() && !$order->getOriginalIncrementId()) {
                Mage::getModel('ved_gorders/order_source')->addData([
                    'original_increment_id' => $this->getIncrementIdOrder($order),
                    'channel' => $this->getChannelSourceOrder($order),
                    'terminal_id' => $this->getTerminalIdOrder($order),
                    'agent_id' => $this->getAgentIdOrder($order)
                ])->save();
            }
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    /**
     * @param $observer
     */
    public function saleOrderSendQueueBefore($observer)
    {
        /** @var  Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        $order->setPaidAmount($order->getPaidAmount() + $order->getDepositAmount() + $order->getTotalPaid());
        $order->setUnpaidAmount(
            $order->getUnpaidAmount()
            + $order->getGrandTotal()
            - $order->getTotalPaid()
            - $order->getDepositAmount()
        );
        if (!is_array($order->getUserActionInfor()))
            $order->setUserActionInfor([]);
        $order->setUserActionInfor(array_merge($order->getUserActionInfor(), [
            'createdBy' => $order->getSourceType()]));
        $user_id = null;
        $userObj = Mage::getSingleton('admin/session')->getUser();
        if ($userObj) {
            $user_id = $userObj->getUserId();
        }
        if ($user_id) {
            $order->setUserActionInfor(array_merge($order->getUserActionInfor(), [
                "confirmedBy" => [
                    "id" => $userObj->getData('sso_id'),
                    "asiaId" => $userObj->getData('asia_id'),
                ]
            ]));
        }
    }

    private function getChannelSourceOrder(Mage_Sales_Model_Order $order)
    {
        if (in_array($order->getStoreId(), [20, 21, 22, 23, 27])) {
            return "ONLINE";
        }
        if ($order->getCreatedById())
            return "APP";
        return "OFFLINE";
    }

    private function getTerminalIdOrder(Mage_Sales_Model_Order $order)
    {
        switch ($this->getChannelSourceOrder($order)) {
            case 'APP':
                return $order->getShippingAddress()->getRegionId();
                break;
            default:
                return "";
        }
    }

    private function getAgentIdOrder(Mage_Sales_Model_Order $order)
    {
        switch ($this->getChannelSourceOrder($order)) {
            case 'APP':
                return $order->getCreatedById();
            default:
                $userObj = Mage::getSingleton('admin/session')->getUser();
                if ($userObj) {
                    return $userObj->getData('sso_id');
                }
                return 0;
        }
    }

    private function getIncrementIdOrder(Mage_Sales_Model_Order $order)
    {
        if (!$order->getIncrementId()) {
            $incrementId = Mage::getSingleton('eav/config')
                ->getEntityType('order')
                ->fetchNewIncrementId($order->getStoreId());
            $order->setIncrementId($incrementId);
        }
        return $order->getIncrementId();
    }

    public function orderTelephoneConfirmBefore($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == 'simple' && strpos($item->getWarehouseSku(), '-'))
                throw new Exception('Sản phẩm: ' . $item->getWarehouseSku() . ' không đúng định dạng.');
        }
    }

    public function changeProductAfterOrderCreate($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        foreach ($order->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            if ($item->getProductType() == 'simple') {
                /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());
                if ($stockItem->getManageStock()) {
                    if ($item->getProduct()->getData('instock') == Mage_Catalog_Model_Product::CLEAR_STOCK && $stockItem->getQty() == 0) {
                        $product = $item->getProduct();
                        $product->setData('instock', Mage_Catalog_Model_Product::OUT_OF_STOCK);
                        $product->getResource()->saveAttribute($product, 'instock');
                        Mage::getSingleton('index/indexer')->processEntityAction(
                            $product, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
                        );
                    }
                }
            }
        }
    }

    /**
     * @param $observer
     * @throws Exception
     */
    public function updateInstockProductOrderCancelAfter($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        foreach ($order->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            if ($item->getProductType() == 'simple') {
                /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());
                if ($stockItem->getManageStock()) {
                    if ($item->getProduct()->getData('instock') == Mage_Catalog_Model_Product::OUT_OF_STOCK &&
                        $stockItem->getQty() > 0 &&
                        $stockItem->getIsInStock()
                    ) {
                        $product = $item->getProduct();
                        $product->setData('instock', Mage_Catalog_Model_Product::CLEAR_STOCK);
                        $product->getResource()->saveAttribute($product, 'instock');
                        Mage::getSingleton('index/indexer')->processEntityAction(
                            $product, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
                        );
                    }
                }
            }
        }
        if (!$order->getIsSendQueue()) {
            Mage::callMessageQueue($order->getDataMessageQueue(), 'sale.neworder.cancel');
        }
    }
}