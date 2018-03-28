<?php

class Queue_Asia_Product_Upsert extends ProcessQueue
{
    /**
     * @param $message
     * @throws Exception
     */
    public function process($message)
    {
        $data = json_decode($message, true);
        $product = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('warehouse_sku', $data['sku'])
            ->getFirstItem();
        if (!$product->getId())
            $product->addData([
                'type' => 'simple',
                'warehouse_sku' => $data['sku'],
                'sku' => $data['brandCode'],
                'name' => $data['name'],
                'warranty' => $data['warranty'],
                'attribute_set_id' => 4,
            ])->save();
        else
            throw new Exception("Product exist");
    }
}