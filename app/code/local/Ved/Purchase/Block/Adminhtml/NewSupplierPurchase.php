<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 1/17/2017
 * Time: 4:02 PM
 */
class Ved_Purchase_Block_Adminhtml_NewSupplierPurchase extends Mage_Adminhtml_Block_Template {

    private $stores;

    public function __construct()
    {
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();

        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);

        $this->stores = Mage::getModel("core/store")->getCollection()
            ->addFieldToFilter('store_id', array('gt' => '0'))
            ->addFieldToFilter('website_id', array('in' => $websiteIds))
            ->getData();
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getStores(){
        return json_encode($this->stores);
    }
}