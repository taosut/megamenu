<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 12/7/2016
 * Time: 3:01 PM
 */
class Ved_Gorders_Block_Adminhtml_Sales_Order_View_Items_Renderer_Product extends Mage_Adminhtml_Block_Sales_Order_View_Items_Renderer_Default
{
    private $html;

    private function getPurchaseWahouse(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Item $item)
    {
        $data = Mage::getModel('ved_gorders/orderitemstock')
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('product_id', $item->getId())
            ->addFieldToFilter('status', 1)
            ->load();

        if (count($data)) {
            $store = Mage::getModel('core/store')->load($data->getFirstItem()->getStoreId());

            $this->html = '<p>Đã lấy từ kho: ' . $store->getName() . '</p>';

            $this->html .= '<p> Số lượng: ' . $data->getFirstItem()->getQuantity() . '</p>';

            return true;
        }
        return false;

    }

    private function getPurchaseSupplier(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Item $item)
    {
        $data = Mage::getModel('ved_gorders/purchaserequestitem')
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('sku', $item->getSku())
            ->addFieldToFilter('status', 1)
            ->load();

        if (count($data)) {
            $store = Mage::getModel('core/store')->load($data->getFirstItem()->getStoreId());

            $this->html = '<p>Đã lấy từ NCC: ' . $store->getName() . '</p>';

            $this->html .= '<p> Số lượng: ' . $data->getFirstItem()->getQuantity() . '</p>';

            return true;
        }
        return false;

    }


    public function __construct()
    {
        parent::__construct();

    }

    public function getPurchaseHtml(Mage_Sales_Model_Order_Item $item)
    {
        /**
         * @var $order Mage_Sales_Model_Order
         */
        $order = $this->getOrder();

        if ($this->getPurchaseWahouse($order, $item)) {
            return $this->html;
        } elseif ($this->getPurchaseSupplier($order, $item)) {
            return $this->html;
        } else {

            $url = $this->getUrl('*/*/purchase', array('order_id' => $order->getId(), 'product_id' => $item->getId()));

            return "<a href='$url'><button class='button'>Purchase Warehouse</button></a>";
        }
    }

    public function getWarehouseSkuItemOrder($item)
    {
        try {
            if ($item->getProductType() == 'configurable') {
                $data = Mage::getModel('sales/order_item')
                    ->getCollection()
                    ->addFieldToFilter('parent_item_id', $item->getId())
                    ->getData();
                if (isset($data[0]['item_id']) && $data[0]['item_id'])
                    return Mage::getModel('sales/order_item')->load($data[0]['item_id'])->getProduct()->getWarehouseSku();
            }
            return $item->getWarehouseSku();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}