<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 3/18/2016
 * Time: 11:51 PM
 */
require_once(Mage::getModuleDir('controllers','Mage_Customer').DS.'AddressController.php');
class Ved_Customer_AddressController  extends Mage_Customer_AddressController{
    public function ajaxAddressInfoAction(){
        $id = $this->getRequest()->getParam('id');
        $address = array(
            'id'=>'',
            'full_name'=>'',
            'telephone'=>'',
            'region'=>'',
            'city'=>'',
            'email'=>'',
            'full_street'=>'',
            'billing'=>false
        );
        if(isset($id)){
            $addr = Mage::getModel('customer/address')->load($id);

//            var_dump($addr->getData());
            if(isset($address)){
                $address['id'] = $id;
                $address['full_name'] = trim($addr->getName());
                $address['telephone'] = trim($addr->getTelephone());
                $address['region'] = trim($addr->getRegion());
                $address['region_id'] = trim($addr->getRegionId());
                $address['email'] = trim($addr->getEmail());
                $address['city'] = trim($addr->getCity());
                $address['city_id'] = $addr->getCityId();
                $address['full_street'] = trim($addr->getStreetFull());
//                var_dump($address);
//                die();
            }
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if(isset($customer)){
                $default_addressId = $customer->getDefaultBilling();
                if(isset($default_addressId)){
                    if($default_addressId == $id){
                        $address['billing']  = true;
                    }
                }
            }
        }
        echo $this->getLayout()->createBlock('ved_customer/address')
            ->setData('address', $address)
            ->setTemplate('checkout/shipping/form.phtml')->toHtml();
    }
    public function ajaxEditAction(){
        $ret = array('message'=>array());
        $address_id = $this->getRequest()->getParam('address_id');
        $full_name = $this->getRequest()->getParam('full_name');
        $telephone = $this->getRequest()->getParam('telephone');
        $email = $this->getRequest()->getParam('email');
        $country_id = $this->getRequest()->getParam('country_id');
        $region_id = $this->getRequest()->getParam('region_id');
        $city_id = $this->getRequest()->getParam('city_id');
        $street = $this->getRequest()->getParam('street');
        $default = $this->getRequest()->getParam('default_shipping_address');

        $message = array();

        if(!isset($telephone) || empty($telephone) || !$this->isPhoneNumber($telephone)) {
            $message['telephone']="Xin bạn vui lòng nhập số điện thoại liên hệ.\n";
        }
//        if(!isset($email) || empty($email)) {
//            $message['email']="Xin bạn vui lòng nhập email.\n";
//        }
        if(!isset($full_name) || empty($full_name)) {
            $message['full_name']="Xin bạn vui lòng nhập họ tên.\n";
        }
        if(!isset($region_id) || empty($region_id)) {
            $message['region_id']="Xin bạn vui lòng chọn Tỉnh.\n";
        }
        if(!isset($city_id) || empty($city_id)) {
            $message['city_id']="Xin bạn vui lòng chọn Quận Huyện.\n";
        }
        if(count($message)>0){
            $ret['message']=$message;
            echo json_encode($ret);
            die();
        }
        $city = $this->getCityName($city_id);
        $_custom_address = array (
            'firstname' => $full_name,
            'lastname' => '',
            'street' => array (
                '0' => $street,
            ),
            'city' => $city,
//            'email' => $email,
            'region_id' => $region_id,
            'region' => '',
            'postcode' => '',
            'country_id' => $country_id,
            'telephone' => $telephone,
        );
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(isset($address_id) && !empty($address_id)){
            $customAddress = Mage::getModel('customer/address')->load($address_id);
            if(isset($default)){
                $customAddress->addData($_custom_address)
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
            }else{
                $customAddress->addData($_custom_address);
            }
            try {
                $customAddress->save();
                $message['save']= $customAddress->getId();
            }
            catch (Exception $ex) {
                var_dump($ex);
                die();
                $message['exception']="Lỗi khi lưu địa chỉ.\n";
                $ret['message']=$message;
            }
        }else{
            $customAddress = Mage::getModel('customer/address');
            if(isset($default)){
                $customAddress->setData($_custom_address)
                    ->setCustomerId($customer->getId())
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
            }else{
                $customAddress->setData($_custom_address)
                    ->setCustomerId($customer->getId());
            }

            try {
                $customAddress->save();
                $message['save'] = $customAddress->getId();
            }
            catch (Exception $ex) {
                $message['exception']="Lỗi khi lưu địa chỉ.\n";
                $ret['message']=$message;
            }

        }
       // $quote_address = Mage::getSingleton('sales/quote_address')->importCustomerAddress($customAddress);
       // Mage::getSingleton('checkout/session')->getQuote()->setBillingAddress($quote_address);
        $ret['message']=$message;
        echo json_encode($ret);
    }

    public function getDistrictsInCityAction()
    {
        $cityid= $this->getRequest()->getParam('cityId');
        $defaultDistrict= $this->getRequest()->getParam('defaultDistrict');

        if(isset($cityid)){
            //Get all district of given city
            $districts = array();
            try{
                $resource  =  Mage::getSingleton('core/resource');
                $readConnection =  $resource->getConnection('core_read');
                $tableName   = $resource->getTableName('directory_city');
                $query = "SELECT * FROM {$tableName} WHERE region_id ='{$cityid}'";
                $results =  $readConnection->fetchAll($query);

                if(count($results) > 0){
                    foreach ($results as $item) {
                        $districts = $districts + array($item['city_id']=>$item['name']);
                    }
                }
            }catch(Exception $e) {
                //do nothing
            }
            //Create option
            $options =  '';
            if(count($districts) > 0){
                foreach($districts as $key=>$val){
                    $isSelected = $defaultDistrict == $val ? ' selected="selected"' : null;
                    $options .= '<option value="' . $key . '"' . $isSelected . '>' . $val . '</option>';
                }
            }
            echo $options;
            die();
        }
        echo '';
    }
    public function ajaxDeleteAction(){
        $id = $this->getRequest()->getParam('id');
        if(isset($id)) {
            $addr = Mage::getModel('customer/address')->load($id);
            if (isset($addr)) {
                try{
                    $addr->delete();
                    echo "OK";;
                    die();
                }catch(Exception $ex){

                }
            }
        }
        echo "Error";
    }
    /****** Support Function **********/
    public function isPhoneNumber($phonenumber){
        return true;
    }
    public function getCityName($city_id){
        try{
            $resource  =  Mage::getSingleton('core/resource');
            $readConnection =  $resource->getConnection('core_read');
            $tableName   = $resource->getTableName('directory_city');
            $query = "SELECT * FROM {$tableName} WHERE city_id ='{$city_id}'";
            $results =  $readConnection->fetchAll($query);

            if(count($results) > 0){
                return $results[0]['name'];
            }
        }catch(Exception $e) {
            //do nothing
        }
        return null;
    }
}