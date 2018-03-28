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
class Ved_Hotdeal_Model_Resource_Category_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_previewFlag;
    
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('ved_hotdeal/category');
    }
    
    /**
     * Add Filter by store
     *
     * @param int|Mage_Core_Model_Store $store Store to be filtered
     * @return Ved_Hotdeal_Model_Mysql4_Category_Collection
     */
    public function addStoreFilter($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }
        
        $this->getSelect()->join(
            array('store_table' => $this->getTable('ved_hotdeal/category_store')),
            'main_table.id = store_table.category_id',
            array ()
        )->where('store_table.store_id in (?)', array (
            0, 
            $store
        ))->group('main_table.id');
        
        return $this;
    }
    
    /**
     * After load processing - adds store information to the datasets
     *
     */
    protected function _afterLoad()
    {
        if ($this->_previewFlag) {
            $items = $this->getColumnValues('faq_id');
            if (count($items)) {
                $select = $this->getConnection()->select()->from(
                    $this->getTable('ved_hotdeal/category_store')
                )->where(
                    $this->getTable('ved_hotdeal/category_store') . '.category_id IN (?)',
                    $items
                );
                if ($result = $this->getConnection()->fetchPairs($select)) {
                    foreach ($this as $item) {
                        if (!isset($result[$item->getData('category_id')])) {
                            continue;
                        }
                        if ($result[$item->getData('category_id')] == 0) {
                            $stores = Mage::app()->getStores(false, true);
                            $storeId = current($stores)->getId();
                            $storeCode = key($stores);
                        }
                        else {
                            $storeId = $result[$item->getData('category_id')];
                            $storeCode = Mage::app()->getStore($storeId)->getCode();
                        }
                        $item->setData('_first_store_id', $storeId);
                        $item->setData('store_code', $storeCode);
                    }
                }
            }
        }
        
        parent::_afterLoad();
    }
    
    protected function _toOptionArray($valueField = 'id', $labelField = 'name', $additional = array())
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }
    
    protected function _toOptionHash($valueField = 'id', $labelField='name')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    public function addIsActiveFilter()
    {
        $this->addFilter('is_active', 1);
        return $this;
    }
}
