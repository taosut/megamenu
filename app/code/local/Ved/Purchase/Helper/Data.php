<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/5/2016
 * Time: 5:25 PM
 */
class Ved_Purchase_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getWebsiteByUserId($userId = 0){

        $userStoreMappings = Mage::getModel("ved_purchase/mapping")->getCollection();

        $userStoreMappings
            ->addFieldToFilter('user_id', $userId);
        $result = array();
        foreach($userStoreMappings as $userStoreMapping){
            $result[] = $userStoreMapping->getWebsiteId();
        }
        return $result;
    }

    public static function getUrl($route='', $params=array())
    {
        return Mage::getModel('adminhtml/url')->getUrl($route, $params);
    }
}