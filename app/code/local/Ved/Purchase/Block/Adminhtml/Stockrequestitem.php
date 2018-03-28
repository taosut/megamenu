<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/8/2016
 * Time: 10:03 AM
 */
class Ved_Purchase_Block_Adminhtml_Stockrequestitem extends Mage_Adminhtml_Block_Template {

    public function __construct()
    {
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);

        $requestItemCollection = Mage::getModel("ved_purchase/stockrequestitem")->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('website_id',
                array(
                    array('in'=> array(array_merge(array('0'),$websiteIds))),
                )
            )
            ->addFieldToSelect('request_store_id')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('store_name')
            ->addFieldToSelect('request_store_name')
            ->addFieldToSelect('website_id')
            ->addExpressionFieldToSelect('total_product', 'sum({{quantity}})', 'quantity');

        $requestItemCollection->getSelect()->group(array('request_store_id', 'store_id'));

//        $requestItemCollection->getSelect()
//            ->joinLeft(
//                array('website' => $requestItemCollection->getTable('core/website')),
//                'website.website_id = main_table.website_id', array('website.name as website_name', 'website.website_id', 'website.code as website_code'));

        $this->requestTransfer = $requestItemCollection->getData();

    }

    private $requestItems;
    private $requestTransfer;

    public function getRequestItems(){
        return json_encode($this->requestItems);
    }

    public function getRequestStockTransfer(){
        return json_encode($this->requestTransfer);
    }

    protected function _prepareLayout()
    {
        //$this->setChild('grid', $this->getLayout()->createBlock('ved_purchase/Adminhtml_Editpurchase_Grid', 'ved_purchase_item_grid'));
        return parent::_prepareLayout();
    }

}