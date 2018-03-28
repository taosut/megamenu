<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 5/11/2017
 * Time: 4:54 PM
 */
class Teko_Amp_Model_Product extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('teko_amp/product');
    }

    public function setPriceWithStore($store, $price, $index = true)
    {
        $attributeId = Mage::getModel('teko_amp/attribute')
            ->getCollection()
            ->addFieldToFilter('attribute_code', 'price')
            ->getFirstItem()
            ->getAttributeId();
        /**
         * @var Teko_Amp_Model_Decimal $decimal
         */
        $decimal = Mage::getModel('teko_amp/decimal')
            ->getCollection()
            ->addFieldToFilter('entity_id', $this->getEntityId())
            ->addFieldToFilter('store_id', $store)
            ->addFieldToFilter('attribute_id', $attributeId)
            ->getFirstItem();
        if (!$decimal->isEmpty())
            $decimal->setValue($price)->save();
        else
            Mage::getModel('teko_amp/decimal')->setData([
                'entity_type_id' => 4,
                'attribute_id' => $attributeId,
                'store_id' => $store,
                'entity_id' => $this->getEntityId(),
                'value' => $price,
            ])->save();
        if ($index)
            $this->indexProductPrice($store, $price);
    }

    private function indexProductPrice($store, $price)
    {
        $store = (int)$store;
        $price = (int)$price;
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->update(
            "catalog_product_index_price",
            array("price" => $price, "final_price" => $price, 'min_price' => $price, 'max_price' => $price),
            "entity_id={$this->getId()} and website_id = $store"
        );
        $write->update(
            "catalog_product_index_price_idx",
            array("price" => $price, "final_price" => $price, 'min_price' => $price, 'max_price' => $price),
            "entity_id={$this->getId()} and website_id = $store"
        );
        $write->update(
            "catalog_product_flat_{$store}",
            array("price" => $price),
            "entity_id={$this->getId()}"
        );
    }

    /**
     * @param int $storeId
     */
    public function inactiveWithStore($storeId)
    {
        $attributeId = Mage::getModel('teko_amp/attribute')
            ->getCollection()
            ->addFieldToFilter('attribute_code', 'status')
            ->getFirstItem()
            ->getAttributeId();
        /**
         * @var Teko_Amp_Model_Decimal $decimal
         */
        $int = Mage::getModel('teko_amp/int')
            ->getCollection()
            ->addFieldToFilter('entity_id', $this->getEntityId())
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('attribute_id', $attributeId)
            ->getFirstItem();
        if (!$int->isEmpty())
            $int->setValue(Mage_Catalog_Model_Product_Status::STATUS_DISABLED)->save();
        else
            Mage::getModel('teko_amp/int')->setData([
                'entity_type_id' => 4,
                'attribute_id' => $attributeId,
                'store_id' => $storeId,
                'entity_id' => $this->getEntityId(),
                'value' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
            ])->save();
        $indexer = new Mage_Catalog_Model_Product_Flat_Indexer();
        $indexer->updateProductStatus($this->getEntityId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED, $storeId);
    }


}