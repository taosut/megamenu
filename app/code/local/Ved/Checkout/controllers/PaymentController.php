<?php

require('Lib/Alepay.php');
require 'Lib/config.php';

class Ved_Checkout_PaymentController extends Mage_Checkout_Controller_Action
{
    public function dispatch($action)
    {
        if ($action == 'alepay_webhook')
            Mage::getModel('ved_adminapi/log')
                ->addData([
                    'controller' => $this->getRequest()->getControllerName(),
                    'action' => $this->getRequest()->getActionName(),
                    'request_body' => $this->getRequest()->getRawBody(),
                    'request_param' => json_encode($this->getRequest()->getParams(), JSON_UNESCAPED_UNICODE),
                    'method' => $this->getRequest()->getMethod(),
                    'response' => $this->getResponse()->getBody(),
                    'status' => 200,
                    'ip' => $this->getRequest()->getClientIp(),
                    'created_at' => now(),
                ])->save();
        parent::dispatch($action);
    }

    public function validateQuoteAction()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cart = Mage::helper('checkout/cart')->getCart();
        /** Check inStock*/
        $outStockId = array();
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            $instock_status = $product->getAttributeText('instock_status');
            if (strstr($instock_status, 'Có') || !$product->isSalable() || !$product->isAvailable()) {
                array_push($outStockId, $item->getId());
            }
        }
        if (count($outStockId)) {
            foreach ($outStockId as $id) {
                $cart->removeItem($id);
            }
            $cart->save();
            $quote->collectTotals()->save();
            $message = "Có thay đổi trong giỏ hàng, vui lòng kiểm tra lại giỏ hàng !";
            echo json_encode(array(
                'message' => $message,
                'redirect' => Mage::getUrl('checkout/cart/index')
            ));
            return;
        }
        /** Ultimate Check */
        $oldQuote = $quote->toArray();
        $newQuote = $quote->collectTotals()->save();
        $newQuote = $newQuote->toArray();
        if (floatval($oldQuote['old_grand_total']) != floatval($newQuote["subtotal_with_discount"])) {
            $quote->setOldGrandTotal($quote->getBaseSubtotalWithDiscount())->save();
            $message = "Có thay đổi trong giỏ hàng, vui lòng kiểm tra lại!";
            echo json_encode(array(
                'message' => $message,
                'redirect' => Mage::getUrl('checkout/cart/index')
            ));
            return;
        }
        /** End */
        /** Coupon Check*/
        $couponCode = $quote->getCouponCode();
        if (is_null($couponCode)) {
            echo json_encode(array('message' => 'success'));
            return;
        }
        $oldCart = $quote->toArray();
        $newCart = $quote->collectTotals()->save();
        $newCart = $newCart->toArray();
        if (floatval($oldCart["subtotal_with_discount"]) != floatval($newCart["subtotal_with_discount"]) && floatval($oldCart["grand_total"]) != floatval($newCart["grand_total"])) {
            $message = "Có thay đổi trong giỏ hàng, vui lòng kiểm tra lại giỏ hàng !";
            echo json_encode(array(
                'message' => $message,
                'redirect' => Mage::getUrl('checkout/cart/index')
            ));
            return;
        } else {
            echo json_encode(array('message' => 'success'));
            return;
        }
    }

//    public function validateQuoteAction()
//    {
//        $quote = Mage::getSingleton('checkout/session')->getQuote();
//        $couponCode = $quote->getCouponCode();
//        /** Check Instock Status */
//
//
//        /** End Check */
//        if(is_null($couponCode))
//        {
//            echo json_encode(array('message'  => 'success'));
//            return;
//        }
//        $appliedRuleIds = $quote->getAppliedRuleIds();
//        if($appliedRuleIds){
//            $appliedRuleIds = explode(',', $appliedRuleIds);
//            $rules =  Mage::getModel('salesrule/rule')->getCollection()->addFieldToFilter('rule_id' , array('in' => $appliedRuleIds))->getData();
//            // Check rules
//            foreach ($rules as $rule)
//            {
//                if(!$rule['is_active'])
//                {
//                    $message = "Chương trình khuyến mại chưa kích hoạt";
//                    echo json_encode(array(
//                        'message'  => $message,
//                        'redirect' => Mage::getUrl('checkout/cart/index')
//                    ));
//                    return;
//                }
//            }
//            // Check coupon
//            $couponCode = Mage::getModel('salesrule/coupon')->getCollection()
//                ->addFieldToFilter('code',$couponCode)
//                ->getData();
//            if($couponCode[0]['usage_limit'] <= $couponCode[0]['times_used'])
//            {
//                $message = "Hết số lượng Coupon";
//                echo json_encode(array(
//                    'message'  => $message,
//                    'redirect' => Mage::getUrl('checkout/cart/index')
//                ));
//                return;
//            }else{
//                echo json_encode(array('message'  => 'success'));
//                return;
//            }
//        }else{
//            $message = "Chương trình khuyến mại chưa kích hoạt";
//            echo json_encode(array(
//                'message'  => $message,
//                'redirect' => Mage::getUrl('checkout/cart/index')
//            ));
//            return;
//        }
//    }

    public function indexAction()
    {

        //Check expire Ajax
        if ($this->_expireAjax()) {
            //Redirect to cart
            $this->_redirect('checkout/cart');
        }
        //Load layout
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        //Get info
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        Mage::register('customer_note', $quote->getShippingAddress()->getCustomerNotes());
        if (!isset($quote)) {
            print "Quote not exits";
            die();
        }
        $billAddress = $quote->getBillingAddress();
        $info = array(
            'region_name' => $billAddress->getRegion(),
            'city_name' => $billAddress->getCity(),
            'current_plan' => 1,
        );
        $quoteData = $quote->getData();
        $grand_total = 0;
        if (isset($quoteData['grand_total'])) {
            $grand_total = $quoteData['grand_total'];
        }
        //Get Security Info
        $token = array(
            'access_key' => Mage::getSingleton('core/session')->getFormKey(),
            'profile_id' => '',
            'transaction_uuid' => '',
            'reference_number' => '',
            'device_fingerprint_id' => '',
            'customer_ip_address' => $quoteData['remote_ip'],
            'signed_date_time' => '',
            'locale' => 'vi-VN',
            'currency' => 'vnd',
            'amount' => $grand_total,
            'signature' => '',
        );

        $block = Mage::app()->getLayout()->getBlock('checkout.payment.panel');
        if ($block) {//check if block actually exists
            $block->setData('token', $token);
            $block->setData('info', $info);
        }
        $this->renderLayout();
    }


    /**
     * Create order action
     */
    public function saveOrderAction()
    {

        //Check valid key
        if (!$this->_validateForm()) {
            $this->_redirect('*/*');
            return;
        }

        //Check expire Ajax
        if ($this->_expireAjax()) {
            return;
        }
        $oncepage = Mage::getSingleton('checkout/type_onepage');
        $oncepage->initCheckout();

        //$quote = Mage::getSingleton('checkout/session')->getQuote();
        $info_user_billing = $this->getRequest()->getParam('infoUserBilling');
        $info_order_company = $this->getRequest()->getParam('infoOrderCompany');

        $message = array();
        //var_dump($info_user_billing); die();
        if (!isset($info_user_billing['no_change'])) {
            $full_name = $info_user_billing['full_name'];
            $telephone = $info_user_billing['telephone'];
            if (!isset($full_name) || empty($full_name)) {
                $message['full_name'] = 'Nhập họ tên đầy đủ';
            }
            if (!isset($telephone) || empty($telephone)) {
                $message['telephone'] = 'Nhập số điện thoại đầy đủ';
            }
            if (count($message) > 0) {
                echo json_encode($message);
                die();
            }
            //Update billing info
            $data = array(
                'firstname' => $full_name,
                'telephone' => $telephone
            );

            $billAddress = $oncepage->getQuote()->getBillingAddress();
            $billAddress->addData($data);
            $oncepage->getQuote()->setBillingAddress($billAddress);

            try {
                $oncepage->getQuote()->save();
                $oncepage->getQuote()->collectTotals()->save();
            } catch (Exception $ex) {
                $message['message'] = 'Lỗi cập nhật thông tin thanh toán';
            }
        }
        if (isset($info_order_company['is_used']) && !empty($info_order_company['is_used'])) {
            $company = $info_order_company['tax_company_name'];
            $tax = $info_order_company['tax_company_code'];
            $street = $info_order_company['tax_company_address'];
            if (!isset($company) || empty($company)) {
                $message['company'] = 'Nhập tên công ty';
            }
            if (!isset($tax) || empty($tax)) {
                $message['tax'] = 'Nhập mã số thuể ';
            }
            if (!isset($street) || empty($street)) {
                $message['street'] = 'Nhập số địa chỉ';
            }
            if (count($message) > 0) {
                echo json_encode($message);
                die();
            }
            //Update tax info
            $oncepage->getQuote()->addData([
                'is' => true,
                'vat_name' => $company,
                'vat_id' => $tax,
                'vat_address' => $street,
                'vat_address_to' => $street,
            ]);
            try {
                $oncepage->getQuote()->save();
            } catch (Exception $ex) {
                $message['message'] = 'Lỗi cập nhật thông tin hóa đơn';
            }
        }
        //Set checkout method
        $oncepage->saveCheckoutMethod('register');

        //Set shipping method
        $shipping_method = 'tablerate_bestway';

        //Set shipping_method to address
        $shipping_address = $oncepage->getQuote()->getShippingAddress();
        $shipping_address->addData(array('shipping_method' => $shipping_method));
        $oncepage->getQuote()->setShippingAddress($shipping_address);

        $oncepage->getQuote()->getShippingAddress()->implodeStreetAddress();
        $oncepage->getQuote()->getShippingAddress()->setCollectShippingRates(true);

        $result = $oncepage->saveShippingMethod($shipping_method);
        // $result will contain error data if shipping method is empty
        if (!$result) {
            $oncepage->getQuote()->collectTotals();
        }
        $oncepage->getQuote()->collectTotals()->save();

        //Update Payment Method
        $oncepage->savePayment(array('method' => 'cashondelivery'));

        try {
            $oncepage->getQuote()->save();
        } catch (Exception $ex) {
            $message['message'] = 'Error 2';
            echo json_encode($message);
            die();
        }
        try {
            $oncepage->saveOrder();
        } catch (Exception $ex) {
            print "Save order error";
            var_dump($ex);
            die();
        }
        try {
            $oncepage->getQuote()->save();
        } catch (Exception $ex) {
            $message['message'] = 'Error 2';
            echo json_encode($message);
            die();
        }

        //Redirect to success website
        //echo json_encode(array('message'=>"End"));
        echo json_encode(array('redirect' => Mage::getUrl('checkout/payment/success')));
    }

    /**
     * Save payment action
     */
    public function savePaymentAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        /**
         * @var Mage_Checkout_Model_Type_Onepage $oncepage
         */
        $request = $this->getRequest();
        $oncepage = Mage::getSingleton('checkout/type_onepage');
        $oncepage->initCheckout();
        $customer_note = nl2br(htmlspecialchars($request->getParam('customer_note')));
        $email = htmlspecialchars($request->getParam('email'));
        /**
         * @var  Mage_Sales_Model_Quote $quote
         */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $info_order_company = $request->getParam('infoOrderCompany');

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            //Set checkout method
            $oncepage->saveCheckoutMethod('register');
        } else {
            $oncepage->getQuote()->setCustomerId(null)
                ->setCustomerEmail($email)
                ->setCustomerFirstname($oncepage->getQuote()->getShippingAddress()->getFirstname())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
            //Set checkout method
            $oncepage->saveCheckoutMethod('METHOD_GUEST');
        }

        $message = array();
        if ($request->has('is_vat')) {
            $quote->addData([
                'is' => true,
                'vat_name' => $request->getParam('vat_name'),
                'vat_id' => $request->getParam('vat_id'),
                'vat_address' => $request->getParam('vat_address'),
                'vat_address_to' => $request->getParam('vat_address'),
            ]);
        }
        //Set shipping method
        $shipping_method = 'tablerate_bestway';

        //Set shipping_method to address
        $shipping_address = $oncepage->getQuote()->getShippingAddress();
        $shipping_address->addData(array('shipping_method' => $shipping_method));
        $shipping_address->setEmail($email);

        $oncepage->getQuote()->setShippingAddress($shipping_address);
        $oncepage->getQuote()->getShippingAddress()->implodeStreetAddress();
        $oncepage->getQuote()->getShippingAddress()->setCollectShippingRates(true);

        $result = $oncepage->saveShippingMethod($shipping_method);
        // $result will contain error data if shipping method is empty
        if (!$result) {
            $oncepage->getQuote()->collectTotals();
        }
        $oncepage->getQuote()->collectTotals()->save();

        //Update Payment Method
        $oncepage->savePayment(array('method' => 'cashondelivery'));

        try {
            $oncepage->getQuote()->save();
        } catch (Exception $ex) {
            $message['message'] = 'Error 2';
            echo json_encode($message);
            die();
        }
        try {
            $oncepage->saveOrder();
            $order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            $order->setCustomerNote($customer_note);
            $order->setAffiliateCode($oncepage->getQuote()->getAffiliateCode());
            $order->save();
            Mage::dispatchEvent('change_product_after_order_create', ['order' => $order]);
        } catch (Exception $ex) {
            print "Save order error";
            var_dump($ex);
            die();
        }
        try {
            $oncepage->getQuote()->save();
        } catch (Exception $ex) {
            $message['message'] = 'Error 2';
            echo json_encode($message);
            die();
        }

        //Redirect to success website
        //echo json_encode(array('message'=>"End"));
        echo json_encode(array('redirect' => Mage::getUrl('checkout/payment/success')));
    }


    /**
     * Order success action
     */
    public function successAction()
    {
        $oncepage = Mage::getSingleton('checkout/type_onepage');
        $session = $oncepage->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        //Set register for current order
        $current_order = Mage::getModel('sales/order')->load($lastOrderId);
        Mage::register('current_order', $current_order);
        $session->clear();

        //send SMS to customer and set order is verified
        if ($this->verify_order($current_order)) {
            $current_order->verify();  //Change to verified order
        }

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }

    public function verify_order($order)
    {
        return true;

//        $address = $order->getShippingAddress();
//        if (isset($address)) {
//            $phone_number = $address->getTelephone();
//            $phone_number = $this->normalize_phonenumber($phone_number);
//            if (isset($phone_number) && $this->is_vietnam_telephone($phone_number)) {
//                if (in_array($order->getStoreId(), array(21, 20))) {
//                    if (date("m", time() + 7 * 3600) == '11') {
//                        $message = Mage::helper('checkout')->__('Ma don hang cua Quy khach la %s. Quy khach duoc tham gia chuong trinh "Mua Hang Thang 11, Trung iPhone 8". Lien he TekShop: https://m.me/tekshopvietnam.', $order->getIncrementId());
//                    } else {
//                        $message = Mage::helper('checkout')->__('Ban da dat hang thanh cong. Ma don hang cua ban la: %s. Neu ban muon tra cuu don hang hoac tu van them, hay chat voi chung toi tai tekshop.vn', $order->getIncrementId());
//                    }
//
//                    Mage::callMessageQueue([
//                        "msg" => $message,
//                        "phone" => $phone_number,
//                        "timestamp" => time(),
//                        "brand" => "Tekshop",
//                        "app" => "Tekshop"
//                    ], 'teko.sms');
//                    return true;
////                    return Mage::sendSMS($phone_number, $message);
//                } else {
//                    $message = Mage::helper('checkout')->__('Cam on quy khach da dat hang. Ma don hang: %s. Chung toi se kiem tra hang hoa va thong bao thoi gian giao hang toi quy khach. HT: 19001232', $order->getIncrementId());
//                    Mage::callMessageQueue([
//                        "msg" => $message,
//                        "phone" => $phone_number,
//                        "timestamp" => time(),
//                        "brand" => "GCafe",
//                        "app" => "GcafeShop"
//                    ], 'teko.sms');
//                    return true;
//                }
//                //$message = Mage::helper('checkout')->__('Gcafeshop da nhan duoc don hang so %s cua ban. Chung toi se giao hang trong 24h. Tat ca san pham deu duoc bao hanh chinh hang. Ho tro: 19001232',$order->getIncrementId());
//                //$message = Mage::helper('checkout')->__('Gcafeshop da nhan duoc don hang so %s cua ban. Chung toi se giao hang cho ban vao ngay 4-5/5. Tat ca san pham deu duoc bao hanh chinh hang. Ho tro: 19001232',$order->getIncrementId());
//                //return true;
//
//                //return true;
//            }
//        }
//        return false;
    }

    public function normalize_phonenumber($phone_number)
    {
        $phone_number = trim($phone_number);
        $phone_number = str_replace(" ", "", $phone_number);
        $phone_number = str_replace("-", "", $phone_number);
        $phone_number = str_replace(".", "", $phone_number);
        return $phone_number;
    }

    public function is_vietnam_telephone($phone_number)
    {
        if (strlen($phone_number) < 14 && strlen($phone_number) > 8) {
            return true;
        }
        return false;
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
            'app' => 'GcafeShop',
            'brand' => 'GCafe',
            'type' => '',
        );

        $data_params = array();
        foreach ($data as $k => $v) {
            $data_params[] = urlencode($k) . '=' . urlencode($v);
        }
        $data_str = implode('&', $data_params);

        $parts = parse_url('http://brandname.ved.com.vn/sendMT');
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

    /**
     * Failure action
     */
    public function failureAction()
    {
        $checkout = Mage::getSingleton('checkout/session');
        $lastQuoteId = $checkout->getLastQuoteId();
        $lastOrderId = $checkout->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /******* Support Function ********************/
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))
        ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        return false;
    }

    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    protected function _validateForm()
    {
        $access_key = $this->getRequest()->getParam('access_key', null);
        $profile_id = $this->getRequest()->getParam('profile_id', null);
        $transaction_uuid = $this->getRequest()->getParam('transaction_uuid', null);
        $reference_number = $this->getRequest()->getParam('reference_number', null);
        $device_fingerprint_id = $this->getRequest()->getParam('device_fingerprint_id', null);
        $customer_ip_address = $this->getRequest()->getParam('customer_ip_address', null);
        $signed_date_time = $this->getRequest()->getParam('signed_date_time', null);
        $locale = $this->getRequest()->getParam('locale', null);
        $currency = $this->getRequest()->getParam('currency', null);
        $amount = $this->getRequest()->getParam('amount', null);
        $signature = $this->getRequest()->getParam('signature', null);

        //Check security
        if ($access_key != Mage::getSingleton('core/session')->getFormKey()) {
            return false;
        }

        return true;
    }

    protected function _prepareCustomerQuote()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();;
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $customerBilling = $billing->exportCustomerAddress();
            $customer->addAddress($customerBilling);
            $billing->setCustomerAddress($customerBilling);
        }
        if ($shipping && !$shipping->getSameAsBilling() &&
            (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
        }

        if (isset($customerBilling) && !$customer->getDefaultBilling()) {
            $customerBilling->setIsDefaultBilling(true);
        }
        if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
            $customerShipping->setIsDefaultShipping(true);
        } else if (isset($customerBilling) && !$customer->getDefaultShipping()) {
            $customerBilling->setIsDefaultShipping(true);
        }
        $quote->setCustomer($customer);
    }


    /** Instalment */
    public function AlePayAction()
    {
        $params = $this->getRequest()->getParams();
        $grand_total = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
        $totalItem = Mage::helper('checkout/cart')->getSummaryCount();
        // Config
        $config = array(
            "apiKey" => (string)Mage::getConfig()->getNode('global/alepay/apiKey'),
            "encryptKey" => (string)Mage::getConfig()->getNode('global/alepay/encryptKey'),
            "checksumKey" => (string)Mage::getConfig()->getNode('global/alepay/checksumKey'),
            "callbackUrl" => (string)Mage::getConfig()->getNode('global/alepay/callbackUrl'),
            "env" => (string)Mage::getConfig()->getNode('global/alepay/env'),
        );
        $alepay = new Alepay($config);
        $data = array();
        $data['cancelUrl'] = (string)Mage::getConfig()->getNode('global/alepay/cancelURL');
        $data['amount'] = intval($grand_total);
        $data['orderCode'] = date('dmY') . '_' . uniqid();
        $data['currency'] = "VND";
        //$data['orderDescription'] = trim($params['customer_note']);
        $data['orderDescription'] = "Đơn hàng trả góp qua Alepay";
        $data['totalItem'] = intval($totalItem);
        $data['checkoutType'] = 2; // Thanh toán trả góp
        $data['buyerName'] = trim($params['name']);
        $data['buyerEmail'] = trim($params['email']);
        $data['buyerPhone'] = trim($params['phone']);
        $data['buyerAddress'] = trim($params['address']);
        $data['buyerCity'] = trim($params['city']);
        $data['buyerCountry'] = "Viet Nam";
        $data['paymentHours'] = 48; //48 tiếng :  Thời gian cho phép thanh toán (tính bằng giờ)
        $session = Mage::getSingleton("core/session", array("name" => "frontend"));
        $session->setData("customer_note", $params['customer_note']);
        $result = $alepay->sendOrderToAlepay($data);
        if (isset($result) && !empty($result->checkoutUrl)) {
            $alepay->return_json('OK', 'Thành công', $result->checkoutUrl);
        } else {
            $alepay->return_json($result->errorCode, $result->errorDescription);
        }
        $this->getResponse()->setBody($result);
    }


    public function alepay_returnAction()
    {
        $config = array(
            "apiKey" => (string)Mage::getConfig()->getNode('global/alepay/apiKey'),
            "encryptKey" => (string)Mage::getConfig()->getNode('global/alepay/encryptKey'),
            "checksumKey" => (string)Mage::getConfig()->getNode('global/alepay/checksumKey'),
            "callbackUrl" => (string)Mage::getConfig()->getNode('global/alepay/callbackUrl'),
            "env" => (string)Mage::getConfig()->getNode('global/alepay/env'),
        );
        $encryptKey = $config['encryptKey'];
        if (isset($_REQUEST['data']) && isset($_REQUEST['checksum'])) {
            $alepay = new Alepay($config);
            $utils = new AlepayUtils();
            $result = $utils->decryptCallbackData($_REQUEST['data'], $encryptKey);
            $obj_data = json_decode($result);

            try {
                $info = json_decode($alepay->getTransactionInfo($obj_data->data), true);
            } catch (Exception $e) {
                var_dump($e->getTraceAsString());
                die;
            }
            $transactionCode = $info["transactionCode"];
            $orderInstalment = Mage::getModel('ved_instalment/order_instalment')
                ->getCollection()
                ->addFieldToFilter('transaction_id', $transactionCode)
                ->getFirstItem();
            // Checking for duplicating transaction Code
            if ($orderInstalment) {
                //Check expire Ajax
                if ($this->_expireAjax()) {
                    return;
                }
                //Create New Order
                $oncepage = Mage::getSingleton('checkout/type_onepage');
                $oncepage->initCheckout();
                $session = Mage::getSingleton("core/session", array("name" => "frontend"));
                $customer_note = $session->getData("customer_note");
                $email = $info["buyerEmail"];
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    //Set checkout method
                    $oncepage->saveCheckoutMethod('register');
                } else {
                    $oncepage->getQuote()->setCustomerId(null)
                        ->setCustomerEmail($email)
                        ->setCustomerFirstname($oncepage->getQuote()->getShippingAddress()->getFirstname())
                        ->setCustomerIsGuest(true)
                        ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                    //Set checkout method
                    $oncepage->saveCheckoutMethod('METHOD_GUEST');
                }
                $message = array();
                //Set shipping method
                $shipping_method = 'tablerate_bestway';
                //Set shipping_method to address
                $shipping_address = $oncepage->getQuote()->getShippingAddress();
                $shipping_address->addData(array('shipping_method' => $shipping_method));
                $shipping_address->setEmail($email);
                $oncepage->getQuote()->setShippingAddress($shipping_address);
                $oncepage->getQuote()->getShippingAddress()->implodeStreetAddress();
                $oncepage->getQuote()->getShippingAddress()->setCollectShippingRates(true);

                $result = $oncepage->saveShippingMethod($shipping_method);
                if (!$result) {
                    $oncepage->getQuote()->collectTotals();
                }
                $oncepage->getQuote()->collectTotals()->save();
//                echo "<pre>";
                /**
                 * @var Mage_Checkout_Model_Type_Onepage $oncepage
                 */
                //Update Payment Method
                try {
                    $oncepage->savePayment(array('method' => 'instalmentorder'));
                } catch (Exception $e) {
                    var_dump('savePayment');
                    var_dump($e);
                    die;
                }
                try {
                    $oncepage->getQuote()->save();
                } catch (Exception $ex) {
                    $message['saveQuote'] = 'Error 2';
                    echo json_encode($message);
                    die();
                }
                try {
                    $oncepage->saveOrder();
                    $order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
                    $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
                    $order->setCustomerNote($customer_note);
                    $order->setAffiliateCode($oncepage->getQuote()->getAffiliateCode());
                    $order->setIsInstalment(true);
                    $order->setStatus('wait_instalment_confirm');
                    $order->save();
                } catch (Exception $ex) {
                    print "Save order error";
                    var_dump($ex);
                    die();
                }
                try {
                    $oncepage->getQuote()->save();
                } catch (Exception $ex) {
                    $message['message'] = 'Error 2';
                    echo json_encode($message);
                    die();
                }
                $description =
                    "<ul>" .
                    '<li>' . 'SĐT: ' . $info['buyerPhone'] . '</li>' .
                    '<li>' . 'Tên người mua:' . $info['buyerName'] . '</li>' .
                    '<li>' . 'Tháng: ' . $info['month'] . '</li>'
                    . '</ul>';
                // Save Order_Instalment
                $newOrderInstalment = array(
                    'instalment_id' => 1,
                    'order_id' => $order->getId(),
                    'transaction_id' => $transactionCode,
                    'provider' => 'Alepay',
                    'amount' => $info["amount"],
                    'prepaid_amount' => 0,
                    'status' => 0,
                    'fee' => 0,
                    'description' => ($description)
                );
                try {
                    Mage::getModel('ved_instalment/order_instalment')->setData($newOrderInstalment)->save();
                } catch (Exception $ee) {
                    var_dump($ee->getTraceAsString());
                    die;
                }
                //Redirect to success website
                $this->_redirect('checkout/payment/success');
            } else {
                $this->_redirect('checkout/payment/index');
            }
        }
    }

    public function alepay_webhookAction()
    {
        try {
            if (!$this->getRequest()->isPost())
                throw new Exception('Method not allow');
            $data = json_decode($this->getRequest()->getRawBody(), true);
            if (!is_array($data))
                throw new Exception('Data fail');
            $input = $data['transactionInfo'];
            if ($input['status'] == '155')
                throw new Exception('Order is processing');
            $installmentOrder = Mage::getModel('ved_instalment/order_instalment')
                ->getCollection()
                ->addFieldToFilter('status', false)
                ->addFieldToFilter('transaction_id', $input['transactionCode'])
                ->getFirstItem();
            $orderId = $installmentOrder->getOrderId();
            /**
             * @var Mage_Sales_Model_Order $order
             */
            $order = Mage::getModel('sales/order')->load($orderId);
            if (!$order->getId()) throw new Exception('Order load fail');
            if ($input['status'] == "000") {
                $order->setStatus('pending');
                $order->addStatusHistoryComment('Instalment', 'Pending');
                $installmentOrder->setData('status', 1);
            } else {
                $order->setStatus('canceled');
                $order->addStatusHistoryComment('Instalment', 'canceled');
                $installmentOrder->setData('status', 2);
            }
            $order->save();
            $installmentOrder->save();
            $message = ['status' => 'success'];
        } catch (Exception $e) {
            $message = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        $this->getResponse()->setBody(json_encode($message));
    }

    public function calculateInstalmentAction()
    {
        $params = $this->getRequest()->getParams();
        $currentGT = $params['grandTotal'];
        $prePaid = intval($params['prePaid'] * $currentGT / 10);
        $prePaid = floor($prePaid / 1000) * 1000;
        // Check conflict grandTotal
        $grandTotal = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();

        if ($currentGT == $grandTotal) {
            switch ($params['requireDocType']) {
                case '6':
                    $delayPerMonthPercent = 0.0139;
                    break;
                case '7':
                    $delayPerMonthPercent = 0.0139;
                    break;
                case '8':
                    $delayPerMonthPercent = 0.0139;
                    break;
                default:
                    $delayPerMonthPercent = 0.0166;
                    break;
            }
            if ($delayPerMonthPercent == 0.0166 && ($prePaid >= $currentGT * 0.4)) {
                $delayPerMonthPercent = 0.0159;
            }
            $terms = [6, 9, 12, 15, 18, 24]; // Ky han
            // Divide to array for fitting data table
            $totalAmountArray = array();
            $amountPerMonthArray = array();
            $firstMonthAmountArray = array();
            $radioArray = array();
            $remainingAmount = $grandTotal - $prePaid;

            foreach ($terms as $term) {
                $totalAmount = floor($remainingAmount * (1 + $term * $delayPerMonthPercent) / 1000) * 1000;
                $amountPerMonth = floor(($totalAmount / ($term)) / 1000) * 1000;
                $firstMonthAmount = $totalAmount - $amountPerMonth * ($term - 1);
                $radio = '<input type="radio" id="r' . $term . '" value="' . $term . '" name="inputTerm">';
                if ($amountPerMonth < 300000 || $firstMonthAmount < 300000) {
                    $amountPerMonth = "Không hỗ trợ";
                    $firstMonthAmount = "Không hỗ trợ";
                    $radio = "";
                }
                if ($delayPerMonthPercent == 0.0139 && ($term == 6)) {
                    $amountPerMonth = "Không hỗ trợ";
                    $firstMonthAmount = "Không hỗ trợ";
                    $radio = "";
                }
                array_push($totalAmountArray, $totalAmount);
                array_push($firstMonthAmountArray, $firstMonthAmount);
                array_push($amountPerMonthArray, $amountPerMonth);
                array_push($radioArray, $radio);
            }
            $this->getResponse()->setBody((json_encode(array(
                'totalAmount' => $totalAmountArray,
                'amountPerMonth' => $amountPerMonthArray,
                'firstMonthAmount' => $firstMonthAmountArray,
                'radioArray' => $radioArray
            ))));
        } else {
            // Current amount and cart amount is not same
            echo json_encode(array(
                'error' => 'Giỏ hàng cần được kiểm tra lại',
                'redirect' => Mage::getUrl('checkout/payment/success')
            ));
        }
    }

    public function acsInstalmentAction()
    {
        $params = $this->getRequest()->getParams();
        $data = json_decode(urldecode(base64_decode($params['data'])), true);
        // check current price with push_price

        if ($this->_expireAjax()) {
            return;
        }
        $grandTotal = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
        if ($data['doc'] == 6 && ($data['term'] == 6 || $data['term'] == 24)) {
            echo json_encode(array('redirect' => Mage::getUrl('checkout/cart')));
        }
        if ($data['grandtotal'] == $grandTotal) {
            //Create New Order
            $oncepage = Mage::getSingleton('checkout/type_onepage');
            $oncepage->initCheckout();
            $customer_note = $data['customer_note'];
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                //Set checkout method
                $oncepage->saveCheckoutMethod('register');
            } else {
                $oncepage->getQuote()->setCustomerId(null)
                    ->setCustomerEmail($data['email'])
                    ->setCustomerFirstname($oncepage->getQuote()->getShippingAddress()->getFirstname())
                    ->setCustomerIsGuest(true)
                    ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                //Set checkout method
                $oncepage->saveCheckoutMethod('METHOD_GUEST');
            }
            $message = array();
            //Set shipping method
            $shipping_method = 'tablerate_bestway';
            //Set shipping_method to address
            $shipping_address = $oncepage->getQuote()->getShippingAddress();
            $shipping_address->addData(array('shipping_method' => $shipping_method));
            $shipping_address->setEmail($data['email']);
            $oncepage->getQuote()->setShippingAddress($shipping_address);
            $oncepage->getQuote()->getShippingAddress()->implodeStreetAddress();
            $oncepage->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $result = $oncepage->saveShippingMethod($shipping_method);
            if (!$result) {
                $oncepage->getQuote()->collectTotals();
            }
            $oncepage->getQuote()->collectTotals()->save();
            try {
                $oncepage->savePayment(array('method' => 'instalmentorder'));
            } catch (Exception $e) {
                echo json_encode(array('error' => $e->getTraceAsString()));
                die;
            }
            try {
                $oncepage->getQuote()->save();
            } catch (Exception $ex) {
                echo json_encode(array('error' => $ex->getTraceAsString()));
                die;
            }
            try {
                $oncepage->saveOrder();
                $order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
                $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
                $order->setCustomerNote($customer_note);
                $order->setAffiliateCode($oncepage->getQuote()->getAffiliateCode());
                $order->setIsInstalment(true);
                $order->setStatus('wait_instalment_confirm');
                $order->save();
            } catch (Exception $ex) {
                echo json_encode(array('error' => $ex->getTraceAsString()));
                die;
            }
            try {
                $oncepage->getQuote()->save();
            } catch (Exception $ex) {
                echo json_encode(array('error' => $ex->getTraceAsString()));
                die;
            }
            $prePaid = ($data["prePaid"] * $data["grandtotal"]) / 10;
            switch ($data['doc']) {
                case '6':
                    $delayPerMonthPercent = 0.0139;
                    break;
                case '7':
                    $delayPerMonthPercent = 0.0139;
                    break;
                case '8':
                    $delayPerMonthPercent = 0.0139;
                    break;
                default:
                    $delayPerMonthPercent = 0.0166;
                    break;
            }
            $fee = 300000;
            if ($delayPerMonthPercent == 0.0166) {
                $fee = 400000;
            }
            if ($data['doc'] == 4 && ($prePaid >= 4)) {
                $delayPerMonthPercent = 0.0159;
            }
            $newOrderInstalment = array(
                'instalment_id' => 2,
                'order_id' => $order->getId(),
                'provider' => 'ACS',
                'amount' => $data['grandtotal'],
                'status' => 0,
                'fee' => $fee,
                'prepaid_amount' => $prePaid,
                'customer_name' => $data['name'],
                'customer_id' => $data['cmnd'],
                'customer_telephone' => $data['telephone'],
                'term' => $data['term'],
                'delay_fee' => $delayPerMonthPercent * 100,
            );
            try {
                Mage::getModel('ved_instalment/order_instalment')->setData($newOrderInstalment)->save();
            } catch (Exception $ee) {
                echo json_encode(array('error' => $ee->getTraceAsString()));
                die;
            }
            //Redirect to success website
            echo json_encode(array('redirect' => Mage::getUrl('checkout/payment/success')));
        } else {
            echo json_encode(array('redirect' => Mage::getUrl('checkout/cart')));
        }
    }


    public function getCartInfo()
    {
        $cartQty = Mage::helper('checkout/cart')->getSummaryCount();

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $shipping_address = $quote->getShippingAddress();

        $shipping_amount = -1;
        if (isset($shipping_address)) {
            $samount = $shipping_address->getShippingAmount();
            if (isset($samount) && !empty($samount) && ($samount > 0)) {
                $shipping_amount = $samount;
            }
        }

        $quoteData = $quote->getData();
        $totalDiscount = 0;


        $grandTotal = 0;
        $products = array();
        $cartItems = $quote->getAllVisibleItems();
        foreach ($cartItems as $item) {
            $productid = $item->getProductId();
            $grandTotal += $item->getPriceInclTax() * $item->getQty();
            $totalDiscount += $item->getDiscountAmount();
            $product = Mage::getModel('catalog/product')->load($productid);

            $productMediaConfig = Mage::getModel('catalog/product_media_config');
            $thumbnailUrl = $productMediaConfig->getMediaUrl($product->getThumbnail());
            $p = array(
                'id' => $productid,
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'quantity' => $item->getQty(),
                'price' => $item->getPrice(),
                'grand_total' => $item->getPriceInclTax() * $item->getQty(),
                'url' => $product->getProductUrl(),
                'image' => $thumbnailUrl,
                'old_price' => $product->getPrice(),
                'new_price' => $product->getFinalPrice()
            );
            $products[] = $p;
        }

        if ($shipping_amount == -1) {
            $cod = $grandTotal - $totalDiscount;
        } else {
            $cod = $grandTotal - $totalDiscount + $shipping_amount;
        }

        return array(
            'cart_qty' => $cartQty,
            'products' => $products,
            'total_discount' => $totalDiscount,
            'grand_total' => $grandTotal,
            'shipping_amount' => $shipping_amount,
            'cod' => $cod
        );
    }
}