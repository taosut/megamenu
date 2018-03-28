<?php

/**
 * @property  array data
 * @property  array $fields
 */
class Queue_Sale_Product_Upsert extends ProcessQueue
{
    protected $fields = [
        'id', 'categoryId', 'brandId', 'sku',
        'tekoSku', 'name', 'skuIdentifier', 'status',
        'sourceUrl', 'createdAt'
    ];

    /**
     * @param $message
     * @throws Exception
     */
    public function process($message)
    {
        $data = json_decode($message, true);
        $this->setData($data);
        $this->validate();
        if (!$data['sku'] && $data['tekoSku'] && $data['status'] == 'inactive') {
            $this->inactiveProducts('standard_product_id', $data['skuIdentifier']);
            $this->inactiveProducts('backup_wh_sku', $data['tekoSku']);
        }
        if ($data['sku']) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect(['status', 'visibility', 'warehouse_sku', 'price',])
                ->addAttributeToFilter('warehouse_sku', $data['sku'])
                ->addWebsiteFilter(20)
                ->getFirstItem();
            if ($product->getId()) {
                $this->updateVisibility($product, $data['status']);
            }
            if (!$product->getId()) {
                if (!$data['tekoSku'])
                    $this->initProduct();
                else {
                    $product = Mage::getResourceModel('catalog/product_collection')
                        ->addAttributeToFilter('standard_product_id', $data['skuIdentifier'])
                        ->addWebsiteFilter(20)
                        ->getFirstItem();
                    if ($product->getId()){
                        /*$product->addData([
                            'warehouse_sku' => $data['sku'],
                            'backup_wh_sku' => $data['tekoSku'],
                            'is_check_api' => true,
                            'standard_product_id' => $data['skuIdentifier'],
                            'name' => $data['name'],
                            'visibility' => $data['status'] == 'active' ? 4 : 1,
                        ])->save();*/
                    } else {
                        $this->initProduct();
                    }
                }
            }
        }

    }

    private function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @throws Queue_Exception
     */
    private function validate()
    {
        $this->checkIsset();
    }

    /**
     * @throws Queue_Exception
     */
    private function checkIsset()
    {
        foreach ($this->fields as $field)
            if (!array_key_exists($field, $this->data))
                throw new Queue_Exception("{$field} is required.");

    }

    private function inactiveProducts($key, $backupSku)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect([
                'status', 'visibility', 'price',
                'backup_wh_sku', 'standard_product_id', 'special_price'])
            ->addAttributeToFilter($key, $backupSku)
            ->addWebsiteFilter(20)
            ->addAttributeToFilter('visibility', 4)
            ->getFirstItem();
        if ($product->getId()) {
            $product->setData('visibility', 1);
            $product->getResource()->saveAttribute($product, 'visibility');
        }
    }

    private function initProduct()
    {
        $product = Mage::getModel('catalog/product');
        $product->setData([
            'name' => $this->data['name'],
            'backup_wh_sku' => $this->data['tekoSku'],
            'standard_product_id' => $this->data['skuIdentifier'],
            'warehouse_sku' => $this->data['sku'],
            'type' => 'simple',
            'sku' => $this->data['sku'],
            'attribute_set_id' => '4',
            'status' => 0,
            'is_check_api' => true,
            'website_ids' => [20],
        ]);
        $product->save();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string $status
     * @return bool
     */
    private function updateVisibility($product, $status)
    {
        if ($status == 'active' && $product->getData('visibility') == 1 && $product->getSpecialPrice() == 0) {
            $product->setData('visibility', 4);
            $product->getResource()->saveAttribute($product, 'visibility');
        }
        if ($status == 'inactive' && $product->getData('visibility') == 4) {
            $product->setData('visibility', 1);
            $product->getResource()->saveAttribute($product, 'visibility');
        }
        return true;
    }

}