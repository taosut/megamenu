<?php

/**
 * Class Ved_AdminApi_Model_Resource_Product_Collection
 */
class Ved_AdminApi_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * @param array $detail
     * @return array
     */
    public function setPriceWithRegion($detail)
    {
        $response = [];
        $collection = $this->addAttributeToFilter('warehouse_sku', $detail['sku'])->load();
        /**
         * @var Mage_Catalog_Model_Product $item
         */
        foreach ($collection as $item) {
            $region = $this->getRegion($detail['region_id']);
            $regionCollection = new Ved_AdminApi_Model_Resource_Region_Collection();
            $storeCode = $regionCollection->addAreaToFilter($region)->getCodes();
            $storeCollection = new Ved_AdminApi_Model_Resource_Store_Collection();
            $storeIds = $storeCollection->addCodeToFilter($storeCode)->getStoreIds();
            $storeIds = array_intersect($storeIds, $item->getStoreIds());
            foreach ($storeIds as $storeId) {
                /**
                 * @var Teko_Amp_Model_Product $TEKOAmpProduct
                 */
                $TEKOAmpProduct = Mage::getModel('teko_amp/product')->load($item->getId());
                $TEKOAmpProduct->setPriceWithStore($storeId, (float)$detail['price']);
                $response[$detail['sku']][$item->getSku()][] = $storeId;
            }
        }
        return $response;
    }

    /**
     * @param int $id
     * @return string
     */
    private function getRegion($id)
    {
        switch ($id):
            case 1:
                return "Mien Bac";
            case 2:
                return "Mien Trung";
            case 3:
                return "Mien Nam";
            default:
                return "Mien Bac";
        endswitch;
    }

    /**
     * @param  string $warehouseSku
     * @param bool|int $status
     * @param $region
     * @return $this
     * @internal param string $channel
     */
    public function updateStatus($warehouseSku, $status, $region)
    {
        if (!!$status)
            return [];
        $storeIds = $this->getStoreFromRegion($region);
        /**
         * @var Ved_AdminApi_Model_Resource_Product_Collection $collection
         */
        $collection = $this->addAttributeToFilter('warehouse_sku', ['like', $warehouseSku]);
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->load();
        /**
         * @var Mage_Catalog_Model_Product $item
         */
        $response = [];
        foreach ($collection as $item) {
            $storeIdsUpdate = array_intersect($storeIds, $item->getStoreIds());
            $indexer = new Mage_Catalog_Model_Product_Flat_Indexer();
            foreach ($storeIdsUpdate as $storeId) {
                $TEKOAmpProduct = new Teko_Amp_Model_Product();
                $TEKOAmpProduct->load($item->getId());
                $TEKOAmpProduct->inactiveWithStore($storeId);
                $response[$warehouseSku][$item->getSku()][] = $storeId;
            }
            Mage::getModel('ved_adminapi/inactive')->addData([
                'entity_id' => $item->getId(),
                'created_at' => now()
            ])->save();
        }
        return $response;
    }

    private function getStoreFromRegion($regionCode)
    {
        $region = $this->getRegion($regionCode);
        $regionCollection = new Ved_AdminApi_Model_Resource_Region_Collection();
        $storeCode = $regionCollection->addAreaToFilter($region)->getCodes();
        $storeCollection = new Ved_AdminApi_Model_Resource_Store_Collection();
        $storeIds = $storeCollection->addCodeToFilter($storeCode)->getStoreIds();
        return $storeIds;
    }

    /**
     * @param array $input
     * @param $store
     * @return Mage_Catalog_Model_Product
     * @throws Exception
     * @internal param int $standardProductId
     */
    public function getProductWithStandardProductId($input, $store)
    {
        $products = $this->addAttributeToFilter('warehouse_sku', $input['product_sku'])
            ->addAttributeToSelect('warehouse_sku')
            ->addAttributeToFilter('type_id', 'simple')
            ->addWebsiteFilter(20)
            ->setOrder('status', 'asc')
            ->load();
        $product = null;
        foreach ($products as $key => $value) {
            if (in_array($store->getId(), $value->getStoreIds())) {
                $product = $value;
                break;
            }
        }
        if (is_null($product) && $products->count()) {
            $productsArray = $products->getItems();
            reset($productsArray);
            $product = current($productsArray);
        }
        if (!is_a($product, Mage_Catalog_Model_Product::class) || !$product->getId())
            throw new Exception("Không tìm thấy sản phẩm id : {$input['product_sku']}", 99);
        return $product;
    }
}