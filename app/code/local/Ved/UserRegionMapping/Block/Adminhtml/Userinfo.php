<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 11/30/2016
 * Time: 11:31 AM
 */
class Ved_UserRegionMapping_Block_Adminhtml_Userinfo extends Mage_Adminhtml_Block_Template
{
    private $user;

    public function __construct()
    {

        $this->user = Mage::getModel('admin/user')->load($this->getRequest()->getParam('user_id'));
        parent::__construct();
    }

    public function getUserName()
    {
        return $this->user->getName();
    }

    public function getUserEmail()
    {
        return $this->user->getEmail();
    }
}