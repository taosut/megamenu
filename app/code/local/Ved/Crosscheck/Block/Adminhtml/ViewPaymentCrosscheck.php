<?php

class Ved_Crosscheck_Block_Adminhtml_ViewPaymentCrosscheck extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    private $store;

    private $listSelected;
    private $createdBy;
    private $crosscheckId;
    private $paymentCrosscheck;

    public function __construct()
    {
        parent::__construct();


        $this->crosscheckId = $this->getRequest()->get('crosscheck_id');
        if($this->crosscheckId){
            $this->paymentCrosscheck = Mage::getModel("ved_crosscheck/paymentcrosscheck")->load($this->crosscheckId);
            $this->store = Mage::getModel("core/store")->load($this->paymentCrosscheck->getStoreId());
        }
        $this->setTemplate('crosscheck/view_payment.phtml');
        $this->createdBy = Mage::getModel("admin/user")->load($this->paymentCrosscheck->getCreatedBy());
        $this->_blockGroup = 'ved_crosscheck';
        $this->_controller = 'adminhtml_ViewPaymentCrosscheck';
    }

    public function getListStore()
    {
        return $this->storeCollection->getItems();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('crosscheck_id' => $this->crosscheckId));
    }

    public function getCrosscheckId()
    {
        return $this->crosscheckId;
    }

    public function getPaymentCrosscheck(){
        return $this->paymentCrosscheck;
    }

    public function getStore(){
        return $this->store;
    }


    public function getStatus(){
        $status = '';
        switch ($this->paymentCrosscheck->getStatus()) {
            case "0":
                $status = "Đã hủy";
                break;
            case "1":
                $status = "Chờ xử lý";
                break;
            case "2":
                $status = "Đã cập nhật đơn hàng";
                break;
            default:
                $status = "Không xác định";
                break;
        }
        return $status;
    }

    public function getFullName(){
        return $this->createdBy->getLastname() . ' ' . $this->createdBy->getFirstname();
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('ved_crosscheck/Adminhtml_ViewPaymentCrosscheck_Grid', 'ved_crosscheckItem_grid'));
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