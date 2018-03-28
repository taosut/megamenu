<?php
/**
 * FAQ accordion for Magento
 */

/**
 * Category Resource Model for FAQ Items
 *
 * Website: www.abc.com
 * Email: honeyvishnoi@gmail.com
 */
class Ved_Hotdeal_Model_Resource_Category extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('ved_hotdeal/category', 'id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select->join(
                array('nns' => 'hot_deal_category_store'),
                $this->getMainTable() . '.item_id = `nns`.category_id'
            )->where('is_active=1 AND `nns`.store_id in (0, ?) ',
                $object->getStoreId())->order('creation_time DESC')->limit(1);
        }
        return $select;
    }

    /**
     * Sets the creation and update timestamps
     *
     * @param Mage_Core_Model_Abstract $object Current faq category
     * @return Ved_Hotdeal_Model_Mysql4_Category
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Assign page to store views
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('category_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete('hot_deal_category_store', $condition);
        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['category_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert(
                'hot_deal_category_store', $storeArray
            );
        }
        return parent::_afterSave($object);
    }

    /**
     * Do store processing after loading
     *
     * @param Mage_Core_Model_Abstract $object Current faq item
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()->from(
            'hot_deal_category_store'
        )->where('category_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }

        return parent::_afterLoad($object);
    }
}
