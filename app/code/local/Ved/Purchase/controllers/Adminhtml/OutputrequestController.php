<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/5/2016
 * Time: 5:33 PM
 */
class Ved_Purchase_Adminhtml_OutputrequestController extends Mage_Adminhtml_Controller_Action
{

    public function listAction(){

        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();

        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);

        $incrementId = $this->getRequest()->getParam('increment_id');
        $name = $this->getRequest()->getParam('name');
        $sku = $this->getRequest()->getParam('sku');
        $warehouseSku = $this->getRequest()->getParam('warehouse_sku');
        $provinceName = $this->getRequest()->getParam('province_name');
        $page = $this->getRequest()->getParam('page');
        $limit = $this->getRequest()->getParam('limit');

        $collection = Mage::getModel('ved_purchase/outputrequest')->getCollection()
            ->addFieldToSelect('id')
            ->addFieldToSelect('order_id')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('item_id')
            ->addFieldToSelect('product_id')
            ->addFieldToSelect('sku')
            ->addFieldToSelect('warehouse_sku')
            ->addFieldToSelect('name')
            ->addFieldToSelect('price')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('province_name')
            ->addFieldToSelect('province_code')
            ->addFieldToSelect('website_id')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('quantity')
            ->addFieldToFilter('status',1)
            ->addFieldToFilter('website_id',
                array(
                    array('in'=> array(array_merge(array('0'),$websiteIds))),
                )
            );

//        $collection->getSelect()->joinLeft(
//            array('website' => $collection->getTable('core/website')),
//            'website.website_id = main_table.website_id');

        if(trim($incrementId)){
            $collection->addFieldToFilter('increment_id', array('like' => '%'. $incrementId .'%'));
        }
        if(trim($name)){
            $collection->addFieldToFilter('name', array('like' => '%'. $name .'%'));
        }
        if(trim($sku)){
            $collection->addFieldToFilter('sku', array('like' =>  '%'. $sku .'%'));
        }
        if(trim($warehouseSku)){
            $collection->addFieldToFilter('warehouse_sku', array('like' =>  '%'. $warehouseSku .'%'));
        }
        if(trim($provinceName)){
            $collection->addFieldToFilter('province_name', array('like' =>  '%'. $provinceName .'%'));
        }
        $count = $collection->count();
        $collection->setPageSize($limit ? $limit : 10)->setCurPage($page);

        $result = array_merge(array('data' => $collection->getData()),array('total' => $count));

        echo json_encode($result);die();
    }

    public function addItemStockAction(){
        $request = json_decode(file_get_contents('php://input'));

        $stockRequests = $request->stock_requests;

        try{
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            if($stockRequests){
                $connection->beginTransaction();

                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();
                $adminUserName = $admin_user_session->getUser()->getLastname() . ' ' . $admin_user_session->getUser()->getFirstname();

                foreach($stockRequests as $stockRequest){

                    $itemStock = Mage::getModel("ved_gorders/orderitemstock");
                    $website = Mage::getModel("core/website")->load($stockRequest->website_id);

                    $itemStock->setOrderId($stockRequest->order_id)
                        ->setProductId($stockRequest->product_id)
                        ->setSku($stockRequest->sku)
                        ->setQuantity($stockRequest->quantity)
                        ->setStoreId($stockRequest->store_id)
                        ->setStoreName($stockRequest->store_name)
                        ->setOrderIncrementId($stockRequest->order_increment_id)
                        ->setOrderItemId($stockRequest->order_item_id)
                        ->setStatus(1)
                        ->setCreatedAt(now())
                        ->setCreatedBy($adminuserId)
                        ->save();

                    $requestItem = Mage::getModel('ved_purchase/outputrequest')->load($stockRequest->id);
                    $requestItem->setStatus(2)
                        ->setUpdatedBy($adminuserId)
                        ->setUpdatedAt(now());
                    $requestItem->save();

                    $connection->commit();

                    $result = array("result"=>"ok","msg"=>"");

                }

            }

        }catch(Exception $e){
            $connection->rollback();
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);die();
    }

    public function getHoldQuantityAction(){
        $productIds = $this->getRequest()->getParam('product_ids');
        try{
            $itemStockCollection = Mage::getModel("ved_gorders/orderitemstock")
                ->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('product_id',
                    array(
                        array('in'=> array(array_merge(array('0'),$productIds))),
                    )
                )
                ->addFieldToSelect('product_id')
                ->addExpressionFieldToSelect('total_request', 'sum({{quantity}})', 'quantity');

            $itemStockCollection->getSelect()->group(array('product_id', 'store_id'));

            $itemStock = $itemStockCollection->getData();

            //var_dump($requestItems);die();
            $result = array("result"=>"ok","msg"=>"",
                "data"=>array(
                    "request_item" => $itemStock
                ));

        }catch(Exception $e){
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);die();
    }

}