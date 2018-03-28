<?php

/**
 * Class Ved_Purchase_Model_Purchase
 * @method string getReceiveDate()
 */
class Ved_Purchase_Model_Purchase extends Mage_Core_Model_Abstract
{
    protected $items;

    /**
     * @return Ved_Purchase_Model_Purchaseitem[]
     */
    public function getItems()
    {
        if (!$this->item) {
            $this->setItems();
        }
        return $this->items;
    }

    /**
     * Function construct class
     */
    protected function _construct()
    {
        $this->_init("ved_purchase/purchase");

    }

    /**
     * Set items for purchase
     */
    private function setItems()
    {
        /**
         * @var Ved_Purchase_Model_Resource_Purchaseitem_Collection $collection
         */
        $collection = Mage::getModel("ved_purchase/purchaseitem")->getCollection();
        $this->items = $collection->addFieldToFilter('purchase_id', $this->getId())
            ->load();
    }

    public function callMessageQueue()
    {
        $data = [
            "id" => $this->getId(),
            "supplierId" => $this->getSupplierId(),
            "warehouseId" => $this->getStoreId(),
            "createdAt" => time($this->getCreateAt()),
            "items" => $this->getItemsForQueue()
        ];
        Mage::callMessageQueue($data, 'sale.purchase.create');
    }

    private function getItemsForQueue()
    {
        $result = [];
        foreach ($this->getItems() as $item) {
            if($item->getRequestQty() > 0){
                $result[] = [
                    "productId" => $item->getProductId(),
                    "quantity" => $item->getRequestQty(),
                    "price" => $item->getPrice(),
                    "vat" => $item->getVat(),
                    "type" => $item->getTypeQueue(),
                ];
            }
        }
        return $result;
    }

    /**
     * @return $this
     */
    protected function _beforeSave()
    {
        if ($this->isObjectNew() && is_a($this->getReceiveDate(), DateTime::class)) {
            $this->setReceiveDate($this->getReceiveDate()->setTimeZone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
        }

        parent::_beforeSave();

        return $this;
    }
}
