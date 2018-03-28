<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/8/2017
 * Time: 2:53 PM
 */

class Ved_Authentication_FacebookController extends Mage_Core_Controller_Front_Action
{
    public function loginAction()
    {
        $helper = Mage::helper('tek_authentication');

        $FBid = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('fbid'))));

        $customer_byFb = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToFilter('facebook_id', array('equal' => $FBid));

        if (!$customer_byFb->count()) {
            $result = array('status' => 'email_is_not_exist');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $customer = Mage::getModel("customer/customer")
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->load($customer_byFb->getFirstItem()->getId());

            if ($customer->getIsaccountactive() == '0') { // Tai khoan chua active
                $result = array('status' => 'phone_not_active');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            else {
                $helper->loginCustomer($customer); // Login user (store session)
                $result = array('status' => 'success');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
    }

    public function registerAction()
    {
        // get all param
        $helper = Mage::helper('tek_authentication');

        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('telephone'))));
        $fb_id = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('fbid'))));
        $email = $fb_id. '_fb@tekshop.vn';
        $name = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('name'))));

        $store = Mage::app()->getStore();
        $websiteId = Mage::app()->getWebsite()->getId();
        $phone_number_formatted = $helper->cutVNPhoneNumber($phone_number);

        $tmpStatus = $helper->checkIfExistPhoneNumberWithFb($phone_number, $fb_id);

        if ($tmpStatus == 'exist_phone') {
            $result = array('status' => 'phone_existed');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        else if ($tmpStatus == 'not_exist') {
            if ($helper->checkIfExistPhoneNumberWithRegister($phone_number)) {
                $customer = Mage::getModel('customer/customer')->getCollection()
                    ->addFieldToFilter('website_id', $websiteId)
                    ->addAttributeToFilter('phone_number', array('like' => '%' . $phone_number_formatted . '%'))
                    ->addFieldToFilter('email', array('like' => '%_tek@tekshop.vn'))->getFirstItem();

                $randomOtp = $helper->generateRandomString();
                $message = 'Mã xác nhận của bạn là ' . $randomOtp;
                $customer->setCustomerOtp($randomOtp);
                $customer->save();
                Mage::sendSMS($phone_number, $message);

                $result = array('status' => 'linked_fb', 'customer_id' => $customer->getId());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            else {
                $customer = Mage::getModel("customer/customer");
                $password = $helper->generateRandomString();
                $customer->setData(
                    array(
                        'website_id' => $websiteId,
                        'firstname' => $name,
                        'email' => $email,
                        'password_hash' => md5($password),
                    )
                );
                $customer->setStore($store);
                $customer->setCustomerOtp($password);
                $customer->setPhoneNumber($phone_number);
                $customer->setCreatedAt(date('Y-m-d H:i:s', time()));
                $customer->setSendsmscount(1); // Khoi tao set sendsmscount = 1 (chua resend code lan nao)
                $customer->setIsaccountactive(0); // Khoi tao set isaccountactive = 0 (chua kich hoat)
                $customer->setErrorinputcount(0); // Khoi tao set errorinputcount = 0 (chua kich hoat)
                $customer->setFacebookId($fb_id);
                $customer->save();

                $message = 'Mã xác nhận của bạn là ' . $password;
                Mage::sendSMS($phone_number, $message);

                $result = array('status' => 'send_otp', 'customer_id' => $customer->getId());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
        else {
            $customer_byPhone = Mage::getModel('customer/customer')->getCollection()
                ->addFieldToFilter('website_id', $websiteId)
                ->addAttributeToFilter('phone_number', array('like' => '%' . $phone_number_formatted . '%'))
                ->addFieldToFilter('email', array('like' => '%_fb@tekshop.vn'));
            $customer = Mage::getModel('customer/customer')
                ->load($customer_byPhone->getFirstItem()->getId());

            if (intval($customer->getSendsmscount()) < 5) {
                $randomOtp = $helper->generateRandomString();
                $customer->setCustomerOtp($randomOtp);
                $customer->setSendsmscount(intval($customer->getSendsmscount()) + 1);
                $customer->save();

                $message = 'Mã xác nhận của bạn là ' . $randomOtp;
                Mage::sendSMS($phone_number, $message);

                $result = array('status' => 'send_otp', 'customer_id' => $customer->getId());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            else {
                $result = array('status' => 'over_sms');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
    }

    public function checkRegisterAction()
    {
        $fb_id = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('fbid'))));
        $websiteId = Mage::app()->getWebsite()->getId();

        $customer = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addAttributeToFilter('facebook_id', array('like' => $fb_id));

        if ($customer->count()) {
            $result = array('status' => 'exist');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        else {
            $result = array('status' => 'register');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
}