<?php

/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/5/2016
 * Time: 5:33 PM
 */
class Ved_Purchase_Adminhtml_PurchaseController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        //var_dump(1);die();
        $this->_title($this->__('Purchase'))->_title($this->__('Manage Purchase'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $this->_addContent($this->getLayout()->createBlock('ved_purchase/adminhtml_purchase'));
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

    public function editAction()
    {
        $purchaseId = (int)$this->getRequest()->getParam('id');
        $this->_title($this->__('Purchase'))->_title($this->__('Manage Purchase'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $block = $this->getLayout()->createBlock('ved_purchase/adminhtml_editpurchase');
        if ($block) {
            $block->setPurchaseId($purchaseId);
        }
        $this->_addContent($block);
        $this->renderLayout();
    }

    public function newAction()
    {
        $purchaseId = (int)$this->getRequest()->getParam('id');
        $this->_title($this->__('Purchase'))->_title($this->__('Create Purchase'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $block = $this->getLayout()->createBlock('ved_purchase/adminhtml_newPurchase');

        $this->_addContent($block);
        $this->renderLayout();
    }

    public function supplierAction()
    {
        $purchaseId  = (int) $this->getRequest()->getParam('id');
        $this->_title($this->__('Purchase'))->_title($this->__('Create Purchase'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $block = $this->getLayout()->createBlock('ved_purchase/adminhtml_newSupplierPurchase');

        $this->_addContent($block);
        $this->renderLayout();
    }

    public function orderAction()
    {
        $this->_title($this->__('Purchase'))->_title($this->__('Purchase Order'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $block = $this->getLayout()->createBlock('ved_purchase/adminhtml_purchaseorder');

        $this->_addContent($block);
        $this->renderLayout();
    }

    public function listRequestAction(){

        $this->_title($this->__('Purchase'))->_title($this->__('List Purchase Request Item'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $this->_addContent($this->getLayout()->createBlock('ved_purchase/adminhtml_requestitem'));
        $this->renderLayout();
    }

    public function checkAction()
    {
        $this->_title($this->__('Purchase'))->_title($this->__('Check Request Purchase'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $this->_addContent($this->getLayout()->createBlock('ved_purchase/adminhtml_checkrequest'));
        $this->renderLayout();
    }

    public function getPurchaseAction()
    {
        $purchaseId = $this->getRequest()->getParam('id');
        $purchaseData = array();
        $purchaseItems = array();
        try {
            if ($purchaseId) {
                $purchase = Mage::getModel("ved_purchase/purchase")->load($purchaseId);
                $purchaseData = $purchase->getData();

                $purchaseItems = Mage::getModel("ved_purchase/purchaseitem")->getCollection()
                    ->addFieldToFilter('purchase_id', $purchaseId)
                    ->getData();
            }

            $result = array("result" => "ok", "msg" => "",
                "data" => array(
                    "purchase" => $purchaseData,
                    "purchase_item" => $purchaseItems
                ));

        } catch (Exception $e) {
            $result = array("result" => "error", "msg" => $e->getMessage() . " Error code " . $e->getCode());
        }
        echo json_encode($result);
        die();
    }

    public function updatePurchaseItemAction()
    {
        $request = json_decode(file_get_contents('php://input'));

        $purchaseId = $request->purchase_id;
        $updateItems = $request->request_items;

        try {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            $admin_user_session = Mage::getSingleton('admin/session');
            $adminuserId = $admin_user_session->getUser()->getUserId();

            if ($purchaseId) {
                $connection->beginTransaction();

                $purchase = Mage::getModel("ved_purchase/purchase")->load($purchaseId);

                foreach ($updateItems as $item) {
                    $purchaseItem = Mage::getModel("ved_purchase/purchaseitem")->load($item->id);
                    $purchaseItemData = $purchaseItem->getData();
                    if ($purchaseItemData['purchase_id'] == $purchaseId) {
                        $currentQty = $purchaseItem->getRequestQty();
                        $purchaseItem->setRequestQty($item->quantity)
                            ->setPrice($item->price)
                            ->setVat($item->vat)
                            ->setType($item->type)
                            ->setUnit($item->unit)
                            ->setUpdatedAt(date('Y-m-d H:i:s', time()))
                            ->save();

                        if ($currentQty > $item->quantity) {
                            $purchaseRequestItem = Mage::getModel("ved_purchase/requestitem");
                            $purchaseRequestItem
                                ->setProductId($purchaseItem->getProductId())
                                ->setSku($purchaseItem->getProductSku())
                                ->setProductName($purchaseItem->getProductName())
                                ->setQuantity($currentQty - $item->quantity)
                                ->setPrice($item->price)
                                ->setStatus(1)
                                ->setSupplierId($purchase->getSupplierId())
                                ->setSupplierName($purchase->getSupplierName())
                                ->setSupplierInfo($purchase->getSupplierInfo())
                                ->setCreatedBy($adminuserId)
                                ->setStoreId($purchase->getStoreId())
                                ->setStoreName($purchase->getStoreName())
                                ->setWebsiteId($purchase->getWebsiteId())
                                ->setType(1)
                                ->setCreatedAt(date('Y-m-d H:i:s', time()))
                                ->save();
                        }

                        $purchaseWarehouseItem[] = array(
                            "sku" => $item->sku,
                            "entity_id" => $item->product_id,
                            "quantity" => $item->quantity,
                            "price" => $item->price,
                            "unit" => $item->unit,
                            "type" => $item->type,
                            "vat" => $item->vat,
                            "note" => ''
                        );
                    }


                }
                if (count($purchaseWarehouseItem) > 0) {
                    $apiUrlWarehouse = (string)Mage::getConfig()->getNode('global/warehouse_api_url');
                    $client = new Varien_Http_Client($apiUrlWarehouse . 'purchase-requests');
                    $client->setMethod(Varien_Http_Client::POST);
                    $client->setParameterPost('code', $purchase->getCode());
                    $client->setParameterPost('store_id', $purchase->getStoreId());
                    $client->setParameterPost('supplier_id', $purchase->getSupplierId());
                    $client->setParameterPost('description', $purchase->getDescription());
                    $client->setParameterPost('receive_date', $purchase->getReceiveDate());
                    $client->setParameterPost('items', $purchaseWarehouseItem);

                    $response = $client->request();
                    if ($response->isSuccessful()) {
                        $connection->commit();
                        $result = array("result" => "ok", "msg" => "");
                    } else {
                        $connection->rollback();
                        $result = array("result" => "error", "msg" => "Cannot send data to Warehouse");
                    }
                } else {
                    $result = array("result" => "error", "msg" => "No data update");
                }
            }
        } catch (Exception $e) {
            $connection->rollback();
            $result = array("result" => "error", "msg" => $e->getMessage() . " Error code " . $e->getCode());
        }
        echo json_encode($result);
        die();
    }

    public function getProductsAction()
    {

        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();

        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);

        $name = $this->getRequest()->getParam('name');
        $sku = $this->getRequest()->getParam('sku');
        $page = $this->getRequest()->getParam('page');

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


        if (trim($name)) {
            $collection->addAttributeToFilter('name', array('like' => '%' . $name . '%'));
        }
        if (trim($sku)) {
            $collection->addAttributeToFilter('sku', array('like' => '%' . $sku . '%'));
        }
        $collection->setPageSize(10)->setCurPage($page);
        $result = $collection->getData();

        echo json_encode($result);
        die();
    }

    /**
     * @deprecated
    */
    public function addPurchaseRequestAction()
    {
        $request = json_decode(file_get_contents('php://input'));

        $websiteId = $request->website_id;
        $storeId = $request->store_id;
        $storeName = $request->store_name;
        $purchaseRequests = $request->purchase_requests;
        $receiveDate = $request->receive_date;
        $description = $request->description;

        try {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            if ($purchaseRequests) {
                $connection->beginTransaction();

                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();
                $adminUserName = $admin_user_session->getUser()->getLastname() . ' ' . $admin_user_session->getUser()->getFirstname();

                foreach ($purchaseRequests as $purchaseRequest) {
                    $purchase = Mage::getModel("ved_purchase/purchase");
                    $website = Mage::getModel("core/website")->load($websiteId);
                    $websiteCode = $website->getCode() ? $website->getCode() : '';
                    $purchase->setSupplierId($purchaseRequest->supplier_id)
                        ->setCode('PU/' . strtoupper($websiteCode) . '/' . date('ymdH', time()))
                        ->setSupplierName($purchaseRequest->supplier_name)
                        ->setSupplierInfo(json_encode($purchaseRequest->supplier_info, JSON_UNESCAPED_UNICODE))
                        ->setWebsiteId($websiteId)
                        ->setStoreId($storeId)
                        ->setStoreName($storeName)
                        ->setStatus(1)
                        ->setReceiveDate($receiveDate)
                        ->setCreatedAt(date('Y-m-d H:i:s', time()))
                        ->setCreatedBy($adminuserId)
                        ->setDescription($description)
                        ->save();

                    $purchaseId = $purchase->getId();

                    $code = str_pad($purchaseId, 4, "0", STR_PAD_LEFT);

                    $purchase->setCode('PU/' . $storeId . '/' . date('ymdH', time()) . $code)
                        ->save();

                    $purchaseWarehouseItem = array();
                    //Only allow to insert valid record, the others will be removed
                    foreach ($purchaseRequest->items as $item) {
                        $purchaseItem = Mage::getModel("ved_purchase/purchaseitem");
                        $purchaseItem->setPurchaseId($purchaseId)
                            ->setProductSku($item->sku)
                            ->setProductName($item->product_name)
                            ->setRequestQty($item->quantity)
                            ->setOrderQty(0)
                            ->setPrice($item->price)
                            ->setVat($item->vat)
                            ->setStatus(1)
                            ->setType($item->type)
                            ->setUnit($item->unit)
                            ->setProductId($item->entity_id)
                            ->setCreatedAt(date('Y-m-d H:i:s', time()))
                            ->save();

                        $purchaseWarehouseItem[] = array(
                            "sku" => $item->sku,
                            "entity_id" => $item->entity_id,
                            "quantity" => $item->quantity,
                            "price" => $item->price,
                            "unit" => $item->unit,
                            "type" => $item->type,
                            "vat" => $item->vat,
                            "reserve_quantity" => 0,
                            "note" => ''
                        );
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
                        $result = array("result" => "ok", "msg" => "");
                    } else {
                        $connection->rollback();
                        $result = array("result" => "error", "msg" => $response);
                    }

                    $purchase->callMessageQueue();

                }

            }

        } catch (Exception $e) {
            $connection->rollback();
            $result = array("result" => "error", "msg" => $e->getMessage() . " Error code " . $e->getCode());
        }
        echo json_encode($result);
        die();
    }


    public function cancelPurchaseAction()
    {
        $request = json_decode(file_get_contents('php://input'));

        $purchaseId = $request->purchase_id;
        $itemIds = $request->product_ids;

        try {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            $admin_user_session = Mage::getSingleton('admin/session');
            $adminuserId = $admin_user_session->getUser()->getUserId();

            if ($purchaseId) {
                $connection->beginTransaction();

                $purchase = Mage::getModel("ved_purchase/purchase")->load($purchaseId);

                $purchase->setUpdatedAt(date('Y-m-d H:i:s', time()))
                    ->setUpdatedBy($adminuserId)
                    ->save();

                $items = array();
                if (!$itemIds) {
                    $purchase->setStatus(0)->save();
                    $purchaseItems = Mage::getModel("ved_purchase/purchaseitem")->getCollection()
                        ->addFieldToFilter('purchase_id', $purchaseId);
                    foreach($purchaseItems as $purchaseItem) {
                        $purchaseItem->setStatus(0)->save();
                        $type = ($purchaseItem->getType() == 0) ? 'HANG_HOA' : (($purchaseItem->getType() == 1) ? 'KY_GUI' : 'KHUYEN_MAI');
                        array_push($items, array(
                            'productId' => intval($purchaseItem->getProductId()),
                            'type' => $type
                        ));
                    }
                }
                else {
                    $purchaseItems = Mage::getModel("ved_purchase/purchaseitem")->getCollection()
                        ->addFieldToFilter('id', array('in' => $itemIds));
                    foreach($purchaseItems as $purchaseItem) {
                        $purchaseItem->setStatus(0)->save();
                        $type = ($purchaseItem->getType() == 0) ? 'HANG_HOA' : (($purchaseItem->getType() == 1) ? 'KY_GUI' : 'KHUYEN_MAI');
                        array_push($items, array(
                            'productId' => intval($purchaseItem->getProductId()),
                            'type' => $type
                        ));
                    }
                    $purchaseItems = Mage::getModel("ved_purchase/purchaseitem")->getCollection()
                        ->addFieldToFilter('purchase_id', $purchaseId);
                    $cancelItems = 0;
                    $doneItems = 0;
                    foreach($purchaseItems as $purchaseItem) {
                        if ($purchaseItem->getStatus() == 0) {
                            $cancelItems++;
                        }
                        else if ($purchaseItem->getRequestQty() == $purchaseItem->getImportQty()) {
                            $purchaseItem->setStatus(2)->save();
                            $doneItems++;
                        }
                    }
                    if ($cancelItems == $purchaseItems->count()) {
                        $purchase->setStatus(0)->save();
                    }
                    else if ($doneItems > 0 && $cancelItems + $doneItems == $purchaseItems->count()){
                        $purchase->setStatus(2)->save();
                    }
                }

                $message = array("id" => intval($purchaseId), "items" => $items, "createdAt" => time());
                Mage::callMessageQueue($message, 'sale.purchase.cancel');

                $connection->commit();
                $itemIds = array();
                $purchaseItems = Mage::getModel("ved_purchase/purchaseitem")->getCollection()
                    ->addFieldToFilter('purchase_id', $purchaseId);
                foreach ($purchaseItems as $purchaseItem) {
                    array_push($itemIds, array(
                        'productId' => $purchaseItem->getProductId(),
                        'status' => $purchaseItem->getStatus()
                    ));
                }

                $result = array("result" => "ok",
                    'item_ids' => $itemIds,
                    "purchase_status" => $purchase->getStatus(),
                    "msg" => "",
                    "updated_by" => Mage::getModel("admin/user")->load($adminuserId)->getData()
                );
            }
        } catch (Exception $e) {
            $connection->rollback();
            $result = array("result" => "error", "msg" => $e->getMessage() . " Error code " . $e->getCode());
        }
        echo json_encode($result);
        die();
    }

    public function addPurchaseFromOrderAction(){
        $request = json_decode(file_get_contents('php://input'));

        $purchaseRequests = $request->purchase_requests;

        try{
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            if($purchaseRequests){
                $connection->beginTransaction();

                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();
                $adminUserName = $admin_user_session->getUser()->getLastname() . ' ' . $admin_user_session->getUser()->getFirstname();

                foreach($purchaseRequests as $purchaseRequest){
                    $purchase = Mage::getModel("ved_purchase/purchase");
                    $website = Mage::getModel("core/website")->load($purchaseRequest->website_id);
                    $websiteCode = $website->getCode() ? $website->getCode() : '';
                    $purchase->setSupplierId($purchaseRequest->supplier_id)
                        ->setSupplierName($purchaseRequest->supplier_name)
                        ->setSupplierInfo(json_encode($purchaseRequest->supplier_info, JSON_UNESCAPED_UNICODE))
                        ->setWebsiteId($purchaseRequest->website_id)
                        ->setStoreId($purchaseRequest->store_id)
                        ->setStoreName($purchaseRequest->store_name)
                        ->setStatus(1)
                        ->setCreatedAt(now())
                        ->setCreatedBy($adminuserId)
                        ->save();

                    $requestItem = Mage::getModel('ved_purchase/outputrequest')->load($purchaseRequest->id);
                    $requestItem->setStatus(2)
                        ->setUpdatedBy($adminuserId)
                        ->setUpdatedAt(now());
                    $requestItem->save();

                    $purchaseId = $purchase->getId();

                    $code =  str_pad($purchaseId, 4, "0", STR_PAD_LEFT);

                    $purchase->setCode('PU/'. $purchaseRequest->store_id . '/' . date('ymdH',time()) . $code)
                        ->save();

                    $purchaseWarehouseItem = array();
                    //Only allow to insert valid record, the others will be removed
                    foreach($purchaseRequest->items as $item){
                        if($item->quantity > 0){
                            $purchaseItem = Mage::getModel("ved_purchase/purchaseitem");
                            $purchaseItem->setPurchaseId($purchaseId)
                                ->setProductSku($item->sku)
                                ->setProductName($item->product_name)
                                ->setRequestQty($item->quantity)
                                ->setOrderQty($item->order_quantity)
                                ->setPrice($item->price)
                                ->setVat($item->vat)
                                ->setStatus(1)
                                ->setType($item->type)
                                ->setUnit($item->unit)
                                ->setProductId($item->product_id)
                                ->setCreatedAt(date('Y-m-d H:i:s',time()))
                                ->save();
                        }
                    }

                    $purchase->callMessageQueue();

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

    public function addPurchaseNonOrderAction(){
        $request = json_decode(file_get_contents('php://input'));

        $websiteId = $request->website_id;
        $storeId = $request->store_id;
        $storeName = $request->store_name;
        $purchaseRequests = $request->purchase_requests;
        $receiveDate = $request->receive_date;
        $description = $request->description;

        try{
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            if($purchaseRequests){
                $connection->beginTransaction();

                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();
                $adminUserName = $admin_user_session->getUser()->getLastname() . ' ' . $admin_user_session->getUser()->getFirstname();

                foreach($purchaseRequests as $purchaseRequest){
                    $purchase = Mage::getModel("ved_purchase/purchase");
                    $website = Mage::getModel("core/website")->load($websiteId);
                    $websiteCode = $website->getCode() ? $website->getCode() : '';
                    $purchase->setSupplierId($purchaseRequest->supplier_id)
                        ->setCode('PU/'. strtoupper($websiteCode) . '/' . date('ymdH',time()))
                        ->setSupplierName($purchaseRequest->supplier_name)
                        ->setSupplierInfo(json_encode($purchaseRequest->supplier_info, JSON_UNESCAPED_UNICODE))
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

                    //Only allow to insert valid record, the others will be removed
                    foreach($purchaseRequest->items as $item){
                        $purchaseItem = Mage::getModel("ved_purchase/purchaseitem");
                        $purchaseItem->setPurchaseId($purchaseId)
                            ->setProductSku($item->sku)
                            ->setProductName($item->product_name)
                            ->setRequestQty($item->quantity)
                            ->setOrderQty(0)
                            ->setPrice($item->price)
                            ->setVat($item->vat)
                            ->setStatus(1)
                            ->setType($item->type)
                            ->setUnit($item->unit)
                            ->setProductId($item->entity_id)
                            ->setCreatedAt(date('Y-m-d H:i:s',time()))
                            ->save();
                    }

                    $purchase->callMessageQueue();

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

    public function updateCheckItemAction()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

        try {
            $request = json_decode(file_get_contents('php://input'));

            $list_item = $request->items;
            $connection->beginTransaction();

            foreach ($list_item as $item) {
                $item_id = $item->item_id;
                $pre_status = $item->pre_status;
                $receive_date = $item->expected_receive_date;
                $note_check = $item->note_check;

                $requestItem = Mage::getModel('ved_purchase/requestitem')->load($item_id);
                $checkNote = $requestItem->getNoteCheck() != null ? json_decode($requestItem->getNoteCheck()) : array();
                array_push($checkNote, $note_check ? (new DateTime("now", new DateTimeZone('Asia/Ho_Chi_Minh')))
                        ->format('Y-m-d H:i:s')." ".$note_check : "");
                $requestItem->setPreStatus($pre_status)
                    ->setNoteCheck(json_encode($checkNote, JSON_UNESCAPED_UNICODE))
                    ->save();
                if ($receive_date != null) {
                    $requestItem->setReceiveDate(DateTime::createFromFormat(
                        'Y-m-d H:i:s', $receive_date,
                        new DateTimeZone('Asia/Ho_Chi_Minh')))
                        ->save();
                }
                else {
                    $requestItem->setReceiveDate(null)->save();
                }
            }

            $connection->commit();

            $result = array('result' => 'ok', 'msg' => '');
        }
        catch (Exception $e) {
            $connection->rollback();
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
            echo json_encode($result);
        }
        echo json_encode($result); die();
    }

    public function getCheckPurchaseAction()
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
            $requestItem = $requestItemByOrder;
        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
        $result = array("result" => "ok", "data" => $requestItem);
        echo json_encode($result); die();
    }
}