<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/8/2016
 * Time: 10:36 AM
 */
class Ved_Purchase_Adminhtml_StockrequestController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        //var_dump(1);die();
        $this->_title($this->__('Purchase'))->_title($this->__('List Request Item'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $this->_addContent($this->getLayout()->createBlock('ved_purchase/adminhtml_stockrequestitem'));
        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_purchase/adminhtml_purchase_grid')->toHtml()
        );
    }

    public function getRequestItemAction(){
        $requestStoreId = $this->getRequest()->getParam('request_store_id');
        $storeId = $this->getRequest()->getParam('store_id');
        $requestItems = array();
        try{
            if($requestStoreId){
                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();
                $helper = Mage::helper("purchase");
                $websiteIds = $helper->getWebsiteByUserId($adminuserId);
                $requestItemCollection = Mage::getModel("ved_purchase/stockrequestitem")->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('request_store_id', $requestStoreId)
                    ->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('main_table.website_id',
                        array(
                            array('in'=> array($websiteIds)),
                        )
                    )
                    ->addFieldToSelect('product_name')
                    ->addFieldToSelect('sku')
                    ->addFieldToSelect('product_id')
                    ->addFieldToSelect('price')
                    ->addExpressionFieldToSelect('total_qty', 'sum({{quantity}})', 'quantity');

                $requestItemCollection->getSelect()->group('product_id');
                $requestItems = $requestItemCollection->getData();

                $requestItemDetails = Mage::getModel("ved_purchase/stockrequestitem")->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('request_store_id', $requestStoreId)
                    ->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('main_table.website_id',
                        array(
                            array('in'=> array($websiteIds)),
                        )
                    )
                    ->addFieldToSelect('id')
                    ->addFieldToSelect('product_id')
                    ->addFieldToSelect('sku')
                    ->addFieldToSelect('price')
                    ->addFieldToSelect('quantity')
                    ->addFieldToSelect('order_id')
                    ->addFieldToSelect('order_increment_id')
                    ->getData();
                //var_dump($requestItemDetails);die();
                foreach($requestItems as $key => $item){
                    $requestItems[$key]['order_item'] = array();
                    $requestItems[$key]['price'] = floatval($item['price']);
                    $requestItems[$key]['total_qty'] = intval($item['total_qty']);
                    foreach($requestItemDetails as $detail){
                        if($item['sku'] == $detail['sku']){
                            $requestItems[$key]['order_item'][] = $detail;
                        }
                    }
                }
            }
            //var_dump($requestItems);die();
            $result = array("result"=>"ok","msg"=>"",
                "data"=>array(
                    "request_item" => $requestItems
                ));

        }catch(Exception $e){
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);die();
    }

    public function getTransferItemAction(){
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);
        try{
            $requestItemCollection = Mage::getModel("ved_purchase/stockrequestitem")->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('main_table.website_id',
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

            $requestTransfer = $requestItemCollection->getData();
            $result = array("result"=>"ok","msg"=>"",
                "data"=>array(
                    "request_transfer" => $requestTransfer
                ));
        }catch(Exception $e){
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);
        die();
    }

    public function createTransferAction(){
        $request = json_decode(file_get_contents('php://input'));

        $requestStoreId = $request->request_store_id;
        $websiteId = $request->website_id;
        $storeId = $request->store_id;
        $transferItems = $request->request_items;
        $itemIds = $request->ids;
        //$receiveDate = $request->receive_date;
        $description = $request->description;
        $isSendCustomer = $request->is_send_customer;

        try{
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            if($requestStoreId){
                $connection->beginTransaction();

                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();
                $adminUserName = $admin_user_session->getUser()->getLastname() . ' ' . $admin_user_session->getUser()->getFirstname();

                $requestTransferCollection = Mage::getModel("ved_purchase/stockrequestitem")->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('request_store_id', $requestStoreId)
                    ->addFieldToFilter('website_id', $websiteId)
                    ->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('id', array(
                        array('in'=> array($itemIds))
                    ))
                    ->addFieldToSelect('request_store_id')
                    ->addFieldToSelect('request_store_name')
                    ->addFieldToSelect('store_name');

                $requestTransferCollection->getSelect()->group('request_store_id');
                $requestItems = $requestTransferCollection->getData();

                if(count($requestItems) > 0){
                    $stockTransfer = Mage::getModel("ved_purchase/stocktransfer");
                    $website = Mage::getModel("core/website")->load($websiteId);
                    $websiteCode = $website->getCode() ? $website->getCode() : '';
                    $stockTransfer->setRequestStoreId($requestItems[0]['request_store_id'])
                        ->setRequestStoreName($requestItems[0]['request_store_name'])
                        ->setStoreName($requestItems[0]['store_name'])
                        ->setCode('ST/'. strtoupper($websiteCode) . '/' . date('ymdHis',time()))
                        ->setWebsiteId($websiteId)
                        ->setStoreId($storeId)
                        ->setStatus(1)
                        ->setIsSendCustomer($isSendCustomer)
                        ->setCreatedBy($adminuserId)
                        ->setCreatedAt(date('Y-m-d H:i:s',time()))
                        ->setDescription($description)
                        ->save();

                    $stockTransferId = $stockTransfer->getId();

                    //Get data group by SKU to check whether item is updated or not
                    $requestItemCollection = Mage::getModel("ved_purchase/stockrequestitem")->getCollection()
                        ->addFieldToFilter('status', 1)
                        ->addFieldToFilter('request_store_id', $requestStoreId)
                        ->addFieldToFilter('website_id', $websiteId)
                        ->addFieldToFilter('id', array(
                            array('in'=> array($itemIds))
                        ))
                        ->addFieldToSelect('sku');

                    $requestItemCollection->getSelect()->group('sku');

                    $requestItems = $requestItemCollection->getData();

                    $createItems = array();
                    foreach($requestItems as $item){
                        foreach($transferItems as $value){
                            if($item['sku'] == $value->sku){
                                $createItems[] = $value;
                            }
                        }
                    }

                    $stockTransferWarehouseItem = array();
                    //Only allow to insert valid record, the others will be removed
                    foreach($createItems as $item){
                        $stockTransferItem = Mage::getModel("ved_purchase/stocktransferitem");
                        $stockTransferItem->setStockTransferId($stockTransferId)
                            ->setProductSku($item->sku)
                            ->setProductName($item->product_name)
                            ->setRequestQty($item->quantity)
                            ->setStatus(1)
                            ->setUnit($item->unit)
                            ->setProductId($item->product_id)
                            ->setCreatedAt(date('Y-m-d H:i:s',time()))
                            ->save();

                        $stockTransferWarehouseItem[] = array(
                            "sku" => $item->sku,
                            "entity_id" => $item->product_id,
                            "quantity" => $item->quantity,
                            "unit" => $item->unit,
                            "note" => ''
                        );
                    }

                    $collection = Mage::getModel("ved_purchase/stockrequestitem")->getCollection()
                        ->addFieldToFilter('status', 1)
                        ->addFieldToFilter('request_store_id', $requestStoreId)
                        ->addFieldToFilter('website_id', $websiteId)
                        ->addFieldToFilter('id', array(
                            array('in'=> array($itemIds))
                        ));

                    foreach($collection as $requestItem)
                    {
                        $requestItem->setStatus(2)
                            ->setUpdatedBy($adminuserId)
                            ->setUpdatedAt(date('Y-m-d H:i:s',time()));
                        $requestItem->save();
                    }

                    $client = new Varien_Http_Client('http://warehouse.gcafe.vn/api/stock-transfer-requests');
                    $client->setMethod(Varien_Http_Client::POST);
                    $client->setParameterPost('code', $stockTransfer->getCode());
                    $client->setParameterPost('is_send_customer', $isSendCustomer);
                    $client->setParameterPost('to_store_id', $stockTransfer->getStoreId());
                    $client->setParameterPost('from_store_id', $stockTransfer->getRequestStoreId());
                    $client->setParameterPost('description', $description);
                    $client->setParameterPost('request_user', $adminUserName);
                    //$client->setParameterPost('receive_date', $receiveDate);
                    $client->setParameterPost('items', $stockTransferWarehouseItem);

                    $response = $client->request();
                    if ($response->isSuccessful()) {
                        $connection->commit();
                        $result = array("result"=>"ok","msg"=>"");
                    }else{
                        $connection->rollback();
                        $result = array("result"=>"error","msg"=>$response);
                    }
                }else{
                    $result = array("result"=>"error","msg"=>"No item");
                }
            }

        }catch(Exception $e){
            $connection->rollback();
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);die();
    }

    public function updateRequestItemAction(){
        $request = json_decode(file_get_contents('php://input'));
        $updateItems = $request->request_items;
        try{
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            if(count($updateItems) > 0){
                $connection->beginTransaction();

                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();

                foreach($updateItems as $item){
                    $requestItem = Mage::getModel("ved_purchase/stockrequestitem")->load($item->id);
                    $requestItem->setRequestStoreId($item->request_store_id)
                        ->setRequestStoreName($item->request_store_name)
                        ->setUpdatedBy($adminuserId)
                        ->setUpdatedAt(date('Y-m-d H:i:s',time()))
                        ->save();
                    //var_dump($requestItem->getData());
                }

                $connection->commit();
            }

            $result = array("result"=>"ok","msg"=>"");

        }catch(Exception $e){
            $connection->rollback();
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);die();
    }




}