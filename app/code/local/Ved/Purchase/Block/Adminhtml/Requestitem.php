<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/8/2016
 * Time: 10:03 AM
 */
class Ved_Purchase_Block_Adminhtml_Requestitem extends Mage_Adminhtml_Block_Template {

    public function __construct()
    {
        try {
            $admin_user_session = Mage::getSingleton('admin/session');
            $adminuserId = $admin_user_session->getUser()->getUserId();
            $helper = Mage::helper("purchase");
            $websiteIds = $helper->getWebsiteByUserId($adminuserId);

            $requestItemCollection = Mage::getModel("ved_purchase/requestitem")->getCollection()
                ->addFieldToFilter('main_table.status', 1)
//                ->addFieldToFilter('pre_status', 1)
                ->addFieldToFilter('website_id',
                    array(
                        array('in' => array(array_merge(array('0'), $websiteIds))),
                    )
                )
                ->addFieldToSelect('supplier_id')
                ->addFieldToSelect('supplier_name')
                ->addFieldToSelect('supplier_info')
                ->addFieldToSelect('store_id')
                ->addFieldToSelect('website_id')
                ->addFieldToSelect('store_name')
                ->addExpressionFieldToSelect('total_product', 'sum({{quantity}})', 'quantity')
                ->addExpressionFieldToSelect(
                    'non_assignee',
                    'SUM(CASE WHEN {{assignee}} IS NULL THEN {{quantity}} ELSE 0 END)',
                    array('assignee' => 'assignee', 'quantity' => 'quantity')
                )
                ->addExpressionFieldToSelect(
                    'owner_assignee',
                    'SUM(CASE WHEN {{assignee}}=' . $adminuserId . ' THEN {{quantity}} ELSE 0 END)',
                    array('assignee' => 'assignee', 'quantity' => 'quantity')
                )
                ->addExpressionFieldToSelect(
                    'other_assignee',
                    'SUM(CASE WHEN ({{assignee}} is not null and {{assignee}} != ' . $adminuserId . ') THEN {{quantity}} ELSE 0 END)',
                    array('assignee' => 'assignee', 'quantity' => 'quantity')
                );

//            $requestItemCollection->getSelect()->join(
//                array('order' => 'sales_flat_order'),
//                'order.entity_id = main_table.order_id',
//                array()
//            );
//            $requestItemCollection->addFieldToFilter('order.status', array('like' => 'telephone_confirm'));

            $requestItemCollection->getSelect()->group(array('supplier_id', 'store_id'));
            $requestItemCollection->setOrder('owner_assignee', 'DESC');

            $this->requestSupplier = $requestItemCollection->getData();

            $this->stores = Mage::getModel("core/store")->getCollection()
                ->addFieldToFilter('store_id', array('gt' => '0'))
                ->getData();
            // var_dump($this->requestSupplier);die();
        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
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