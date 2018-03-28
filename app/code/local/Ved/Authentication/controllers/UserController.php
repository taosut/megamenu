<?php

/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 8/11/2017
 * Time: 3:02 PM
 */
class Ved_Authentication_UserController extends Mage_Core_Controller_Front_Action
{
    public function registerAction()
    {
        // get all param
        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('telephone'))));
        $email = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('email'))));
        $password = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('password'))));
        $name = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('name'))));

        $helper = Mage::helper('tek_authentication');

        if ($helper->checkIfExistAccount($phone_number)) {
            $websiteId = Mage::app()->getWebsite()->getId();
            $phone_number_formatted = $helper->cutVNPhoneNumber($phone_number);
            $tmpEmail = $phone_number_formatted . '_tek@tekshop.vn';

            $customer_item = Mage::getModel('customer/customer')->getCollection()
                ->addFieldToFilter('website_id', $websiteId)
                ->addFieldToFilter('email', array('like' => '%'.$tmpEmail));

            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId)
                ->load($customer_item->getFirstItem()->getId());

            if ($customer->getIsaccountactive() == '0') {
                $customer = $helper->updateCustomer($customer->getId(), $phone_number, $email, $password, $name, 0);
                $otp = $helper->generateRandomString();
                $customer->setCustomerOtp($otp);
                $customer->save();
                $message = 'Mã xác nhận của bạn là ' . $otp;
                Mage::sendSMS($phone_number, $message);
                $result = array('status' => 'account_not_active', 'customer_id' => $customer->getId());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            else {
                $result = array('status' => 'account_exist');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
        else {
            if ($helper->checkIfRegisterFb($phone_number) < 0) {
                if ($helper->checkIfExistOrder($phone_number)) {
                    $websiteId = Mage::app()->getWebsite()->getId();
                    $phone_number_formatted = $helper->cutVNPhoneNumber($phone_number);
                    $customer_item = Mage::getModel('customer/customer')->getCollection()
                        ->addFieldToFilter('website_id', $websiteId)
                        ->addFieldToFilter('email', array('like' => '%'.$phone_number_formatted.'@tekshop.vn'));

                    if ($customer_item->count()) {
                        $customer = $helper->updateCustomer($customer_item->getFirstItem()->getId(),
                            $phone_number, $email, $password, $name);
                    }
                    else {
                        $customer = $helper->createCustomer($phone_number, $email, $password, $name);
                    }
                }
                else {
                    $customer = $helper->createCustomer($phone_number, $email, $password, $name);
                }
//                $helper->loginCustomer($customer);
                $result = array('status' => 'created', 'customer_id' => $customer->getId());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            else {
                $customer_id = $helper->checkIfRegisterFb($phone_number);
                $result = array('status' => 'account_fb_exist', 'customer_id' => $customer_id);
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
    }

    public function logoutAction()
    {
        Mage::getSingleton('customer/session')->logout();
        $this->_redirect("/");
    }

    public function loginAction()
    {
        try {
            $helper = Mage::helper('tek_authentication');

            $customer_telephone = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('telephone'))));
            $customer_telephone = $helper->cutVNPhoneNumber($customer_telephone);

            $customer_item = Mage::getModel("customer/customer")->getCollection()
                ->addAttributeToFilter('phone_number', array("like" => "%$customer_telephone%"));

            if (!$customer_item->count()) {
                $result = array('status' => 'account_not_exist');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } else {
                $customer = Mage::getModel("customer/customer")
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->load($customer_item->getFirstItem()->getId());

                $password = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('password'))));

                $customer_password = $customer->getData('password_hash');

                if ($customer->getIsaccountactive() == '0') {
                    $result = array('status' => 'account_not_active');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }

                if (md5($password) == $customer_password) { //Mat khau trung khop
                    $helper->loginCustomer($customer); // Login user (store session)
                    $result = array('status' => 'password_is_valid');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                } else {
                    $result = array('status' => 'password_is_invalid');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                }
            }
        } catch (Exception $e) {
        }
    }

    public function updateAction()
    {
        $helper = Mage::helper('tek_authentication');

        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('telephone'))));
        $email = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('email'))));
        $password = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('password'))));
        $name = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('name'))));
        $customer_id = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('customer_id'))));

        $customer = $helper->updateCustomer($customer_id, $phone_number, $email, $password, $name);

        $helper->loginCustomer($customer); // Login for this user
        $result = array('status' => 'signed');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}