<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/8/2016
 * Time: 10:03 AM
 */
class Ved_Purchase_Block_Adminhtml_Purchaseorder extends Mage_Adminhtml_Block_Template {

    public function __construct()
    {
//        var_dump(1); die();
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);
        //var_dump($websiteIds);die();

        $requestItemCollection = Mage::getModel("ved_purchase/requestitem")->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('website_id',
                array(
                    array('in'=> array(array_merge(array('0'),$websiteIds))),
                    //array('in'=> array($websiteIds)),
                )
            )
            ->addFieldToSelect('supplier_id')
            ->addFieldToSelect('supplier_name')
            ->addFieldToSelect('supplier_info')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('website_id')
            ->addFieldToSelect('store_name')
            ->addExpressionFieldToSelect('total_product', 'sum({{quantity}})', 'quantity');

//        var_dump($requestItemCollection->getData()); die();


        $requestItemCollection->getSelect()->group(array('supplier_id', 'store_id'));

//        $requestItemCollection->getSelect()
//            ->joinLeft(
//                array('website' => $requestItemCollection->getTable('core/website')),
//                'website.website_id = main_table.website_id', array('website.name as website_name', 'website.website_id', 'website.code as website_code'));

        $this->requestSupplier = $requestItemCollection->getData();

        $this->stores = Mage::getModel("core/store")->getCollection()
            ->addFieldToFilter('store_id', array('gt' => '0'))
            ->addFieldToFilter('website_id', array('in' => $websiteIds))
            ->getData();

//         var_dump($this->stores);die();

    }

    private $requestItems;
    private $requestSupplier;
    private $stores;

    public function getRequestItems(){
        return json_encode($this->requestItems);
    }

    public function getRequestSupplier(){
        return json_encode($this->requestSupplier);
    }

    public function getStores(){
        return json_encode($this->stores);
    }

    protected function _prepareLayout()
    {
        //$this->setChild('grid', $this->getLayout()->createBlock('ved_purchase/Adminhtml_Editpurchase_Grid', 'ved_purchase_item_grid'));
        return parent::_prepareLayout();
    }

}