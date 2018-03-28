<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 1/17/2017
 * Time: 4:02 PM
 */
class Ved_Purchase_Block_Adminhtml_NewPurchase extends Mage_Adminhtml_Block_Template {

    public function __construct()
    {
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();

        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('id')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('attribute_set_id')
            ->addWebsiteFilter($websiteIds);

        $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
        $collection->joinAttribute(
            'name',
            'catalog_product/name',
            'entity_id',
            null,
            'inner',
            $adminStore
        );

        $collection->joinAttribute(
            'origin_price',
            'catalog_product/price',
            'entity_id',
            null,
            'inner',
            $adminStore
        );

        $this->product_count = $collection->count();

        $collection->setPageSize(10)->setCurPage(1);
        $this->products = $collection->getData();

        $this->stores = Mage::getModel("core/store")->getCollection()
            ->addFieldToFilter('store_id', array('gt' => '0'))
            ->addFieldToFilter('website_id', array('in' => $websiteIds))
            ->getData();

    }

    private $products;
    private $product_count;
    private $stores;


    protected function _prepareLayout()
    {
        //$this->setChild('grid', $this->getLayout()->createBlock('ved_purchase/Adminhtml_Editpurchase_Grid', 'ved_purchase_item_grid'));
        return parent::_prepareLayout();
    }

    public function getProducts(){
        return json_encode($this->products);
    }

    public function getProductCount(){
        return json_encode($this->product_count);
    }

    public function getStores(){
        return json_encode($this->stores);
    }

}