<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/7/2016
 * Time: 11:38 AM
 */
class Ved_Purchase_Block_Adminhtml_Editstocktransfer extends Mage_Adminhtml_Block_Template {

    public function __construct()
    {
        //var_dump(1);die();
        $id = $this->getRequest()->get('id');
        $this->stockTransfer = Mage::getModel("ved_purchase/stocktransfer")->load($id)->getData();
        $this->fromWarehouse = Mage::getModel("core/store")->load($this->stockTransfer['request_store_id'])->getData();
        $this->createdBy = Mage::getModel("admin/user")->load($this->stockTransfer['created_by'])->getData();

        $this->stockTransferItems = Mage::getModel("ved_purchase/stocktransferitem")->getCollection()
            ->addFieldToFilter('stock_transfer_id', $id)
            ->getData();

        foreach($this->stockTransferItems as $key => $item){
            $this->stockTransferItems[$key]['request_qty'] = intval($item['request_qty']);
        }
    }

    private $stockTransfer;
    private $stockTransferItems;
    private $fromWarehouse;
    private $createdBy;


    public function getStockTransfer(){
        return json_encode($this->stockTransfer);
    }

    public function getStockTransferItems(){
        return json_encode($this->stockTransferItems);
    }

    public function getCreatedBy(){
        return json_encode($this->createdBy);
    }

    public function getFromWarehouse(){
        return json_encode($this->fromWarehouse);
    }
}