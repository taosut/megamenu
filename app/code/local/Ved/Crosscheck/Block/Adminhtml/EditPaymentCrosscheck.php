<?php

class Ved_Crosscheck_Block_Adminhtml_EditPaymentCrosscheck extends Mage_Adminhtml_Block_Template
{
    private $storeCollection;

    private $listSelected;

    private $crosscheckId;
    private $paymentCrosscheck;

    public function __construct()
    {
        parent::__construct();

        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $helper = Mage::helper("crosscheck");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);

        $this->storeCollection = Mage::getModel("core/store")->getCollection()
            ->addFieldToFilter('website_id', array('in' => array_merge(array(0), $websiteIds)));
        $this->crosscheckId = $this->getRequest()->get('crosscheck_id');
        if($this->crosscheckId){
            $this->paymentCrosscheck = Mage::getModel("ved_crosscheck/paymentcrosscheck")->load($this->crosscheckId);
        }
        $this->setTemplate('crosscheck/new_payment.phtml');
    }

    public function getListStore()
    {
        return $this->storeCollection->getItems();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('crosscheck_id' => $this->crosscheckId));
    }

    public function getCrosscheckId(){
        return $this->crosscheckId;
    }

    public function getPaymentCrosscheck(){
        return $this->paymentCrosscheck;
    }

}