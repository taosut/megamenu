<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/8/2017
 * Time: 2:29 PM
 */

class Ved_Authentication_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function checkIfRegisterFb($phone_number)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);

        $customer_byPhone = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addAttributeToFilter('phone_number', array('like' => '%' . $phone_number_formatted . '%'))
            ->addAttributeToFilter('facebook_id', array('notnull' => true));
        if ($customer_byPhone->count()) {
            return $customer_byPhone->getFirstItem()->getEntityId();
        }
        return -1;
    }

    public function createCustomer($phone_number, $email, $password, $name)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();

        // Tao moi usermoi
        $customer = Mage::getModel("customer/customer");
        $customer->setData(
            array(
                'website_id' => $websiteId,
                'firstname' => $name,
                'email' => $phone_number.'_tek@tekshop.vn',
                'password_hash' => md5($password),
            )
        );
        $customer->setStore($store);
        $otp = $this->generateRandomString();
        $customer->setCustomerOtp($otp);

        $customer->setPhoneNumber($phone_number);

        $customer->setCreatedAt(date('Y-m-d H:i:s', time()));

        $customer->setSendsmscount(1); // Khoi tao set sendsmscount = 1 (chua resend code lan nao)
        $customer->setIsaccountactive(0); // Khoi tao set isaccountactive = 0 (chua kich hoat)
        $customer->setErrorinputcount(0); // Khoi tao set errorinputcount = 0 (chua kich hoat)
        $customer->save();

        $address = Mage::getModel('customer/address');
        $address->setCustomer($customer);

        $address->setCustomerEmail($email);
        $address->save();

        $customer->addAddress($address)->setDefaultShipping($address->getId())->save();
        $message = 'Mã xác nhận của bạn là ' . $otp;
        Mage::sendSMS($phone_number, $message);
        return $customer;
    }

    public function checkIfExistAccount($phone_number)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $email = $phone_number_formatted . '_tek@tekshop.vn';

        $customer = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addFieldToFilter('email', array('like' => '%'.$email));

        if ($customer->count()) {
            return true;
        }
        return false;
    }

    public function checkIfExistPhoneNumberWithFb($phone_number, $fb_id)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);

        $customer_byPhone = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addAttributeToFilter('phone_number', array('like' => '%' . $phone_number_formatted . '%'))
            ->addAttributeToFilter('facebook_id', array('notnull' => true));

        if ($customer_byPhone->count()) {
            $customer = Mage::getModel('customer/customer')->load($customer_byPhone->getFirstItem()->getId());
            if ($customer->getFacebookId() == $fb_id) {
                return 'exist_fb';
            }
            return 'exist_phone';
        }
        return 'not_exist';
    }

    public function checkIfExistPhoneNumberWithRegister($phone_number)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);

        $customer_byPhone = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addAttributeToFilter('phone_number', array('like' => '%' . $phone_number_formatted . '%'))
            ->addFieldToFilter('email', array('like' => '%_tek@tekshop.vn'));

        if ($customer_byPhone->count()) {
            return true;
        }
        return false;
    }

    public function checkIfExistPhoneNumber($phone_number)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);

        $customer_byPhone = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addAttributeToFilter('phone_number', array('like' => '%' . $phone_number_formatted . '%'));

        if ($customer_byPhone->count()) {
            return true;
        }
        return false;
    }

    public function checkIfExistOrder($phone_number)
    {
        $storeId = Mage::app()->getStore()->getId();
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $orders = Mage::getModel('sales/order')->getCollection()->setOrder('created_at', 'DESC');
        $orders->getSelect()->join(
            'sales_flat_order_address',
            'main_table.entity_id = sales_flat_order_address.parent_id',
            array('telephone', 'city', 'postcode', 'country_id', 'parent_id')
        )
            ->where("main_table.store_id = $storeId")
            ->where("sales_flat_order_address.telephone LIKE '%$phone_number_formatted%'")
            ->where("sales_flat_order_address.address_type = 'shipping'")->limit(1);

        if ($orders->count()) {
            return true;
        }
        return false;
    }

    public function loginCustomer($customer){
        require_once("app/Mage.php");
        umask(0);
        ob_start();
        $session = Mage::getSingleton('customer/session');

        Mage::app();
        Mage::getSingleton("core/session", array("name" => "frontend"));

        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();

        $customer->website_id = $websiteId;
        $customer->setStore($store);

        try {
            $session->loginById($customer->getId());
        } catch (Exception $e) {

        }
    }

    public function cutVNPhoneNumber($phone_number)
    {
        if ($phone_number[0] == '0') {
            return ltrim($phone_number, '0');
        }
        if ($phone_number[0] == '8' && $phone_number[1] == '4') {
            return ltrim($phone_number, '84');
        }
        if ($phone_number[0] == '+' && $phone_number[1] == '8' && $phone_number[2] == '4') {
            return ltrim($phone_number, '+84');
        }
        return false;
    }

    public function generateRandomString($length = 6)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function updateCustomer($customer_id, $phone_number, $email, $password, $name, $isActive = 1)
    {
        $websiteId = Mage::app()->getWebsite()->getId();

        $customer = Mage::getModel("customer/customer")
            ->setWebsiteId($websiteId)
            ->load($customer_id);
        $customer->setPhoneNumber($phone_number);
        $customer->setFirstname($name);

        $customer->setEmail($phone_number.'_tek@tekshop.vn');
        $customer->setPasswordHash(md5($password));

        if ($isActive) {
            $customer->setSendsmscount(0);
        }
        else {
            $customer->setSendsmscount(intval($customer->getSendsmscount()) + 1);
        }
        $customer->setIsaccountactive($isActive);
        $customer->setErrorinputcount(0);
        $customer->save();

        $address = $customer->getPrimaryShippingAddress();
        if (!$address) {
            $address = Mage::getModel('customer/address');
            $address->setCustomer($customer);
            $address->setCustomerEmail($email);
            $address->save();

            $customer->addAddress($address)->setDefaultShipping($address->getId())->save();
        }
        else {
            $address->setCustomer($customer);
            $address->setCustomerEmail($email);
            $address->save();
        }
        return $customer;
    }
}
