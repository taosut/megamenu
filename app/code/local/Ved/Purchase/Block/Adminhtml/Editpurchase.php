<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/7/2016
 * Time: 11:38 AM
 */
class Ved_Purchase_Block_Adminhtml_Editpurchase extends Mage_Adminhtml_Block_Template {

    public function __construct()
    {
        $this->purchaseId = $this->getRequest()->get('id');
        $this->purchase = Mage::getModel("ved_purchase/purchase")->load($this->purchaseId)->getData();
        $this->purchase['created_at'] = date('Y-m-d H:i:s',Mage::getModel('core/date')->timestamp($this->purchase['created_at']));
        $this->purchase['updated_at'] = date('Y-m-d H:i:s',Mage::getModel('core/date')->timestamp($this->purchase['updated_at']));
        $this->createdBy = Mage::getModel("admin/user")->load($this->purchase['created_by'])->getData();
        $this->updatedBy = Mage::getModel("admin/user")->load($this->purchase['updated_by'])->getData();
        $this->purchaseItems = Mage::getModel("ved_purchase/purchaseitem")->getCollection()
            ->addFieldToFilter('purchase_id', $this->purchaseId)
            ->getData();
        foreach($this->purchaseItems as $key => $item){
            $this->purchaseItems[$key]['price'] = floatval($item['price']);
            $this->purchaseItems[$key]['request_qty'] = intval($item['request_qty']);
            $this->purchaseItems[$key]['vat'] = intval($item['vat']);
        }
    }

    private $purchaseId = 0;
    private $purchase;
    private $purchaseItems;
    private $createdBy = '';
    private $updatedBy = '';

    public function getPurchaseId(){
        return $this->purchaseId;
    }

    public function getPurchaseCode(){
        return $this->purchase->getCode();
    }

    public function getPurchase(){
        //var_dump(json_encode($this->purchase->getData()));die();
        return json_encode($this->purchase);
    }

    public function getPurchaseItems(){
        //var_dump(json_encode($this->purchase->getData()));die();
        return json_encode($this->purchaseItems);
    }

    public function getCreatedBy(){
        return json_encode($this->createdBy);
    }

    public function getUpdatedBy(){
        return json_encode($this->updatedBy);
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('ved_purchase/Adminhtml_Editpurchase_Grid', 'ved_purchase_item_grid'));
        return parent::_prepareLayout();
    }


    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}