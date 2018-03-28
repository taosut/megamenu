<?php
/**
 * FAQ accordion for Magento
 */

/**
 * FAQ accordion for Magento
 *
 * Website: www.abc.com
 * Email: honeyvishnoi@gmail.com
 */
class Teko_Amp_Model_Resource_Product_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_previewFlag;

    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('teko_amp/product');
    }

    /**
     * @param array $sku
     * @param bool $status
     * @return array
     */
    public function updateStatus($sku, $status)
    {
        $warehouseSkuAttribute = Mage::getModel('teko_amp/attribute')
            ->getCollection()
            ->addFilter('attribute_code', 'warehouse_sku')
            ->getFirstItem()->getAttributeId();
        /**
         * @var  Teko_Amp_Model_Resource_Product_Collection $products
         */
        $products = Mage::getModel('teko_amp/varchar')->getCollection()
            ->addFieldToFilter('attribute_id', $warehouseSkuAttribute)
            ->addFieldToFilter('store_id', 0)
            ->addFieldToFilter('value', ['in' => $sku])
            ->load();
        return [];
    }
}
