<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/8/2017
 * Time: 3:07 PM
 */

class Ved_Authentication_OtpController extends Mage_Core_Controller_Front_Action
{
    public function resendSmsAction()
    {
        $helper = Mage::helper('tek_authentication');
        $customer_id = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('customer_id'))));

        $websiteId = Mage::app()->getWebsite()->getId();
        $customer = Mage::getModel("customer/customer")
            ->setWebsiteId($websiteId)
            ->load($customer_id);
        $phone_number = $customer->getPhoneNumber();

        $countSms = intval($customer->getSendsmscount());
        if ($countSms < 5) {
            $randomOtp = $helper->generateRandomString();
            $customer->setCustomerOtp($randomOtp);
            $customer->setSendsmscount($countSms + 1);
            $customer->save();

            $message = 'Mã xác nhận của bạn là ' . $randomOtp;
            Mage::sendSMS($phone_number, $message);
            $result = array('status' => 'sent_sms');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        else {
            $result = array('status' => 'over_sms');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkAction()
    {
        $customer = Mage::getModel('customer/customer')
            ->load(intval(trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('customer_id'))))));
        $otpCode = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('otpCode'))));

        $check_sms_count = intval($customer->getErrorinputcount());

        if ($otpCode != $customer->getCustomerOtp()) {
            $customer->setErrorinputcount(strval($check_sms_count + 1));
            $customer->save();
            $result = array('status' => 'wrong_otp');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $customer->setIsaccountactive(1);
            $customer->save();
            $result = array('status' => 'right_otp', 'customer_id' => $customer->getId());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkRegisterAction()
    {
        $customer = Mage::getModel('customer/customer')
            ->load(intval(trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('customer_id'))))));
        $otpCode = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('otpCode'))));

        $check_sms_count = intval($customer->getErrorinputcount());
        $helper = Mage::helper('tek_authentication');

        if ($otpCode != $customer->getCustomerOtp()) {
            $customer->setErrorinputcount(strval($check_sms_count + 1));
            $customer->save();
            $result = array('status' => 'wrong_otp');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $customer->setIsaccountactive(1);
            $customer->save();
            $helper->loginCustomer($customer);
            $result = array('status' => 'right_otp');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkAndLoginAction()
    {
        $customer = Mage::getModel('customer/customer')
            ->load(intval(trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('customer_id'))))));
        $otpCode = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('otpCode'))));
        $fb_id = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('fb_id'))));
        $helper = Mage::helper('tek_authentication');

        $check_sms_count = intval($customer->getErrorinputcount());

        if ($otpCode != $customer->getCustomerOtp()) {
            $customer->setErrorinputcount(strval($check_sms_count + 1));
            $customer->save();
            $result = array('status' => 'wrong_otp');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $customer->setIsaccountactive(1);
            $customer->setFacebookId($fb_id);
            $customer->save();
            $helper->loginCustomer($customer);
            $result = array('status' => 'right_otp', 'customer_id' => $customer->getId());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
}