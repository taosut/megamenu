<?php

class Queue_Wh_Import_Create extends ProcessQueue
{
    public function process($message)
    {
        $data = json_decode($message, true);
        $items = $data['items'];
        foreach ($items as $item) {
            /**
             * @var Zend_Db_Select $query
             */
            $query = Mage::getModel('ved_purchase/purchaseitem')
                ->getCollection()
                ->getSelect();
            $query->join(['purchase' => 'sales_flat_purchase'], 'main_table.purchase_id = purchase.id', null)
                ->where('purchase.status = ?', 1)
                ->where('purchase.store_id = ?', $data['warehouseId'])
                ->where('purchase.supplier_id = ?', $item['supplierId'])
                ->where('main_table.product_id = ?', $item['productId'])
                ->where('main_table.import_qty < main_table.request_qty');
            $purchaseItems = $query->query()->fetchAll();
            /**
             * @var Ved_Purchase_Model_Resource_Purchaseitem_Collection $purchaseItemsCollection
             */
            $purchaseItemsCollection = Mage::getModel('ved_purchase/purchaseitem')
                ->getCollection();
            $purchaseItemsCollection->distributeImport($this->getArrayByData($purchaseItems, 'id'), $item['quantity']);
            /**
             * @var Ved_Purchase_Model_Resource_Purchase_Collection $purchaseCollection
             */
            $purchaseCollection = Mage::getModel('ved_purchase/purchase')
                ->getCollection();
            $purchaseCollection->checkComplete($this->getArrayByData($purchaseItems, 'purchase_id'));
        }
    }
}