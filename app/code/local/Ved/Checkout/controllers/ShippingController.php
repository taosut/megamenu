<?php

class Ved_Checkout_ShippingController extends Mage_Checkout_Controller_Action
{
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Checkout page
     */
    public function indexAction()
    {

        if (!Mage::helper('checkout')->canOnepageCheckout()) {

            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();

//        if ((!$quote->hasItems()) ||  (!$quote->getHasError())) {
//            $this->_redirect('checkout/cart');
//            return;
//        }
        if ((!$quote->hasItems())) {
            $this->_redirect('checkout/cart');
            return;
        }

        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
        $this->getOnepage()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Shipping'));
        $this->renderLayout();
    }

    public function saveAddressAction()
    {
        $address_id = $this->getRequest()->getParam('id');
        if (isset($address_id)) {
            $customAddress = Mage::getModel('customer/address')->load($address_id);
            if ($customAddress->getId()) {
                $quote = Mage::getSingleton('checkout/session')->getQuote();

                if (isset($quote)) {
                    $quote_address = Mage::getSingleton('sales/quote_address')->importCustomerAddress($customAddress);
                    $quote->setBillingAddress($quote_address);
                    $quote->setShippingAddress($quote_address);

                    //Update Street address and shipping rate
                    $customAddress->implodeStreetAddress();
                    $customAddress->setCollectShippingRates(true);

                    //Save quote
                    try {
                        $quote->save();
                    } catch (Exception $ex) {
                        echo json_encode(array('message' => 'Error in save quote address'));
                        die();
                    }
                    $url = Mage::getUrl('checkout/payment/index');
                    //echo json_encode(array('message' => 'address_id:'.$address_id));
                    echo json_encode(array('redirect' => $url));
                    die();
                } else {
                    echo json_encode(array('message' => 'Not find quote'));
                    die();
                }

            } else {
                echo json_encode(array('message' => 'Not found customer address'));
                die();
            }
        } else {
            echo json_encode(array('message' => 'Not found address_id'));
            die();
        }
    }

    private function getCityName($city_id)
    {
        try {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $tableName = $resource->getTableName('directory_city');
            $query = "SELECT * FROM {$tableName} WHERE city_id = ?";
            $results = $readConnection->fetchAll($query, $city_id);

            if (count($results) > 0) {
                return $results[0]['name'];
            }
        } catch (Exception $e) {
            //do nothing
        }
        return null;
    }

    private function getCity($city_id)
    {
        try {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $tableName = $resource->getTableName('directory_city');
            $query = "SELECT * FROM {$tableName} WHERE city_id = ?";
            $results = $readConnection->fetchAll($query, $city_id);

            if (count($results) > 0) {
                return $results[0];
            }
        } catch (Exception $e) {
            //do nothing
        }
        return null;
    }

    public function getDistrictsInCityAction()
    {
        $cityid = intval($this->getRequest()->getParam('cityId'));

        $defaultDistrict = $this->getRequest()->getParam('defaultDistrict');

        if (isset($cityid)) {
            //Get all district of given city
            $districts = array();
            try {
                $resource = Mage::getSingleton('core/resource');
                $readConnection = $resource->getConnection('core_read');
                $tableName = $resource->getTableName('directory_city');
                $query = "SELECT * FROM {$tableName} WHERE region_id = ?";
                $results = $readConnection->fetchAll($query, $cityid);

                if (count($results) > 0) {
                    foreach ($results as $item) {
                        $districts = $districts + array($item['city_id'] => $item['name']);
                    }
                }
            } catch (Exception $e) {
                //do nothing
            }
            //Create option
            $options = '<option value="">Chọn Quận / Huyện</option>';
            if (count($districts) > 0) {
                foreach ($districts as $key => $val) {
                    $isSelected = $defaultDistrict == $val ? ' selected="selected"' : null;
                    $options .= '<option value="' . $key . '"' . $isSelected . '>' . $val . '</option>';
                }
            }
            echo $options;
            die();
        }
        echo '';
    }

    public function getDistrictNameByIdAction()
    {
        $cityid = intval($this->getRequest()->getParam('cityId'));

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('directory_city');
        $query = "SELECT * FROM {$tableName} WHERE city_id = ?";
        $results = $readConnection->fetchAll($query, $cityid);

        $result = array('district_name' => $results[0]['name']);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    public function saveShippingAction()
    {
        $request = $this->getRequest();
        $isVat = $request->has('is_vat');
        $full_name = strip_tags($this->getRequest()->getParam('full_name'));
        $telephone = strip_tags($this->getRequest()->getParam('telephone'));
        $email = strip_tags($this->getRequest()->getParam('email'));
        $country_id = $this->getRequest()->getParam('country_id');
        $region_id = $this->getRequest()->getParam('region_id');
        $city_id = $this->getRequest()->getParam('city_id');
        $street = strip_tags($this->getRequest()->getParam('street'));
        $city = $this->getCity($city_id);
        $cityName = '';
        $postCode = '';
        if ($city) {
            $cityName = $city['name'];
            $postCode = $city['code'];
        }
        $customer_notes = nl2br(htmlspecialchars($this->getRequest()->getParam('customer_note')));
        $affiliate_code = strip_tags($this->getRequest()->getParam('affiliate_code'));
        $address = Mage::getModel('customer/address');
        $address->firstname = $full_name;
        $address->country_id = $country_id;
        $address->email = $email;
        $address->street = $street;
        $address->postcode = $postCode;
        $address->city = $cityName;
        $address->region = '';
        $address->region_id = $region_id;
        $address->telephone = $telephone;
        $address->customer_notes = $customer_notes;
        $address->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1');

        if ($customer = Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $address->setCustomerId($customerData->getId());
        } else {
            $address->setCustomerAddressId(null);
        }

        try {
            $address->save();
        } catch (Exception $ex) {
            //Zend_Debug::dump($ex->getMessage());
        }
        /**
         * @var Mage_Sales_Model_Quote $quote
         */
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if (isset($quote)) {
            $quote_address = Mage::getSingleton('sales/quote_address')->importCustomerAddress($address);
            $quote_address->setCustomerNotes($address->customer_notes);

            $quote->setBillingAddress($quote_address);
            $quote->setShippingAddress($quote_address);
            if (!empty($affiliate_code)) {
                $quote->setAffiliateCode($affiliate_code);
            }
            if ($isVat)
                $quote->addData([
                    'is_vat' => true,
                    'vat_name' => strip_tags($request->get('vat_name')),
                    'vat_address' => strip_tags($request->get('vat_address')),
                    'vat_id' => strip_tags($request->get('vat_id')),
                    'vat_address_to' => strip_tags($request->get('vat_address_to'))
                ]);

            $address->implodeStreetAddress();
            $address->setCollectShippingRates(true);

            //Save quote
            try {
                $quote->save();
            } catch (Exception $ex) {
                echo json_encode(array('message' => 'Error in save quote address'));
                die();
            }
            $url = Mage::getUrl('checkout/payment/index');
            //echo json_encode(array('message' => 'address_id:'.$address_id));
            echo json_encode(array('redirect' => $url, 'shipping_address' => $address->getData()));
            die();
        } else {
            echo json_encode(array('message' => 'Not find quote'));
            die();
        }
    }

}
