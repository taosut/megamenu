<?php

/**
 * Class Ved_Productqc_Helper_Data
 */
class Ved_Productqc_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getWebsiteByUserId($userId = 0)
    {

        $userStoreMappings = Mage::getModel("ved_productqc/mapping")->getCollection();

        $userStoreMappings
            ->addFieldToFilter('user_id', $userId);
        $result = array();
        foreach ($userStoreMappings as $userStoreMapping) {
            $result[] = $userStoreMapping->getWebsiteId();
        }
        return $result;
    }

    /**
     * @return mixed|string
     */
    public function getType()
    {
        $type = Mage::app()->getRequest()->getParam('type');
        if ($type) {
            return $type;
        }
        $id = Mage::app()->getRequest()->getParam('id');
        if (!$id)
            return 'simple';
        /**
         * @var Mage_Catalog_Model_Product $product
         */
        $product = Mage::getModel('catalog/product')->load($id);
        return $product->getTypeId();
    }
}