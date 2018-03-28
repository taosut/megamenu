<?php

/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 12/20/2017
 * Time: 2:09 PM
 */
class Ved_AdminApi_Adminhtml_ApiController extends Ved_AdminApi_Controller_ApiController
{
    public function categoriesAction()
    {
        try {
            $storeId = 23; // store_id of tekshop v2
            $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('is_active')
                ->addFieldToFilter('path', array('like' => "1/$rootId/%"));
            $data = array();
            foreach ($categories as $category) {
                array_push($data, array(
                    'id' => $category->getId(),
                    'code' => $category->getUrlKey(),
                    'name' => $category->getName(),
                    'status' => $category->getIsActive(),
                    'created_at' => $category->getCreatedAt(),
                    'updated_at' => $category->getUpdatedAt(),
                    'deleted_at' => null,
                    'sort_weight' => $category->getPosition(),
                    'level' => $category->getLevel(),
                    'path' => $category->getPath(),
                    'parent' => $category->getParentCategory()->getId(),
                    'created_by' => null,
                    'updated_by' => null,
                ));
            }
            $result = array('status' => true, 'data' => $data);
        }
        catch (Exception $e) {
            $result = ['status' => false, 'data' => $e->getMessage(), 'code' => $e->getCode()];
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    public function productsAction()
    {
//        Mage::app()->setCurrentStore(23);
        try {
            $storeId = 23; // store_id of tekshop v2
            $productIds = array();
            if ($this->getRequest()->getParam('ids') != null) {
                $productIds = explode(',', $this->getRequest()->getParam('ids'));
            }
            $category_id = $this->getRequest()->getParam('category_id');
            $name = $this->getRequest()->getParam('name');
            $length = intval($this->getRequest()->getParam('length', 10)); // limit
            $start = intval($this->getRequest()->getParam('start', 0)); // offset
            $fromPrice = intval($this->getRequest()->getParam('from_price', null));
            $toPrice = intval($this->getRequest()->getParam('to_price', null));
            $sku = $this->getRequest()->getParam('sku', null);

            if (($category_id != null && !is_numeric($category_id)) ||
                ($fromPrice != null && !is_numeric($fromPrice)) ||
                ($toPrice != null && !is_numeric($toPrice)) ||
                (count($productIds) > 0 && !is_numeric(implode('', $productIds))) ||
                !is_numeric($length) || !is_numeric($start)) {
                throw new Exception("params must be numeric");
            }


            $products = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addUrlRewrite()
                ->addWebsiteFilter(20)
                ->addAttributeToFilter('status', 1)
                ->addAttributeToFilter('price', array('notnull' => true));

            if ($name){
                $products ->addAttributeToFilter('name', array('like' => "%$name%"));
            }

            if ($category_id != null) {
                $category = Mage::getModel('catalog/category')->load($category_id);
                $subCategories = explode(",", $category->getAllChildren());
                $products->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left')
                    ->addAttributeToFilter('category_id', array('in' => $subCategories));
            }

            if (count($productIds) > 0) {
                $products->addAttributeToFilter('standard_product_id', array('in' => $productIds));
            }

            if ($sku != null) {
                $products->addAttributeToFilter('warehouse_sku', array('eq' => $sku));
            }

            if ($fromPrice != null) {
                $products->addAttributeToFilter('price', array('gteq' => $fromPrice));
            }

            if ($toPrice != null) {
                $products->addAttributeToFilter('price', array('lteq' => $toPrice));
            }

            $data = array();
            $products->getSelect()->group('entity_id')->limit($length, $start);

            foreach ($products as $product) {
                // source_url
                $images = Mage::getModel('catalog/product')->load($product->getId())->getMediaGalleryImages();
                $img_array = array();
                foreach ($images as $image) {
                    $ele = array();
                    $ele['url'] = $image['url'];
                    $ele['position'] = $image->getPosition();
                    array_push($img_array, $ele);
                }
                $source_url = array(
                    'base_image' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage(),
                    'image' => $img_array
                );
                // attributes
                $attributes = $product->getAttributes();
                $attr_data = array();
                foreach ($attributes as $attribute) {
                    if ($attribute->getAttributeCode() != 'warranty_return' && $attribute->getAttributeCode() != 'description')
                        $attr_data[$attribute->getAttributeCode()] = $attribute->getFrontend()->getValue($product);
                }

                array_push($data, array(
                    'id' => $product->getStandardProductId(),
                    "category_id" => $product->getCategoryIds(),
                    'name' => $product->getName(),
                    'source_url' => $source_url,
                    'old_sku' => $product->getBackupWhSku(),
                    'sku' => $product->getWarehouseSku(),
                    'pv_sku' => null,
                    'price' => $product->getPrice(),
                    'instock_status' => $product->getInstockStatus(),
                    'image' => $source_url,
                    'description' => $product->getShortDescription(),
                    'attributes' => $attr_data
                ));
            }
            $result = array('status' => true, 'data' => $data);
        }
        catch (Exception $e) {
            $result = ['status' => 'error', 'data' => $e->getMessage(), 'code' => $e->getCode()];
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }
}
