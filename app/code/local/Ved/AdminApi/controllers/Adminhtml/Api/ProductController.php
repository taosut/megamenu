<?php

/**
 * Class Ved_AdminApi_Adminhtml_Api_ProductController
 * @property array _detailUpdatePrice
 */
class Ved_AdminApi_Adminhtml_Api_ProductController extends Ved_AdminApi_Controller_ApiController
{
    /**
     * Update Price
     */
    public function updatePriceAction()
    {
        try {
            if (!$this->getRequest()->isPost()) throw  new Exception('Method not allow');
            $this->_detailUpdatePrice = [];
            $data = $this->getRequest()->getRawBody();
            $input = json_decode($data, true);
            foreach ($input['storeIds'] as $storeId) {
                switch ($storeId) {
                    case 1;
                        $this->updateProductPrice($input);
                        break;
                    case 3;
                        $this->updateProductPrice($input, 2);
                        break;
                    default:
                        break;
                }
            }
            $response = ['status' => 'success', 'detail' => $this->_detailUpdatePrice];
        } catch (Exception $e) {
            $response = ['status' => 'fail', 'message' => $e->getMessage()];
        }
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Update deposit
     */
    public function updateDepositAction()
    {
        try {
            if (!$this->getRequest()->isPost()) throw  new Exception('Method not allow');
            $data = $this->getRequest()->getRawBody();
            $input = json_decode($data, true);
            /**
             * @var Mage_Sales_Model_Order $order
             */
            $order = Mage::getModel('sales/order')->load($input['orderId']);
            $order->updateDepositAmount($input['deposit']);
            $response = ['status' => 'success'];
        } catch (Exception $e) {
            $response = ['status' => 'fail', 'message' => $e->getMessage()];
        }
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Update price for Product
     * @param array $input
     * @param int $type
     */
    private function updateProductPrice($input, $type = 1)
    {
        $siteOnline = explode(',', (string)Mage::getConfig()->getNode('global/site_online'));
        $standardProductIdAttribute = Mage::getModel('teko_amp/attribute')
            ->getCollection()
            ->addFilter('attribute_code', 'standard_product_id')
            ->getFirstItem()->getAttributeId();
        /**
         * @var Teko_Amp_Model_Resource_Product_Collection $products
         */
        $products = Mage::getModel('teko_amp/varchar')->getCollection()
            ->addFieldToFilter('attribute_id', $standardProductIdAttribute)
            ->addFieldToFilter('store_id', 0)
            ->addFieldToFilter('value', $input['productId'])
            ->load();

        foreach ($products as $product) {
            /**
             * @var Teko_Amp_Model_Product $model
             * @var Mage_Catalog_Model_Product $catalogProduct
             */
            $model = Mage::getModel('teko_amp/product')->load($product->getEntityId());
            $catalogProduct = Mage::getModel('catalog/product')->load($product->getEntityId());
            if ($type == 1)
                $storeIds = array_intersect($catalogProduct->getStoreIds(), $siteOnline);
            if ($type == 2) {
                $storeIds = array_diff($catalogProduct->getStoreIds(), $siteOnline);
                /**
                 * @var Mage_Core_Model_Resource_Store_Collection $storeCollection
                 */
                $storeCollection = Mage::getModel('core/store')->getCollection();
                $storeCollection->join(['region' => 'teko_amp/region'], 'main_table.code = region.code', 'region.region_id')
                    ->addFieldToFilter('region.area', ['in' => $input['regionNames']]);
                $storeCollection->getSelect()->group('main_table.store_id');
                $storeCollection->load();
                $storeIds = array_intersect($storeIds, $storeCollection->getColumnValues('store_id'));
            }
            foreach ($storeIds as $storeId)
                $model->setPriceWithStore($storeId, $input['price']);
            if ($storeIds) {
                $this->_detailUpdatePrice[$product->getEntityId()] = $storeIds;
            }
        }
    }

    public function updatePriceWithRegionAction()
    {
        try {
            if (!$this->getRequest()->isPost()) throw  new Exception('Method not allow');
            $this->_detailUpdatePrice = [];
            $data = $this->getRequest()->getRawBody();
            $input = json_decode($data, true);
            foreach ($input['data'] as $detail) {
                /**
                 * @var Mage_Catalog_Model_Product $product
                 */
                $product = new Ved_AdminApi_Model_Resource_Product_Collection();
                $this->_detailUpdatePrice = $product->setPriceWithRegion($detail);
            }
            $response = ['status' => 'success', 'detail' => $this->_detailUpdatePrice];
        } catch (Exception $e) {
            Mage::logException($e);
            $response = ['status' => 'fail', 'message' => $e->getMessage()];
        }
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     *
     */
    public function update_statusAction()
    {
        try {
            if (!$this->getRequest()->isPost()) throw  new Exception('Method not allow');
            $this->_detailUpdatePrice = [];
            $data = $this->getRequest()->getRawBody();
            $input = json_decode($data, true);
            /**
             * @var Mage_Catalog_Model_Product $product
             */
            $product = new Ved_AdminApi_Model_Resource_Product_Collection();
            $this->_detailUpdatePrice = $product->updateStatus(
                $input['data']['products'],
                $input['data']['status'],
                $input['data']['regionId']
            );
            $response = ['status' => 'success', 'detail' => $this->_detailUpdatePrice];
        } catch (Exception $e) {
            Mage::logException($e);
            $response = ['status' => 'error', 'message' => $e->getMessage(), 'code' => $e->getCode()];
        }
        $this->getResponse()->setBody(json_encode($response));
    }

    public function getProductTekshopAction()
    {
        try {

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $limit = intval($this->getRequest()->getParam('limit', 50));
            $offset = intval($this->getRequest()->getParam('offset', 0));

            $query = "select a.entity_id, b.attribute_set_name, a.name, a.price, a.sku, c.value 'warehouse_sku'   
                      from catalog_product_flat_23 a 
                      join eav_attribute_set b on a.attribute_set_id = b.attribute_set_id
                      join catalog_product_entity_varchar c on a.entity_id = c.entity_id and c.attribute_id = 334 and a.type_id = 'simple' 
                      limit $offset, $limit";

            $order = $read->fetchAll($query);

            $result = $order;
        } catch (Exception $e) {
            $result = array();
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    public function getProductTekEduAction()
    {
        try {

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $limit = intval($this->getRequest()->getParam('limit', 50));
            $offset = intval($this->getRequest()->getParam('offset', 0));

            $query = "select distinct a.entity_id, 'Hàng hóa' as `type`, d.value `warehouse_sku`, 
                      a.price, a.name,
                      concat('http://tekshop.edu.vn/media/catalog/product',a.small_image ) image, 0 'serial'
                      from catalog_product_flat_22 a 
                      join catalog_product_entity_varchar d on a.entity_id = d.entity_id and d.attribute_id = 334 and a.type_id = 'simple' 
                      group by d.value
                      limit $offset, $limit";

            $order = $read->fetchAll($query);

            $result = $order;
        } catch (Exception $e) {
            $result = array();
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    /**
     *
     */
    public function sync_products_tekshopAction()
    {
        try {
            /**
             * @var Magento_Db_Adapter_Pdo_Mysql $read
             */
            $limit = intval($this->getRequest()->getParam('limit', 100));
            $offset = intval($this->getRequest()->getParam('offset', 0));
            /**
             * @var  Ved_AdminApi_Model_Resource_Product_Collection $productCollection
             */
            $productCollection = new Ved_AdminApi_Model_Resource_Product_Collection();
            $productCollection->addStoreFilter(20)
                ->addAttributeToFilter('status', true)
                ->addAttributeToSelect([
                    'name', 'price', 'attribute_set_id',
                    'warehouse_sku', 'media_gallery',
                    'manufacturer', 'attribute_set_name'
                ])
                ->addAttributeToFilter('visibility', ['neq' => 1])
                ->addAttributeToFilter('type_id', 'simple');
            $productCollection->setPage($offset, $limit)->load();
            $listProducts = [];
            $attributeSets = Mage::getModel('eav/entity_attribute_set')
                ->getCollection()
                ->load()
                ->toArray(['attribute_set_id', 'attribute_set_name']);
            $attributeSets = array_combine(
                array_column($attributeSets['items'], 'attribute_set_id'),
                array_column($attributeSets['items'], 'attribute_set_name')
            );
            foreach ($productCollection as $product) {
                /**
                 * @var Mage_Catalog_Model_Product $product
                 */
                $product->getResource()->getAttribute('media_gallery')
                    ->getBackend()->afterLoad($product);
                $entityId = $product->getId();
                $listProducts[$entityId] = $product->toArray([
                    'entity_id', 'attribute_set_id',
                    'name', 'price', 'sku', 'warehouse_sku',
                    'attribute_set_name'
                ]);
                if (is_object($product->getMediaGalleryImages())) {
                    $images = $product->getMediaGalleryImages()->toArray(['url']);
                    $listProducts[$entityId]['images'] = array_column($images['items'], 'url');
                }
                if (isset($listProducts[$entityId]['stock_item'])) unset($listProducts[$entityId]['stock_item']);
                if ($product->getManufacturer()) $listProducts[$entityId]['brand'] = $product->getAttributeText('manufacturer');
                if (isset($attributeSets[$product->getAttributeSetId()]))
                    $listProducts[$entityId]['attribute_set_name'] =
                        isset($attributeSets[$product->getAttributeSetId()]) ?
                            $attributeSets[$product->getAttributeSetId()] : "";
            }
            $count = Mage::getModel('catalog/product')->getCollection()
                ->addStoreFilter(20)
                ->addAttributeToFilter('status', true)
                ->addAttributeToFilter('visibility', ['neq' => 1])
                ->addAttributeToFilter('type_id', 'simple')->count();
            $result = ['status' => 'success', 'data' => $listProducts, 'total' => $count];
        } catch (Exception $e) {
            $result = ['status' => 'error', 'data' => $e->getTraceAsString(), 'total' => 0];
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    public function detail_product_tekshopAction()
    {
        try {
            /**
             * @var Magento_Db_Adapter_Pdo_Mysql $read
             */
            $entityId = intval($this->getRequest()->getParam('id', 0));
            $product = Mage::getModel('catalog/product')->load($entityId);
            if (!$product->getId()) throw new Exception("Product Not Found", 99);
            $product = $product->toArray([
                'entity_id', 'meta_title', 'meta_description',
                'description', 'short_description', 'price',
                'image', 'small_image', 'thumbnail'
            ]);
            unset($product['stock_item']);
            $product['image'] = Mage::app()->setCurrentStore(20)->getStore()->getBaseUrl('media') . "catalog/product" . $product['image'];
            $product['small_image'] = Mage::app()->setCurrentStore(20)->getStore()->getBaseUrl('media') . "catalog/product" . $product['small_image'];
            $product['thumbnail'] = Mage::app()->setCurrentStore(20)->getStore()->getBaseUrl('media') . "catalog/product" . $product['thumbnail'];
            $result = ['status' => 'success', 'data' => $product];
        } catch (Exception $e) {
            $result = ['status' => 'error', 'data' => $e->getMessage(), 'code' => $e->getCode()];
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    public function getPurchaseAction()
    {
        try {

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $limit = intval($this->getRequest()->getParam('limit', 50));
            $offset = intval($this->getRequest()->getParam('offset', 0));

            $query = "select a.store_name store, a.id, a.code, a.supplier_id, a.supplier_name supplier, b.product_id, b.product_sku, b.product_name, b.request_qty, b.price, DATE_ADD(a.created_at,INTERVAL 7 hour) request_at , 
                        case when a.status = 0 then 'Đã hủy' 
                        when a.status = 1 then 'Chờ xử lý' 
                        when a.status = 2 then 'Đã xử lý' end 'status' 
                      from sales_flat_purchase a join sales_flat_purchase_item b on a.id = b.purchase_id
                        where DATE_ADD(a.created_at,INTERVAL 7 hour) > '2017-08-01'
                      limit $offset, $limit";

            $order = $read->fetchAll($query);

            $result = $order;
        } catch (Exception $e) {
            $result = array();
        }
        $this->getResponse()->setBody(json_encode($result));
    }
}