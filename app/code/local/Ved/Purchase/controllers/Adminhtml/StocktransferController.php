<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/5/2016
 * Time: 5:33 PM
 */
class Ved_Purchase_Adminhtml_StocktransferController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        //var_dump(1);die();
        $this->_title($this->__('Purchase'))->_title($this->__('Manage Purchase'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $this->_addContent($this->getLayout()->createBlock('ved_purchase/adminhtml_stocktransfer'));
        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_purchase/adminhtml_stocktransfer_grid')->toHtml()
        );
    }

    public function editAction()
    {
        $purchaseId  = (int) $this->getRequest()->getParam('id');
        $this->_title($this->__('Purchase'))->_title($this->__('Manage Purchase'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $block = $this->getLayout()->createBlock('ved_purchase/adminhtml_editstocktransfer');
        if ($block) {
            $block->setPurchaseId($purchaseId);
        }
        $this->_addContent($block);
        //var_dump($block);die();
        $this->renderLayout();
    }

    public function listRequestAction(){
        $this->_title($this->__('Purchase'))->_title($this->__('List Purchase Request Item'));
        $this->loadLayout();
        $this->_setActiveMenu('purchase/purchase');
        $this->_addContent($this->getLayout()->createBlock('ved_purchase/adminhtml_requestitem'));
        $this->renderLayout();
    }

    public function getPurchaseAction(){
        $purchaseId = $this->getRequest()->getParam('purchase_id');
        $purchaseData = array();
        $purchaseItems = array();
        try{
            if($purchaseId){
                $purchase = Mage::getModel("ved_purchase/purchase")->load($purchaseId);
                $purchaseData = $purchase->getData();

                $purchaseItems = Mage::getModel("ved_purchase/purchaseitem")->getCollection()
                    ->addFieldToFilter('purchase_id', $purchaseId)
                    ->getData();
            }

            $result = array("result"=>"ok","msg"=>"",
                "data"=>array(
                    "purchase" => $purchaseData,
                    "purchase_item" => $purchaseItems
                ));

        }catch(Exception $e){
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);die();
    }

    public function updateTransferItemAction(){
        $request = json_decode(file_get_contents('php://input'));

        $transferId = $request->stock_transfer_id;
        $updateItems = $request->request_items;

        try {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            if ($transferId) {
                $connection->beginTransaction();

                $stockTransfer = Mage::getModel("ved_purchase/stocktransfer")->load($transferId);

                foreach($updateItems as $item){
                    $transferItem = Mage::getModel("ved_purchase/stocktransferitem")->load($item->id);
                    $transferItemData = $transferItem->getData();
                    if($transferItemData['stock_transfer_id'] == $transferId){
                        $transferItem->setRequestQty($item->quantity)
                            ->setUnit($item->unit)
                            ->setUpdatedAt(date('Y-m-d H:i:s',time()))
                            ->save();

                        $stockTransferWarehouseItem[] = array(
                            "sku" => $item->sku,
                            "entity_id" => $item->product_id,
                            "quantity" => $item->quantity,
                            "unit" => $item->unit,
                            "note" => ''
                        );
                    }


                }
                if(count($stockTransferWarehouseItem)> 0){
                    $client = new Varien_Http_Client('http://warehouse.gcafe.vn/api/stock-transfer-requests/edit');
                    $client->setMethod(Varien_Http_Client::POST);
                    $client->setParameterPost('code', $stockTransfer->getCode());
                    //$client->setParameterPost('receive_date', $receiveDate);
                    $client->setParameterPost('items', $stockTransferWarehouseItem);

                    $response = $client->request();
                    if ($response->isSuccessful()) {
                        $connection->commit();
                        $result = array("result"=>"ok","msg"=>"");
                    }else{
                        $connection->rollback();
                        $result = array("result"=>"error","msg"=>"Cannot send data to Warehouse");
                    }
                }else{
                    $result = array("result"=>"error","msg"=>"No data update");
                }
            }
        } catch(Exception $e){
            $connection->rollback();
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result);die();
    }

}