<?php

/**
 * @property int createdAt
 */
class Queue_Asia_Product_Price_Upsert
{
    /**
     * @param $message
     * @return bool
     * @throws Exception
     */
    public function process($message)
    {
        $data = json_decode($message, true);
        $date = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $from = $date->setTime(5, 0, 0)->getTimestamp();
        $to = $date->setTime(23, 0, 0)->getTimestamp();
        if ($this->createdAt > $from && $this->createdAt < $to)
            $this->updateProductPrice($data);
        return true;
    }

    /**
     * Update price for Product
     * @param array $input
     * @throws Exception
     */
    private function updateProductPrice($input)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(['status', 'visibility', 'price', 'special_price', 'image'])
            ->addAttributeToFilter('warehouse_sku', $input['sku'])
            ->addWebsiteFilter(20)
            ->getFirstItem();
        if ($product->getId()) {
            $product->setData('price', $input['price']);
            $product->getResource()->saveAttribute($product, 'price');
            $this->updateProductStatus($product, $input['status']);
            $event = Mage::getSingleton('index/indexer')->logEvent(
                $product,
                $product->getResource()->getType(),
                Mage_Index_Model_Event::TYPE_SAVE,
                false
            );
            Mage::getSingleton('index/indexer')
                ->getProcessByCode('catalog_product_flat')
                ->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)
                ->processEvent($event);
        } else {
            throw new Exception('Product not found');
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param  bool $status
     * @return bool
     */
    private function updateProductStatus($product, $status)
    {
        if (in_array($status, [2, 5]) && $product->getData('status') != 2) {
            $product->setData('instock', Mage_Catalog_Model_Product::OUT_OF_STOCK);
            $product->getResource()->saveAttribute($product, 'instock');
            return true;
        }
        if (in_array($status, [1, 3, 4, 6])) {
            if ($product->getData('image')) {
                $product->setData('status', 1);
                $product->getResource()->saveAttribute($product, 'status');
            } else {
                $product->setData('status', 0);
                $product->getResource()->saveAttribute($product, 'status');
            }
        }
        $product->setData('instock', $status);
        $product->getResource()->saveAttribute($product, 'instock');
        return true;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }


}