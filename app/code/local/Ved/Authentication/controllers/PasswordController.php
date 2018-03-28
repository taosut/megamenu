<?php

/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 8/11/2017
 * Time: 3:02 PM
 */
class Ved_Authentication_PasswordController extends Mage_Core_Controller_Front_Action
{
    public function forgetAction()
    {
        $helper = Mage::helper('tek_authentication');

        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('telephone'))));
        $phone_number_formatted = $helper->cutVNPhoneNumber($phone_number);
        $customer = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToFilter('phone_number', array('like' => '%' . $phone_number_formatted . '%'));

        if (!$customer->count()) { // khong ton tai so 1n thoai
            $result = array('status' => 'not_exist_customer');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $randomOtp = $helper->generateRandomString();
            $firstCustomer = Mage::getModel("customer/customer")
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->load($customer->getFirstItem()->getId());

            if (intval($firstCustomer->getSendsmscount()) < 5) {
                $firstCustomer->setCustomerOtp($randomOtp);
                $tmp = intval($firstCustomer->getSendsmscount()) + 1;
                $firstCustomer->setSendsmscount(strval($tmp));
                $firstCustomer->save();
                $message = 'Mã xác nhận của bạn là ' . $randomOtp;
                Mage::sendSMS($phone_number, $message);
                $customer_load = Mage::getModel('customer/customer')->load($firstCustomer->getId());

                $result = array('status' => 'true',
                    'customer_id' => $firstCustomer->getId(),
                    'name' => $customer_load->getFirstname()
                );

                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            else {
                $result = array('status' => 'over_sms');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
    }

    public function resetAction()
    {
        $helper = Mage::helper('tek_authentication');

        $customer = Mage::getModel('customer/customer')
            ->load(intval(trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('customer_id'))))));
        $password = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('password'))));

        $customer->setPasswordHash(md5($password));
        $customer->save();

        $helper->loginCustomer($customer); // Login for this user
        $result = array('status' => 'signed');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
