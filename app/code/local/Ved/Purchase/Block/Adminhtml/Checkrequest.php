<?php
/**
 * Created by PhpStorm.
 * User: Loinp
 * 0
 *
 * Date: 11/8/2017
 * Time: 1:49 PM
 */
class Ved_Purchase_Block_Adminhtml_Checkrequest extends Mage_Adminhtml_Block_Template {
    public function __construct()
    {
        try {
            $admin_user_session = Mage::getSingleton('admin/session');
            $adminUserId = $admin_user_session->getUser()->getUserId();
            $helper = Mage::helper("purchase");
            $websiteIds = $helper->getWebsiteByUserId($adminUserId);

            $requestItem = Mage::getModel("ved_purchase/requestitem")->getCollection()
                ->addFieldToSelect('*')
                ->addExpressionFieldToSelect('sum_qty',
                    '(SELECT SUM(`a`.`quantity`) FROM `sales_flat_purchase_request_item` AS `a` WHERE `a`.`sku` = `main_table`.`sku`
                    AND `a`.`pre_status` = 0 AND `a`.`status` = 1 GROUP BY `a`.`sku`)',
                    [])
                ->addExpressionFieldToSelect('count_order',
                    '(SELECT COUNT(`a`.`order_id`) FROM `sales_flat_purchase_request_item` AS `a` WHERE `a`.`sku` = `main_table`.`sku`
                    AND `a`.`pre_status` = 0 AND `a`.`status` = 1 GROUP BY `a`.`sku`)',
                    [])
                ->addFieldToFilter('website_id',
                    array(
                        array('in' => array(array_merge(array('0'), $websiteIds))),
                    )
                )
                ->addFieldToFilter('pre_status', 0)
                ->addFieldToFilter('status', 1)->getData();
            $requestItemByOrderCollection = Mage::getModel("ved_purchase/requestitem")->getCollection()
                ->addFieldToSelect('order_id')
                ->addFieldToSelect('order_increment_id')
                ->addFieldToSelect('store_id')
                ->addFieldToSelect('store_name')
                ->addFieldToFilter('website_id',
                    array(
                        array('in' => array(array_merge(array('0'), $websiteIds))),
                    )
                )
                ->addFieldToFilter('pre_status', 0)
                ->addFieldToFilter('status', 1);
            $requestItemByOrderCollection->getSelect()->group(['order_id']);
            $requestItemByOrderCollection->setOrder('order_id', 'DESC');
            $requestItemByOrder = $requestItemByOrderCollection->getData();
            foreach($requestItemByOrder as $key => $item) {
                $requestItemByOrder[$key]['item_list'] = array();
                foreach ($requestItem as $detail) {
                    if ($item['order_id'] == $detail['order_id']) {
                        $requestItemByOrder[$key]['item_list'][] = $detail;
                    }
                }
            }
            $this->requestItem = $requestItemByOrder;
        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }
    private $requestItem;

    public function getRequestItem()
    {
        return json_encode($this->requestItem);
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}