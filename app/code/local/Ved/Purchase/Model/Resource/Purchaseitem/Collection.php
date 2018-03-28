<?php

/**
 * Class Ved_Purchase_Model_Resource_Purchaseitem_Collection
 */
class Ved_Purchase_Model_Resource_Purchaseitem_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init("ved_purchase/purchaseitem");
    }

    /**
     * @param int $quantity
     * @param array $ids
     */
    public function distributeImport($ids, $quantity)
    {
        /**
         * @var Ved_Purchase_Model_Resource_Purchaseitem[] $purchaseItems
         */
        $purchaseItems = $this->addFieldToFilter('id', ['in' => $ids])
            ->load();
        foreach ($purchaseItems as $item) {
            /**
             * @var Ved_Purchase_Model_Purchaseitem $item
             */
            $needQuantity = min($item->getRequestQty() - $item->getImportQty(), $quantity);
            $item->setImportQty($item->getImportQty() + $needQuantity)->save();
            $quantity -= $needQuantity;
        }
    }
}