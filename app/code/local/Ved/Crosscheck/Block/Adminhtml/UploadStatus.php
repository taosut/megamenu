<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/30/2016
 * Time: 3:25 PM
 */
class Ved_Crosscheck_Block_Adminhtml_UploadStatus extends Mage_Adminhtml_Block_Template {

    private $payments;
    private $websites;

    public function __construct()
    {

        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $helper = Mage::helper("crosscheck");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);

        $this->websites = Mage::getModel('core/website')->getCollection()
            ->addFieldToFilter('website_id', array('in' => array_merge(array(-1), $websiteIds)))
            ->getData();

        $collection = Mage::getModel("ved_crosscheck/paymentcrosscheck")->getCollection();

        $collection->addFieldToFilter('status', array('in' => array(1,2)));
        //$collection->addFieldToFilter('pay_date', array('gt' => date('Y-m-d', strtotime('-7 days'))));
        $this->payments = $collection->getData();
        //var_dump($this->payments);die();
        parent::__construct();

    }



    public function getPayments(){
        return json_encode($this->payments);
    }

    public function getWebsites(){
        return json_encode($this->websites);
    }
}