<?php

class Ved_Tracking_OrderController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $storeIds = Mage::app()->getWebsite()->getStoreIds();

        // Check user login session
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $phone_number = Mage::getSingleton('customer/session')->getCustomer()->getPhoneNumber();
            if ($this->getRequest()->getMethod() == "POST") {
                $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));
            }
            $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
            $orders = Mage::getModel('sales/order')->getCollection()->setOrder('created_at', 'DESC');
            $orders->getSelect()->join(
                'sales_flat_order_address',
                'main_table.entity_id = sales_flat_order_address.parent_id',
                array('telephone', 'city', 'postcode', 'country_id', 'parent_id')
            )
                ->where("main_table.store_id in (" . implode(',', $storeIds) . ')')
                ->where("main_table.state != 'canceled'")
                ->where("sales_flat_order_address.telephone LIKE '%$phone_number_formatted%'")
                ->where("sales_flat_order_address.address_type = 'shipping'")->limit(10);

            Mage::register('orders', $orders);
            Mage::register('phone_number', $phone_number);
            $this->loadLayout();
//            $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('tracking/order'));
            $this->renderLayout();
        } else {
            $this->_redirectReferer();
        }
    }

    public function detailAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $block_order_detail = $this->getLayout()->createBlock('tracking/orderdetail')->setData('order_id', $order_id);
        $result = array('order_detail_html' => $block_order_detail->toHtml());
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    public function searchAction()
    {
        $storeId = Mage::app()->getStore()->getId();

        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $order_id = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('search_key_order_id'))));

        $orders = Mage::getModel('sales/order')->getCollection()->setOrder('created_at', 'DESC');
        $orders->getSelect()->join(
            'sales_flat_order_address',
            'main_table.entity_id = sales_flat_order_address.parent_id',
            array('telephone', 'city', 'postcode', 'country_id', 'parent_id')
        )
            ->where("main_table.store_id = $storeId")
            ->where("main_table.state != 'canceled'")
            ->where("sales_flat_order_address.telephone LIKE '%$phone_number_formatted%'")
            ->where("main_table.increment_id LIKE '%$order_id%'")
            ->where("sales_flat_order_address.address_type = 'shipping'")->limit(10);
        $first_order_id = $orders->getFirstItem()->getIncrementId();

        $block_searched_order = $this->getLayout()->createBlock('tracking/searchorder')->setData('orders', $orders);
        $block_order_detail = $this->getLayout()->createBlock('tracking/orderdetail')->setData('order_id', $first_order_id);

        $result = array('search_order_html' => $block_searched_order->toHtml(), 'order_detail_html' => $block_order_detail->toHtml());
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    private function cutVNPhoneNumber($phone_number)
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
        return 'false';
    }

    public function ajaxTrackOrderAction()
    {
        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));

        if ($this->checkIfExistOrder($phone_number)) { // Neu so dien thoai co don hang trong he thong
            if ($this->checkIfExistCustomer($phone_number)) { // Neu so dien thoai da duoc dang ky TK

                if ($this->isOverSmsCount($phone_number)) { // Neu tai khoan vuot qua so lan gui SMS
                    $result = array('error_message' => 'Tài khoản cần hỗ trợ');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                } else if (!$this->isActive($phone_number)) { // Neu tai khoan chua active

                    if ($this->isOverErrorInputCount($phone_number)) { // Neu tai khoan vuot qua so lan nhap sai ma
                        $result = array('error_message' => 'Tài khoản vượt quá số lần nhập sai mã');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    } else {
                        $result = array('error_message' => 'Tài khoản chưa active');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }
                } else { // Neu tai khoan da active

                    $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);

                    // Check user login session
                    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $customer_email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
                        $customer_email = $this->cutVNPhoneNumber($customer_email);
                        $email = $phone_number_formatted . '@tekshop.vn';
                        if ($customer_email == $email) {
                            $result = array('error_message' => '', 'is_login' => 'true');
                            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                        } else {
                            $result = array('error_message' => '', 'is_login' => 'false');
                            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                        }
                    } else {
                        $result = array('error_message' => '', 'is_login' => 'false');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }

                }

            } else { // Neu so dien thoai chua duoc dang ky TK

                $result = array('error_message' => 'Chưa có tài khoản');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }

        } else { // Neu so dien thoai khong co don hang trong he thong

            $result = array('error_message' => 'Hệ thống chưa ghi nhận được đơn hàng nào với số điện thoại này');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function ajaxTrackOrderMobileAction()
    {
        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));

        if ($this->checkIfExistOrder($phone_number)) { // Neu so dien thoai co don hang trong he thong

            if ($this->checkIfExistCustomer($phone_number)) { // Neu so dien thoai da duoc dang ky TK

                if ($this->isOverSmsCount($phone_number)) { // Neu tai khoan vuot qua so lan gui SMS
                    $result = array('error_message' => 'Tài khoản cần hỗ trợ');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                } else if (!$this->isActive($phone_number)) { // Neu tai khoan chua active

                    if ($this->isOverErrorInputCount($phone_number)) { // Neu tai khoan vuot qua so lan nhap sai ma
                        $result['error_message'] = 'Tài khoản vượt quá số lần nhập sai mã';
                        $html = "<form id='confirm_code_form'><h4 class='text-center'>Vui lòng nhập mã xác nhận</h4>";
                        $html .= "<p class='text-center'> <span style='color: #0a568c'>Mã xác nhận đã được gửi vào số điện thoại của bạn </span>. Nếu chưa nhận được vui lòng bấm <a class='resend-code-text' onclick='goToResendCode(" . $phone_number . ")'>vào đây</a> để gửi lại </p>";
                        $html .= "<div><input  type='hidden' name='phone_number' id='phone_number' value='" . $phone_number . "'/> </div>";
                        $html .= "<input class='input-num' type='text' name='code' id='code'/><div id='html_element' class='captcha-block'></div><div id='reCaptchaError' style='color:red'></div>";
                        $html .= "<div class='text-center'><button type='button' id = 'btn_cancel_code' class= 'mtek-cnbtn'>Hủy</button>&nbsp;&nbsp;<button id = 'btn_submit_code' class='mtek-scbtn'>Xác nhận</button></div><form>";

                    } else {
                        $result['error_message'] = 'Tài khoản chưa active';
                        $html = "<form id='confirm_code_form'><h4 class='text-center'>Vui lòng nhập mã xác nhận</h4>";
                        $html .= "<p class='text-center'><span style='color: #0a568c'>Mã xác nhận đã được gửi vào số điện thoại của bạn </span>. Nếu chưa nhận được vui lòng bấm <a class='resend-code-text' onclick='goToResendCode(" . $phone_number . ")'>vào đây</a> để gửi lại </p>";
                        $html .= "<div><input  type='hidden' name='phone_number' id='phone_number' value='" . $phone_number . "'/> </div>";
                        $html .= "<input class='input-num' type='text' name='code' id='code'/>";
                        $html .= "<div class='text-center'><button type='button' id = 'btn_cancel_code' class= 'mtek-cnbtn'>Hủy</button>&nbsp;&nbsp;<button type='submit' id = 'btn_submit_code' class='mtek-scbtn'>Xác nhận</button></div><form>";
                    }

                    $result['html'] = $html;
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                } else { // Neu tai khoan da active
                    $result = array('error_message' => '');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                }

            } else { // Neu so dien thoai chua duoc dang ky TK
                $result['error_message'] = 'Chưa có tài khoản';
                $html = "<form id='confirm_code_form'><h4 class='text-center'>Vui lòng nhập mã xác nhận</h4>";
                $html .= "<p class='text-center'><span style='color: #0a568c'>Mã xác nhận đã được gửi vào số điện thoại của bạn </span>. Nếu chưa nhận được vui lòng bấm <a class='resend-code-text' onclick='goToResendCode(" . $phone_number . ")'>vào đây</a> để gửi lại </p>";
                $html .= "<div><input  type='hidden' name='phone_number' id='phone_number' value='" . $phone_number . "'/> </div>";
                $html .= "<input class='input-num' type='text' name='code' id='code'/>";
                $html .= "<div class='text-center'><button  type='button' id = 'btn_cancel_code' class= 'mtek-cnbtn'>Hủy</button>&nbsp;&nbsp;<button id = 'btn_submit_code' class='mtek-scbtn'>Xác nhận</button></div><form>";

                $result['html'] = $html;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }

        } else { // Neu so dien thoai khong co don hang trong he thong
            $result = array('error_message' => 'Hệ thống chưa ghi nhận được đơn hàng nào với số điện thoại này');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    // Kiem tra tai khoan da kich hoat chua
    private function isActive($phone_number)
    {
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

        $is_active = $customer->getIsaccountactive();
        if ($is_active == '0') {
            return false;
        }
        return true;
    }

    // Kiem tra neu so lan send sms vuot qua 4
    private function isOverSmsCount($phone_number)
    {
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);

        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

        $send_sms_count = intval($customer->getSendsmscount());

        if ($send_sms_count < 5) {
            return false;
        }
        return true;
    }

    // Kiem tra neu so lan nhap sai ma xac nhan >= 4
    private function isOverErrorInputCount($phone_number)
    {
        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

        $send_sms_count = intval($customer->getErrorinputcount());
        if ($send_sms_count < 4) {
            return false;
        }
        return true;
    }

    // Kiem tra ton tai don hang
    private function checkIfExistOrder($phone_number)
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

    // Kiem tra ton tai tai khoan
    private function checkIfExistCustomer($phone_number)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();

        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer = Mage::getModel("customer/customer")->getCollection()
            ->addFieldToFilter('email', array("like" => "%$customer_email%"))
            ->addFieldToFilter('website_id', $websiteId);

        if ($customer->count()) { // Da co tai khoan
            return true;
        } else { // Chua co tai khoan

            $quote_address = Mage::getModel('sales/quote_address')->getCollection()->addFieldToFilter('telephone', array("like" => "%$phone_number_formatted%"))->getFirstItem();

            // Tao moi user va gui mat khau random qua SMS
            $customer = Mage::getModel("customer/customer");
            $random_password = $this->generateRandomString(); // Gen random password 6 ky tu
            $customer->setData(
                array(
                    'website_id' => $websiteId,
                    'firstname' => $quote_address->getData()['firstname'],
                    //       'firstname' => $random_password, // TEST
                    'email' => $phone_number . '@tekshop.vn',
                    'password_hash' => md5($random_password),
                )
            );
            $customer->setStore($store);

            $customer->setCreatedAt(date('Y-m-d H:i:s', time()));

            $customer->setSendsmscount(1); // Khoi tao set sendsmscount = 1 (chua resend code lan nao)
            $customer->setIsaccountactive(0); // Khoi tao set isaccountactive = 0 (chua kich hoat)
            $customer->setErrorinputcount(0); // Khoi tao set errorinputcount = 0 (chua kich hoat)

            $customer->save(); // Tao user

            // Send SMS
            $message = 'Mã xác nhận đăng ký tài khoản của bạn là ' . $random_password;
            $this->sendSMS($phone_number, $message);

            return false;
        }
    }

    // Lay lai mat khau
    public function reRetrievePasswordAction()
    {
        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));

        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);

        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();

        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

        $random_password = $this->generateRandomString(); // Gen random password 6 ky tu

//        $customer->setFirstname($random_password); // TEST

        // Tang so lan gui lai ma xac nhan (SMS count) len 1
        $send_sms_count = intval($customer->getSendsmscount() + 1);
        $customer->setSendsmscount($send_sms_count);

        $customer->setPasswordHash(md5($random_password));
        $customer->save();

        $error_input_count = $customer->getErrorinputcount();

        //Send SMS
        if (!$this->isOverSmsCount($phone_number)) {
            $message = 'Mã xác nhận lấy lại mật khẩu của bạn là ' . $random_password;
            $this->sendSMS($phone_number, $message);
        }

        $result = array('send_sms_count' => $send_sms_count, 'error_input_count' => $error_input_count);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    public function reRetrievePasswordMobileAction()
    {

        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));

        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

        $random_password = $this->generateRandomString(); // Gen random password 6 ky tu

//        $customer->setFirstname($random_password); // TEST

        // Tang so lan gui lai ma xac nhan (SMS count) len 1
        $send_sms_count = intval($customer->getSendsmscount() + 1);
        $customer->setSendsmscount($send_sms_count);

        $customer->setPasswordHash(md5($random_password));
        $customer->save();

        $error_input_count = $customer->getErrorinputcount();

        //Send SMS
        if (!$this->isOverSmsCount($phone_number)) {
            $message = 'Mã xác nhận lấy lại mật khẩu của bạn là ' . $random_password;
            $this->sendSMS($phone_number, $message);
        }

        if ($send_sms_count < 5) {
            if ($error_input_count >= 4) {
                $html = "<form id='confirm_code_form'><h4 class='text-center'>Vui lòng nhập mã xác nhận</h4>";
                $html .= "<p class='text-center'><span style='color: #0a568c'>Mã xác nhận đã được gửi vào số điện thoại của bạn </span>. Nếu chưa nhận được vui lòng bấm <a class='resend-code-text' onclick='goToResendCode(" . $phone_number . ")'>vào đây</a> để gửi lại </p>";
                $html .= "<div><input  type='hidden' name='phone_number' id='phone_number' value='" . $phone_number . "'/> </div>";
                $html .= "<input class='input-num' type='text' name='code' id='code'/><div id='html_element' class='captcha-block'></div><div id='reCaptchaError' style='color:red'></div>";
                $html .= "<div class='text-center'><button type='button' id = 'btn_cancel_code' class= 'mtek-cnbtn'>Hủy</button>&nbsp;&nbsp;<button id = 'btn_submit_code' class='mtek-scbtn'>Xác nhận</button></div><form>";

            } else {
                $html = "<form id='confirm_code_form'><h4 class='text-center'>Vui lòng nhập mã xác nhận</h4>";
                $html .= "<p class='text-center'><span style='color: #0a568c'>Mã xác nhận đã được gửi vào số điện thoại của bạn </span>. Nếu chưa nhận được vui lòng bấm <a class='resend-code-text' onclick='goToResendCode(" . $phone_number . ")'>vào đây</a> để gửi lại </p>";
                $html .= "<div><input  type='hidden' name='phone_number' id='phone_number' value='" . $phone_number . "'/> </div>";
                $html .= "<input class='input-num' type='text' name='code' id='code'/>";
                $html .= "<div class='text-center'><button type='button' id = 'btn_cancel_code' class= 'mtek-cnbtn'>Hủy</button>&nbsp;&nbsp;<button id = 'btn_submit_code' class='mtek-scbtn'>Xác nhận</button></div><form>";

            }
        }


        $result['html'] = $html;
        $result['error_input_count'] = $error_input_count;
        $result['send_sms_count'] = $send_sms_count;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    // Kiem tra user nhap ma xac nhan
    public function checkCodeAction()
    {
        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));

        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

        $customer_code = $customer->getData('password_hash'); // Ma xac nhan da duoc gen tu truoc
        $code = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('code')))); // Ma xac nhan user nhap

        if (md5($code) == $customer_code) { // Neu ma xac nhan trung khop

            $result = array('code_is_valid' => 'true');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else { // Neu ma xac nhan khong trung khop
            $error_input_count = intval($customer->getErrorinputcount() + 1);
            $customer->setErrorinputcount($error_input_count);
            $customer->save();

            $result = array('code_is_valid' => 'false', 'error_input_count' => $error_input_count);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkCodeMobileAction()
    {

        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));

        if ($phone_number[0] == '8' || $phone_number[0] == '+' || $phone_number[0] == '0') {
            $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        } else {
            $phone_number_formatted = $phone_number;
        }

        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);
        $customer_code = $customer->getData('password_hash'); // Ma xac nhan da duoc gen tu truoc
        $code = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('code')))); // Ma xac nhan user nhap

        if (md5($code) == $customer_code) { // Neu ma xac nhan trung khop

            $result['code_is_valid'] = 'true';
            $html = "<form id='confirm_password_form'>";
            $html .= "<h4 class='text-center'>Thiết lập mật khẩu cho tài khoản</h4>";
            $html .= "<p>Để thuận tiện cho việc tra cứu đơn hàng, Tekshop đã tạo tài khoản cho bạn. Vui lòng thiết lập mật khẩu cho tài khoản</p>";
            $html .= "<input type='hidden' name='phone_number' id='phone_number' value='" . $phone_number . "'/>";
            $html .= '<input class="password is-display mb5 input-num" type="password" id = "password" placeholder="Nhập mật khẩu..." value="">';
            $html .= '<div class="password-error text-left" style="color: red;"></div><input class="re-password input-num is-display mb5" type="password" id = "re-password" placeholder="Nhập lại mật khẩu..." value="">';
            $html .= '<div class="repassword-error text-left" style="color: red;"></div><div class="password-not-match text-left" style="color: red;"></div>';
            $html .= '<div class="text-center"><button  id="btn_cancel" class="mtek-cnbtn">Hủy</button>&nbsp;&nbsp;<button id = "btn_submit_password" class="mtek-scbtn">Xác nhận</button></div> </form>';
            $result['html'] = $html;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else { // Neu ma xac nhan khong trung khop
            $result['code_is_valid'] = 'false';
            $error_input_count = intval($customer->getErrorinputcount() + 1);
            $customer->setErrorinputcount($error_input_count);
            $customer->save();

            if ($error_input_count < 4) {
                $html = "<form id='confirm_code_form'><input  type='hidden' name='phone_number' id='phone_number' value='" . $phone_number . "'/> ";
                $html .= "<p class='text-center' style='color:red;'>Nhập sai mã xác nhận</p>";
                $html .= "<div class='sa-error-text blink_me'>Bạn đã nhập sai mã xác nhận, vui lòng nhập lại</div>Nếu chưa nhận được vui lòng bấm <a class='resend-code-text' 
                    onclick='goToResendCode(" . $phone_number . ")'>vào đây </a>để gửi lại<br>";
                $html .= "<input type='text' class='input-num' name='code' id='code'/>";
                $html .= "<div class='text-center'><button type='button' id = 'btn_cancel_code' class= 'mtek-cnbtn'>Hủy</button>&nbsp;&nbsp;<button id = 'btn_submit_code' class='mtek-scbtn'>Xác nhận</button></div><form>";
            } else {
                $html = "<form id='confirm_code_form'><p class='text-center' style='color:red;'>Nhập sai mã xác nhận</p>";
                $html .= "<p class='text-center'><span style='color: #0a568c'>Mã xác nhận đã được gửi vào số điện thoại của bạn </span>. Nếu chưa nhận được vui lòng bấm <a class='resend-code-text' onclick='goToResendCode(" . $phone_number . ")'>vào đây</a> để gửi lại </p>";
                $html .= "<div><input  type='hidden' name='phone_number' id='phone_number' value='" . $phone_number . "'/> </div>";
                $html .= "<input class='input-num' type='text' name='code' id='code'/><div id='html_element' class='captcha-block'></div><div id='reCaptchaError' style='color:red'></div>";
                $html .= "<div class='text-center'><button type='button' id = 'btn_cancel_code' class= 'mtek-cnbtn'>Hủy</button>&nbsp;&nbsp;<button id = 'btn_submit_code' class='mtek-scbtn'>Xác nhận</button></div><form>";
            }

            $result['html'] = $html;
            $result['error_input_count'] = $error_input_count;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    // Gui lai ma xac nhan
    public function resendCodeAction()
    {
        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));

        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

        $random_password = $this->generateRandomString(); // Gen random password 6 ky tu

//        $customer->setFirstname($random_password); // TEST

        $customer->setPasswordHash(md5($random_password));

        // Tang so lan gui lai ma xac nhan (SMS count) len 1
        $send_sms_count = intval($customer->getSendsmscount() + 1);
        $customer->setSendsmscount($send_sms_count);
        $customer->setErrorinputcount(0);

        $customer->save();

        //Send SMS
        if (!$this->isOverSmsCount($phone_number)) {
            if ($this->isActive($phone_number)) {
                $message = 'Mã xác nhận lấy lại mật khẩu của bạn là ' . $random_password;
            } else {
                $message = 'Mã xác nhận đăng ký tài khoản của bạn là ' . $random_password;
            }
            $this->sendSMS($phone_number, $message);
        }

        $result = array('send_sms_count' => $send_sms_count);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function resendCodeMobileAction()
    {
        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));
        if ($phone_number[0] == '8' || $phone_number[0] == '+' || $phone_number[0] == '0') {
            $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        } else {
            $phone_number_formatted = $phone_number;
        }

        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

        $random_password = $this->generateRandomString(); // Gen random password 6 ky tu

//        $customer->setFirstname($random_password); // TEST

        $customer->setPasswordHash(md5($random_password));

        // Tang so lan gui lai ma xac nhan (SMS count) len 1
        $send_sms_count = intval($customer->getSendsmscount() + 1);
        $customer->setSendsmscount($send_sms_count);
        $customer->setErrorinputcount(0);

        $customer->save();
        $phone_number = '0' . $phone_number_formatted;
        //Send SMS
        if (!$this->isOverSmsCount($phone_number)) {
            if ($this->isActive($phone_number)) {
                $message = 'Mã xác nhận lấy lại mật khẩu của bạn là ' . $random_password;
            } else {
                $message = 'Mã xác nhận đăng ký tài khoản của bạn là ' . $random_password;
            }

            $this->sendSMS($phone_number, $message);
        }

        $result['send_sms_count'] = $send_sms_count;
        $html = "<form id='confirm_code_form'><h4 class='text-center'>Vui lòng nhập mã xác nhận</h4>";
        $html .= "<p class='text-center'><span style='color: #0a568c'>Mã xác nhận đã được gửi vào số điện thoại của bạn </span>. Nếu chưa nhận được vui lòng bấm <a class='resend-code-text' onclick='goToResendCode(" . $phone_number . ")'>vào đây</a> để gửi lại </p>";
        $html .= "<div><input  type='hidden' name='phone_number' id='phone_number' value='" . $phone_number . "'/> </div>";
        $html .= "<input class='input-num' type='text' name='code' id='code' />";
        $html .= "<div class='text-center'><button type='button' id = 'btn_cancel_code' class= 'mtek-cnbtn'>Hủy</button>&nbsp;&nbsp;<button id = 'btn_submit_code' class='mtek-scbtn'>Xác nhận</button></div><form>";
        $result['html'] = $html;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    // Tao mat khau moi cho user
    public function createPasswordAction()
    {
        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));

        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

        $password = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('password')))); // Mat khau user da nhap vao

//        $customer->setFirstname($password); // TEST

        $customer->setPasswordHash(md5($password)); // Tao mat khau cho user

        // Khi kich hoat tai khoan thanh cong, set sendsmscount = 0 (reset) va isaccountactive = 1, errorinputcount = 0
        $customer->setSendsmscount(0);
        $customer->setIsaccountactive(1);
        $customer->setErrorinputcount(0);

        $customer->save();

        $this->loginUser($customer_item->getData()['email'], $password); // Login user (store session)

        $result = array('is_success' => 'true');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    // Kiem tra user nhap mat khau dung khong
    public function checkPasswordAction()
    {
        $phone_number = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('phone_number'))));

        $phone_number_formatted = $this->cutVNPhoneNumber($phone_number);
        $customer_email = $phone_number_formatted . '@tekshop.vn';
        $customer_item = Mage::getModel("customer/customer")->getCollection()->addFieldToFilter('email', array("like" => "%$customer_email%"))->getFirstItem();
        $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customer_item->getData()['email']);

//        var_dump($customer_item->getData()['email']); die();

        $password = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('password'))));

        $customer_password = $customer->getData('password_hash');

        if (md5($password) == $customer_password) { //Mat khau trung khop
            $this->loginUser($customer_item->getData()['email'], $password); // Login user (store session)
            $result = array('password_is_valid' => 'true');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $result = array('password_is_valid' => 'false');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }

    }

    public function sendSMS($phone, $message)
    {
        $key = "8b78a259eb04ccc9676a80abe791e16d";
        $timestamp = time();
        $skey = md5($key);
        $sign = md5($key . $phone . $timestamp);
        $data = array(
            'skey' => $skey,
            'sign' => $sign,
            'phone' => $phone,
            'body' => $message,
            'timestamp' => $timestamp,
            'brand' => 'PV',
            'type' => '',
        );

        $data_params = array();
        foreach ($data as $k => $v) {
            $data_params[] = urlencode($k) . '=' . urlencode($v);
        }
        $data_str = implode('&', $data_params);

        $parts = parse_url('http://sms-brandname.teko.vn/sendMT');
        $host = $parts['host'];
        if (isset($parts['port'])) {
            $port = $parts['port'];
        } else {
            $port = $parts['scheme'] == 'https' ? 443 : 80;
        }

        $fp = fsockopen(
            $host,
            $port,
            $errno,
            $errstr,
            2
        );

        if (!$fp) {
            return false;
        } else {
            $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
            $out .= "Host: " . $parts['host'] . "\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            if (!empty($data_str)) {
                $out .= "Content-Length: " . strlen($data_str) . "\r\n";
            }
            $out .= "Connection: Close\r\n\r\n";
            if (!empty($data_str)) {
                $out .= $data_str;
            }
            fwrite($fp, $out);
            fclose($fp);
            return true;
        }
    }

    function loginUser($email, $password)
    {
        require_once("app/Mage.php");
        umask(0);
        ob_start();
        $session = Mage::getSingleton('customer/session');

        Mage::app();
        Mage::getSingleton("core/session", array("name" => "frontend"));

        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        $customer = Mage::getModel("customer/customer");
        $customer->website_id = $websiteId;
        $customer->setStore($store);
        try {
            //$customer->loadByEmail($email);
            $session->login($email, $password);
            $session->setCustomerAsLoggedIn($session->getCustomer());


        } catch (Exception $e) {

        }
    }

    private function generateRandomString($length = 6)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    public function checkphoneAction()
    {
        // Check user login session
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer_email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
            $phone_number = str_replace('@tekshop.vn', '', $customer_email);
            $redirectUrl = Mage::getUrl('tracking/order/', array('_query' => 'phone_number=' . $phone_number));
            Mage::app()->getResponse()->setRedirect($redirectUrl)->sendResponse();
        } else {
            $this->loadLayout();
            $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('tracking/checkphone'));
            $this->renderLayout();
        }

    }


}

?>