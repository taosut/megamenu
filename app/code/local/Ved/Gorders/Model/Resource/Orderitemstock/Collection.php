<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 12/7/2016
 * Time: 5:07 PM
 */
class Ved_Gorders_Model_Resource_Orderitemstock_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @param $productId
     * @param $orderId
     * @return int
     */
    public function getQuantityByOrderItem($productId, $orderId)
    {
        $item = $this->addFieldToFilter('status', 1)
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('product_id', $productId)
            ->getFirstItem();
        return (int)$item->getData('quantity');
    }

    protected function _construct()
    {
        $this->_init('ved_gorders/orderitemstock');
    }

    /**
     * @param int $quantity
     */
    public function distributeImport($quantity)
    {
        foreach ($this->getItems() as $item) {
            /**
             * @var Ved_Gorders_Model_Resource_Orderitemstock $item
             */
            $quantity = min($item['quantity'], $item->getRequestQuantity());
            $item->setImportQuantity(min())->save();
        }
    }
}