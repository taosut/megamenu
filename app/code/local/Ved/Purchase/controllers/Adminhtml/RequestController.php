<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/8/2016
 * Time: 10:36 AM
 */
class Ved_Purchase_Adminhtml_RequestController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        //var_dump(1);die();
        $this->_title($this->__('Purchase'))->_title($this->__('List Request Item'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $this->_addContent($this->getLayout()->createBlock('ved_purchase/adminhtml_requestitem'));
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
        $supplierId = $this->getRequest()->getParam('supplier_id');
        $websiteId = $this->getRequest()->getParam('website_id');
        $storeId = $this->getRequest()->getParam('store_id');
        $user_admin_id = Mage::getSingleton('admin/session')->getUser()->getUserId();
        $requestItems = array();
        try{
            $requestItemCollection = Mage::getModel("ved_purchase/requestitem")->getCollection()
//                ->addFieldToFilter('pre_status', 1)
                ->addFieldToFilter('main_table.status', 1);

            if($supplierId){
                $requestItemCollection->addFieldToFilter('main_table.supplier_id', $supplierId);
            }else{
                $requestItemCollection->addFieldToFilter('main_table.supplier_id', array('null' => true));
            }

            $requestItemCollection
                ->addFieldToFilter('main_table.store_id', $storeId)
//                ->addFieldToFilter('website_id', $websiteId)
                ->addFieldToSelect('store_name')
                ->addFieldToSelect('product_name')
                ->addFieldToSelect('sku')
                ->addFieldToSelect('product_id')
                ->addFieldToSelect('standard_product_id')
                ->addFieldToSelect('price')
                ->addFieldToSelect('assignee')
                ->addExpressionFieldToSelect('original_price', 'sum(quantity * original_price) / sum(quantity)', 'original_price')
                ->addExpressionFieldToSelect('total_qty', 'sum({{quantity}})', 'quantity');

//            $requestItemCollection->getSelect()->join(
//                array('order' => 'sales_flat_order'),
//                'order.entity_id = main_table.order_id',
//                array()
//            );
//            $requestItemCollection->addFieldToFilter('order.status', array('like' => 'telephone_confirm'));

            $requestItemCollection->getSelect()->group(['product_id', 'assignee']);
            $requestItemCollection->setOrder('FIELD(assignee,'.$user_admin_id.')', 'DESC');
            $requestItemCollection->setOrder('assignee', 'ASC');
            $requestItemCollection->setOrder('product_name', 'ASC');

            $requestItems = $requestItemCollection->getData();


            $requestItemDetails = Mage::getModel("ved_purchase/requestitem")->getCollection()
//                ->addFieldToFilter('main_table.pre_status', 1)
                ->addFieldToFilter('main_table.status', 1);
//            $requestItemDetails->getSelect()->join(
//                array('order' => 'sales_flat_order'),
//                'order.entity_id = main_table.order_id',
//                array()
//            );
//            $requestItemDetails->addFieldToFilter('order.status', array('like' => 'telephone_confirm'));

            if($supplierId){
                $requestItemDetails->addFieldToFilter('main_table.supplier_id', $supplierId);
            }

            $itemDetails = $requestItemDetails
                ->addFieldToFilter('main_table.store_id', $storeId)
                ->addFieldToSelect('id')
                ->addFieldToSelect('product_id')
                ->addFieldToSelect('sku')
                ->addFieldToSelect('price')
                ->addFieldToSelect('quantity')
                ->addFieldToSelect('order_qty')
                ->addFieldToSelect('order_id')
                ->addFieldToSelect('original_price')
                ->addFieldToSelect('order_increment_id')
                ->addFieldToSelect('assignee')
                ->getData();

            $tmp = array();
            foreach ($requestItemCollection as $item) {
                array_push($tmp, $item['standard_product_id']);
            }

            $requestItemsPending = Mage::getModel('ved_purchase/purchaseitem')->getCollection()
                ->addFieldToSelect('product_id')
                ->addFieldToSelect('product_sku')
                ->addFieldToSelect('request_qty')
                ->addFieldToSelect('import_qty')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('type')
                ->addFieldToSelect('unit')
                ->addFieldToSelect('price')
                ->addFieldToSelect('vat')
                ->addFieldToFilter('product_id', array(
                    'in' => $tmp
                ));

            $requestItemsPending->getSelect()->join(
                array('purchase' => 'sales_flat_purchase'),
                'main_table.purchase_id = purchase.id',
                array('code', 'purchase_id' => 'purchase.id', 'supplier_name', 'store_name', 'status')
            )
            ->where('purchase.status = 1')
            ->where('purchase.store_id = '.$storeId);

            $requestItemsPending->setOrder('created_at', 'DESC');

            foreach($requestItems as $key => $item){
                $requestItems[$key]['order_item'] = array();
                $requestItems[$key]['price'] = floatval($item['price']);
                $requestItems[$key]['total_qty'] = intval($item['total_qty']);
                foreach ($requestItemsPending as $itemPending) {
                    if ($item['standard_product_id'] == $itemPending['product_id']) {
                        $requestItems[$key]['item_pending'][] = $itemPending->getData();
                    }
                }
                foreach($itemDetails as $detail){
                    if($item['product_id'] == $detail['product_id'] && $item['assignee'] == $detail['assignee']){
                        $requestItems[$key]['order_item'][] = $detail;
                        if ($detail['assignee'] != null) {
                            $requestItems[$key]['assignee_id'] = $detail['assignee'];
                            $requestItems[$key]['assignee'] = Mage::getModel('admin/user')
                                ->load(intval($detail['assignee']))->getUsername();
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

    public function getSupplierItemAction(){
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);
        try{
            $requestItemCollection = Mage::getModel("ved_purchase/requestitem")->getCollection()
                ->addFieldToFilter('main_table.status', 1)
//                ->addFieldToFilter('pre_status', 1)
                ->addFieldToFilter('website_id',
                    array(
                        array('in'=> array(array_merge(array('0'),$websiteIds))),
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

            $requestSupplier = $requestItemCollection->getData();
            $result = array("result"=>"ok","msg"=>"",
                "data"=>array(
                    "request_supplier" => $requestSupplier
                ));
        }catch(Exception $e){
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);
        die();
    }

    /**
     * @deprecated
    */
    public function createPurchaseAction(){
        $request = json_decode(file_get_contents('php://input'));

        $supplierId = $request->supplier_id;
        $websiteId = $request->website_id;
        $storeId = $request->store_id;
        $storeName = $request->store_name;
        $purchaseItems = $request->request_items;
        $itemIds = $request->ids;
        $receiveDate = $request->receive_date;
        $description = $request->description;

        if(!$storeId || !$websiteId || !$supplierId || !$itemIds){
            $result = array("result"=>"error","msg"=>" Error: Missing data");
        }else{
            try{
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

                if($supplierId){
                    $connection->beginTransaction();

                    $admin_user_session = Mage::getSingleton('admin/session');
                    $adminuserId = $admin_user_session->getUser()->getUserId();
                    $adminUserName = $admin_user_session->getUser()->getLastname() . ' ' . $admin_user_session->getUser()->getFirstname();

                    $requestSupplierCollection = Mage::getModel("ved_purchase/requestitem")->getCollection()
                        ->addFieldToFilter('status', 1)
                        ->addFieldToFilter('supplier_id', $supplierId)
                        ->addFieldToFilter('store_id', $storeId)
                        ->addFieldToFilter('id', array(
                            array('in'=> array($itemIds))
                        ))
                        ->addFieldToSelect('supplier_id')
                        ->addFieldToSelect('supplier_name')
                        ->addFieldToSelect('supplier_info');

                    $requestSupplierCollection->getSelect()->group('supplier_id');
                    $requestItems = $requestSupplierCollection->getData();

                    if(count($requestItems) > 0){
                        $purchase = Mage::getModel("ved_purchase/purchase");
                        $website = Mage::getModel("core/website")->load($websiteId);
                        $websiteCode = $website->getCode() ? $website->getCode() : '';
                        $purchase->setSupplierId($requestItems[0]['supplier_id'])
                            ->setCode('PU/'. strtoupper($websiteCode) . '/' . date('ymdH',time()))
                            ->setSupplierName($requestItems[0]['supplier_name'])
                            ->setSupplierInfo($requestItems[0]['supplier_info'])
                            ->setWebsiteId($websiteId)
                            ->setStoreId($storeId)
                            ->setStoreName($storeName)
                            ->setStatus(1)
                            ->setReceiveDate($receiveDate)
                            ->setCreatedAt(date('Y-m-d H:i:s',time()))
                            ->setCreatedBy($adminuserId)
                            ->setDescription($description)
                            ->save();

                        $purchaseId = $purchase->getId();

                        $code =  str_pad($purchaseId, 4, "0", STR_PAD_LEFT);

                        $purchase->setCode('PU/'. $storeId . '/' . date('ymdH',time()) . $code)
                            ->save();

                        //Get data group by SKU to check whether item is updated or not
                        $requestItemCollection = Mage::getModel("ved_purchase/requestitem")->getCollection()
                            ->addFieldToFilter('status', 1)
                            ->addFieldToFilter('supplier_id', $supplierId)
                            ->addFieldToFilter('website_id', $websiteId)
                            ->addFieldToFilter('store_id', $storeId)
                            ->addFieldToFilter('id', array(
                                array('in'=> array($itemIds))
                            ))
                            ->addFieldToSelect('sku');

                        $requestItemCollection->getSelect()->group('sku');

                        $requestItems = $requestItemCollection->getData();

                        $createItems = array();
                        foreach($requestItems as $item){
                            foreach($purchaseItems as $value){
                                if($item['sku'] == $value->sku){
                                    $createItems[] = $value;
                                }
                            }
                        }

                        $purchaseWarehouseItem = array();
                        //Only allow to insert valid record, the others will be removed
                        foreach($createItems as $item){
                            $purchaseItem = Mage::getModel("ved_purchase/purchaseitem");
                            $purchaseItem->setPurchaseId($purchaseId)
                                ->setProductSku($item->sku)
                                ->setProductName($item->product_name)
                                ->setRequestQty($item->quantity)
                                ->setOrderQty($item->order_qty)
                                ->setPrice($item->price)
                                ->setVat($item->vat)
                                ->setStatus(1)
                                ->setType($item->type)
                                ->setUnit($item->unit)
                                ->setProductId($item->product_id)
                                ->setCreatedAt(date('Y-m-d H:i:s',time()))
                                ->save();

                            $purchaseWarehouseItem[] = array(
                                "sku" => $item->sku,
                                "entity_id" => $item->product_id,
                                "quantity" => $item->quantity,
                                "price" => $item->price,
                                "unit" => $item->unit,
                                "type" => $item->type,
                                "vat" => $item->vat,
                                "reserve_quantity" => $item->order_qty,
                                "note" => ''
                            );
                        }

                        $collection = Mage::getModel("ved_purchase/requestitem")->getCollection()
                            ->addFieldToFilter('status', 1)
                            ->addFieldToFilter('supplier_id', $supplierId)
                            ->addFieldToFilter('store_id', $storeId)
                            ->addFieldToFilter('website_id', $websiteId)
                            ->addFieldToFilter('id', array(
                                array('in'=> array($itemIds))
                            ));

                        foreach($collection as $requestItem)
                        {
                            $requestItem->setPurchaseId($purchaseId)
                                ->setStatus(2)
                                ->setUpdatedBy($adminuserId)
                                ->setUpdatedAt(date('Y-m-d H:i:s',time()));

                            $requestItem->save();
                        }

                        $apiUrlWarehouse = (string)Mage::getConfig()->getNode('global/warehouse_api_url');
                        $client = new Varien_Http_Client($apiUrlWarehouse . 'purchase-requests');
                        $client->setMethod(Varien_Http_Client::POST);
                        $client->setParameterPost('code', $purchase->getCode());
                        $client->setParameterPost('store_id', $purchase->getStoreId());
                        $client->setParameterPost('supplier_id', $purchase->getSupplierId());
                        $client->setParameterPost('description', $description);
                        $client->setParameterPost('receive_date', $receiveDate);
                        $client->setParameterPost('created_by', $adminUserName);
                        $client->setParameterPost('items', $purchaseWarehouseItem);

                        $response = $client->request();
                        if ($response->isSuccessful()) {
                            $connection->commit();
                            $result = array("result"=>"ok","msg"=>"");
                        }else{
                            $connection->rollback();
                            $result = array("result"=>"error","msg"=>$response);
                        }
                        /**
                         * @var Ved_Purchase_Model_Purchase $purchase
                         */
                        $purchase->callMessageQueue();
                    }
                }

            }catch(Exception $e){
                $connection->rollback();
                $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
            }
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
                    $requestItem = Mage::getModel("ved_purchase/requestitem")->load($item->id);
                    if($requestItem->getId()){
                        if(isset($item->supplier_id))
                            $requestItem->setSupplierId($item->supplier_id);
                        if(isset($item->supplier_name))
                            $requestItem->setSupplierName($item->supplier_name);
                        if(isset($item->supplier_info))
                            $requestItem->setSupplierInfo($item->supplier_info);
                        if(isset($item->store_id))
                            $requestItem->setStoreId($item->store_id);
                        if(isset($item->store_name))
                            $requestItem->setStoreName($item->store_name);
                        if(isset($item->website_id))
                            $requestItem->setWebsiteId($item->website_id);

                        $requestItem
                            ->setUpdatedBy($adminuserId)
                            ->setUpdatedAt(date('Y-m-d H:i:s',time()))
                            ->save();
                    }
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

    public function removeRequestItemAction(){
        $request = json_decode(file_get_contents('php://input'));
        $item = $request->request_item;
        try{
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $result = array("result"=>"ok","msg"=>"");
            if($item->id){
                $connection->beginTransaction();

                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();
                $requestItem = Mage::getModel("ved_purchase/requestitem")->load($item->id);
                if($requestItem->getId()){
                    $requestItem
                        ->setStatus(0)
                        ->setUpdatedBy($adminuserId)
                        ->setUpdatedAt(date('Y-m-d H:i:s',time()))
                        ->save();
                }else{
                    $result  = array("result"=>"error","msg"=>"Cannot find Item ". $item->id);
                }

                $connection->commit();
            }else{
                $result  = array("result"=>"error","msg"=>"No item");
            }

        }catch(Exception $e){
            $connection->rollback();
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);die();
    }



    public function createPurchaseFromOrderAction(){
        $request = json_decode(file_get_contents('php://input'));

        $supplierId = $request->supplier_id;
        $websiteId = $request->website_id;
        $storeId = $request->store_id;
        $storeName = $request->store_name;
        $purchaseItems = $request->request_items;
        $itemIds = $request->ids;
        $receiveDate = $request->receive_date;
        $description = $request->description;

        if(!$storeId || !$websiteId || !$supplierId || !$itemIds){
            $result = array("result"=>"error","msg"=>" Error: Missing data");
        }else{
            try{
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

                if($supplierId){
                    $connection->beginTransaction();

                    $admin_user_session = Mage::getSingleton('admin/session');
                    $adminuserId = $admin_user_session->getUser()->getUserId();
                    $adminUserName = $admin_user_session->getUser()->getLastname() . ' ' . $admin_user_session->getUser()->getFirstname();

                    $requestSupplierCollection = Mage::getModel("ved_purchase/requestitem")->getCollection()
                        ->addFieldToFilter('status', 1)
                        ->addFieldToFilter('supplier_id', $supplierId)
                        ->addFieldToFilter('store_id', $storeId)
                        ->addFieldToFilter('id', array(
                            array('in'=> array($itemIds))
                        ))
                        ->addFieldToSelect('supplier_id')
                        ->addFieldToSelect('supplier_name')
                        ->addFieldToSelect('supplier_info');

                    $requestSupplierCollection->getSelect()->group('supplier_id');
                    $requestItems = $requestSupplierCollection->getData();

                    if(count($requestItems) > 0){
                        $purchase = Mage::getModel("ved_purchase/purchase");
                        $website = Mage::getModel("core/website")->load($websiteId);
                        $websiteCode = $website->getCode() ? $website->getCode() : '';
                        $purchase->setSupplierId($requestItems[0]['supplier_id'])
                            ->setCode('PU/'. strtoupper($websiteCode) . '/' . date('ymdH',time()))
                            ->setSupplierName($requestItems[0]['supplier_name'])
                            ->setSupplierInfo($requestItems[0]['supplier_info'])
                            ->setWebsiteId($websiteId)
                            ->setStoreId($storeId)
                            ->setStoreName($storeName)
                            ->setStatus(1)
                            ->setReceiveDate(
                                DateTime::createFromFormat(
                                    'Y-m-d H:i:s', $receiveDate,
                                    new DateTimeZone('Asia/Ho_Chi_Minh'))
                            )
                            ->setCreatedAt(date('Y-m-d H:i:s',time()))
                            ->setCreatedBy($adminuserId)
                            ->setDescription($description)
                            ->save();

                        $purchaseId = $purchase->getId();

                        $code =  str_pad($purchaseId, 4, "0", STR_PAD_LEFT);

                        $purchase->setCode('PU/'. $storeId . '/' . date('ymdH',time()) . $code)
                            ->save();

                        //Get data group by product_id to check whether item is updated or not
                        $requestItemCollection = Mage::getModel("ved_purchase/requestitem")->getCollection()
                            ->addFieldToFilter('status', 1)
                            ->addFieldToFilter('supplier_id', $supplierId)
//                            ->addFieldToFilter('website_id', $websiteId)
                            ->addFieldToFilter('store_id', $storeId)
                            ->addFieldToFilter('id', array(
                                array('in'=> array($itemIds))
                            ))
                            ->addFieldToSelect('sku')
                            ->addFieldToSelect('standard_product_id');

                        $requestItemCollection->getSelect()->group('standard_product_id');

                        $requestItems = $requestItemCollection->getData();

                        $createItems = array();
                        foreach($requestItems as $item){
                            foreach($purchaseItems as $value){
                                if($item['standard_product_id'] == $value->product_id){
                                    $createItems[] = $value;
                                }
                            }
                        }

                        //Only allow to insert valid record, the others will be removed
                        foreach($createItems as $item){
                            $purchaseItem = Mage::getModel("ved_purchase/purchaseitem");
                            $purchaseItem->setPurchaseId($purchaseId)
                                ->setProductSku($item->sku)
                                ->setProductName($item->product_name)
                                ->setRequestQty($item->quantity)
                                ->setOrderQty($item->order_qty)
                                ->setPrice($item->price)
                                ->setVat(10)
                                ->setStatus(1)
                                ->setType($item->type)
                                ->setUnit($item->unit)
                                ->setProductId($item->product_id)
                                ->setCreatedAt(date('Y-m-d H:i:s',time()))
                                ->save();
                        }

                        $collection = Mage::getModel("ved_purchase/requestitem")->getCollection()
                            ->addFieldToFilter('status', 1)
                            ->addFieldToFilter('supplier_id', $supplierId)
                            ->addFieldToFilter('store_id', $storeId)
//                            ->addFieldToFilter('website_id', $websiteId)
                            ->addFieldToFilter('id', array(
                                array('in'=> array($itemIds))
                            ));

                        foreach($collection as $requestItem)
                        {
                            $requestItem->setPurchaseId($purchaseId)
                                ->setStatus(2)
                                ->setUpdatedBy($adminuserId)
                                ->setCreatedAt(date('Y-m-d H:i:s',time()));

                            $requestItem->save();
                        }


                        $purchase->callMessageQueue();

                        $connection->commit();

                        $result = array("result"=>"ok","msg"=>"");
                    }else{
                        $result = array("result"=>"error","msg"=>"no data");
                    }

                }

            }catch(Exception $e){
                $connection->rollback();
                $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
            }
        }
        echo json_encode($result);die();
    }

    public function holdAction()
    {
        $requestItemCount = 0;
        try {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $supplierId = $this->getRequest()->getParam('supplier_id');
            $websiteId = $this->getRequest()->getParam('website_id');
            $storeId = $this->getRequest()->getParam('store_id');
            $product_ids = $this->getRequest()->getParam('product_ids');
            $user_admin = Mage::getSingleton('admin/session')->getUser();

            $connection->beginTransaction();

            foreach ($product_ids as $product_id) {
                $requestItemDetails = Mage::getModel("ved_purchase/requestitem")->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('product_id',  $product_id)
//                    ->addFieldToFilter('website_id', $websiteId)
                    ->addFieldToFilter('store_id', $storeId);

                if($supplierId){
                    $requestItemDetails->addFieldToFilter('supplier_id', $supplierId);
                }else{
                    $requestItemDetails->addFieldToFilter('supplier_id', array('null' => true));
                }

                $flag = 0;
                foreach ($requestItemDetails as $requestItemDetail) {
                    if ($requestItemDetail->getAssignee() != null &&
                        $requestItemDetail->getAssignee() != $user_admin->getUserId()) {
                        $flag = 1;
                        break;
                    }
                }
                if ($flag) {
                    $requestItemCount++;
                    continue;
                }

                $requestItemDetails->setDataToAll('assignee', $user_admin->getUserId())->save();
            }
            if (count($product_ids) - $requestItemCount == 0) {
                $result = array('status' => 'held');
            }
            else {
                $connection->commit();
                $result = array('status' => 'success', 'not_hold' => $requestItemCount);
            }
            echo json_encode($result);
        }
        catch (Exception $e) {
            $connection->rollback();
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
            echo json_encode($result);
        }
    }

    public function unholdAction()
    {
        try {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $supplierId = $this->getRequest()->getParam('supplier_id');
            $websiteId = $this->getRequest()->getParam('website_id');
            $storeId = $this->getRequest()->getParam('store_id');
            $product_ids = $this->getRequest()->getParam('product_ids');
            $user_admin_id = Mage::getSingleton('admin/session')->getUser()->getUserId();

            $connection->beginTransaction();

            $requestItemDetails = Mage::getModel("ved_purchase/requestitem")->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('product_id', array('in' => $product_ids))
//                ->addFieldToFilter('website_id', $websiteId)
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('assignee', $user_admin_id);

            if($supplierId){
                $requestItemDetails->addFieldToFilter('supplier_id', $supplierId);
            }else{
                $requestItemDetails->addFieldToFilter('supplier_id', array('null' => true));
            }

            $requestItemDetails->setDataToAll('assignee', null)->save();
            $connection->commit();
            $result = array('status' => 'success');
            echo json_encode($result);
        }
        catch(Exception $e) {
            $connection->rollback();
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
            echo json_encode($result);
        }
    }
}