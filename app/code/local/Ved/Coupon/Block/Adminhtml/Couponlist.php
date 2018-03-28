<?php

class Ved_Coupon_Block_Adminhtml_Couponlist extends Mage_Adminhtml_Block_Template
{
    private $couponLists;
    private $userName;
    private $roleName;
    private $name;

    public function __construct()
    {
        try {
            $admin_user_session = Mage::getSingleton('admin/session');
            $adminUserId = $admin_user_session->getUser()->getUserId();
            $role_data = Mage::getModel('admin/user')->load($adminUserId)->getRole()->getData();
            $this->roleName = $role_data['role_name'];
            if($role_data['role_name']=="super admin")
            {
                $couponLists = Mage::getModel('ved_coupon/coupon_request')
                    ->getCollection()->getData();
            }else{
                $couponLists = Mage::getModel('ved_coupon/coupon_request')
                    ->getCollection()
                    ->addFieldToFilter('admin_user_id',$adminUserId)
                    ->getData();
            }
            $this->couponLists = $couponLists;
            $this->name = $admin_user_session->getUser()->getName();
            $this->userName = $admin_user_session->getUser()->getUsername();
        } catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }

    public function getCouponList()
    {
        return json_encode($this->couponLists);
    }

    public function getUserName()
    {
        return $this->userName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRoleName()
    {
        return $this->roleName;
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}