<?php

/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 8/11/2017
 * Time: 3:02 PM
 */
class Ved_Authentication_LinkedController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $helper = Mage::helper('tek_authentication');

        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('telephone'))));
        $phone_number_formatted = $helper->cutVNPhoneNumber($phone_number);
        $customer_email = $phone_number . '@tekshop.vn';
        $websiteId = Mage::app()->getWebsite()->getId();

        // Tim account voi so dien thoai da nhap
        $customer_byPhone = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addAttributeToFilter('phone_number', array('like' => '%' . $phone_number_formatted . '%'));

        $customer = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addFieldToFilter('email', array('like' => '%'.$phone_number_formatted.'_tek@tekshop.vn'));

        if ($customer->count()) {
            $tmpCustomer = $customer = Mage::getModel("customer/customer")
                ->setWebsiteId($websiteId)
                ->load($customer->getFirstItem()->getId());
            if ($tmpCustomer->getIsaccountactive() == '1') {
                $result = array('status' => 'phone_linked');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            else {
                $randomOtp = $helper->generateRandomString();
                $tmpCustomer->setCustomerOtp($randomOtp);
                $tmpCustomer->save();
                $message = 'Mã xác nhận của bạn là ' . $randomOtp;
                Mage::sendSMS($phone_number, $message);

                $result = array('status' => 'not_active', 'customer_id' => $tmpCustomer->getId());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            return;
        }

        if ($customer_byPhone->count() > 0) { // neu ton tai account => da co nguoi su dung
            $result = array('status' => 'phone_exist');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        else {
            if (!$helper->checkIfExistOrder($phone_number)) { // Neu khong ton tai don hang voi so dien thoai do
                $result = array('status' => 'order_not_exist');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            else {
                $randomOtp = $helper->generateRandomString();
                $customer = Mage::getModel("customer/customer")
                    ->setWebsiteId($websiteId)
                    ->loadByEmail($customer_email);

                if (!$customer->getId()) {
                    $customer = $helper->createCustomer($phone_number,
                        $customer_email, $randomOtp, 'tekshop', '0');
                }
                else {
                    $customer->setCustomerOtp($randomOtp);
                    $customer->setPhoneNumber($phone_number);
                    $customer->save();
                }

                $message = 'Mã xác nhận của bạn là ' . $randomOtp;
                Mage::sendSMS($phone_number, $message);

                $result = array('status' => 'allow_link', 'customer_id' => $customer->getId());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
    }
}
