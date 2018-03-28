<?php
/**
 * Created by PhpStorm.
 * User: tranlinh
 * Date: 16/3/2016
 * Time: 10:19 AM
 */
class Ved_Gcafe_IndexController extends Mage_Core_Controller_Front_Action{
    private $secretKey = 'RvAcjZN2uptllenxeTCE';

    public function indexAction(){
        $this->loadLayout();

        $id = $this->getRequest()->getParam('cafeid');
        $random = $this->getRequest()->getParam('random');
        $token = $this->getRequest()->getParam('token');
        if(isset($id) && isset($random) && isset($token) && $id > 0 && $random <> '1'){
            $tokenGenerated = md5($id.$random.$this->secretKey);
            if($tokenGenerated == $token){
                $new_db_resource = Mage::getSingleton('core/resource');
                $connection = $new_db_resource->getConnection('gcafe_database');
                $query = 'select a.id, a.cafe_name, a.contact_name, a.contact_mobile, a.contact_phone,
                    a.authkey, a.cafe_phone, b.districtname, a.district_id, c.areaname, a.area_id, a.address, a.postcode
                     from cafe_cafe_tab a join gcmsbase_district_tab b on a.district_id = b.id
                    join gcmsbase_area_tab c on a.area_id = c.id
                    where a.id = ' . intval($id);
                $results    = $connection->fetchAll($query);

                if(isset($results[0])){
                    if(isset($results[0]['districtname']) && $results[0]['districtname']){
                        $regionModel = Mage::getModel('directory/region')->loadByName($results[0]['districtname'], 'VN');
                        $regionId = $regionModel->getId();
                        $regionCode = $regionModel->getCode();

                        $city = $this->getCity($regionId,$results[0]['areaname']);
                    }
                    if(intval($id) == 1434858){
                        $regionCode = 'hth';
                    }
                    else if(intval($id) == 1463451){
                        $regionCode = 'dni';
                    }
                    $store = Mage::app()->getStore();
                    if(isset($regionCode) && $store->getCode() == strtolower($regionCode)){
                        $customerData = Mage::getModel('customer/customer')->load($id);

                        //Get customer group
                        $targetGroup = Mage::getModel('customer/group');
                        $targetGroup->load($results[0]['districtname'], 'customer_group_code');

                        if(!($customerData && $customerData->getEntityId())){
                            //create new user and login
                            $websiteId = Mage::app()->getWebsite()->getId();
                            $customer = Mage::getModel("customer/customer");
                            $customer->setData(
                                array(
                                    'entity_id' => $id,
                                    'website_id' => $websiteId,
                                    'firstname' => $results[0]['contact_name'],
                                    'email' => $id.'@gcafeshop.vn',
                                    'password_hash' => md5($id),
                                )
                            );
							$customer->setCreatedAt(date('Y-m-d H:i:s', time()));
                            $customer->setGcafeRegion($results[0]['districtname']);
                            $customer->setStore($store);
                            if($targetGroup && $targetGroup->getId()){
                                $customer->setData('group_id', $targetGroup->getId());
                            }
                            try {
                                if($customer->save()){
                                    $address = Mage::getModel("customer/address");
                                    $address->setCustomerId($id);
                                    $address->firstname = $results[0]['contact_name'];
                                    $address->country_id = "VN";
                                    $address->street = $results[0]['address'];
                                    if($city){
                                        $address->postcode = $city['code'];
                                        $address->city = $city['name'];
                                    }
                                    $address->region = $results[0]['districtname'];
                                    $address->region_id = isset($regionId)?$regionId:null;
                                    $address->setIsDefaultBilling('1')
                                        ->setIsDefaultShipping('1')
                                        ->setSaveInAddressBook('1');
                                    $address->telephone = $results[0]['contact_mobile']? $results[0]['contact_mobile'] : ($results[0]['contact_phone']?$results[0]['contact_phone']:'');

                                    try {
                                        $address->save();
                                    }
                                    catch (Exception $ex) {
                                        //Zend_Debug::dump($ex->getMessage());
                                    }
                                }

                            }
                            catch (Exception $ex) {
                                $this->getResponse()->setBody(
                                    $this->getLayout()->createBlock(
                                        'Mage_Core_Block_Template',
                                        'gcafelogin',
                                        array('template'=>'gcafelogin/login_gcafe.phtml'))
                                        ->toHtml()
                                );
                            }


                        }else{
                            $needUpdate = false;
                            if($targetGroup && $targetGroup->getId()){
                                if($customerData->getGroupId() != $targetGroup->getId()) {
                                    $customerData->setData('group_id', $targetGroup->getId());

                                    $needUpdate = true;
                                }
                            }else{
                                $customerData->setData('group_id', 1);

                                $needUpdate = true;
                            }
                            
                            if($customerData->getStoreId() != $store->getStoreId()){
                                $websiteId = Mage::app()->getWebsite()->getId();
                                $customerData->setData('website_id', $websiteId);
                                $customerData->setData('store_id', $store->getStoreId());

                                $needUpdate = true;
                            }
                            if($needUpdate){
                                $customerData->save();
                            }
                        }
                        $this->loginUser($id.'@gcafeshop.vn',$id);

                        $this->_redirect('');
                    }elseif(isset($regionCode) && $store->getCode() != strtolower($regionCode)){
                        try{
                            $url = Mage::app(strtolower($regionCode))->getStore(strtolower($regionCode))->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
                            $url.='gcafelogin?cafeid='.$id.'&random='.$random.'&token='.$token;
                            $this->_redirectUrl($url);
                        }catch (Exception $ex){
                            $this->getResponse()->setBody(
                                $this->getLayout()->createBlock(
                                    'Mage_Core_Block_Template',
                                    'gcafelogin',
                                    array('template'=>'gcafelogin/login_gcafe.phtml'))
                                    ->toHtml()
                            );
                        }

                    }

                }
            }
        }

        //Khong ton tai tai khoan Gcafe, ko du tham so, tham so ko chinh xac
        // Redirect ve trang bao loi
        //$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'gcafelogin',
                array('template'=>'gcafelogin/login_gcafe.phtml'))
                ->toHtml()
        );
    }

    function loginUser( $email, $password ) {
        require_once ("app/Mage.php");
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
            $session->login( $email, $password );
            $session->setCustomerAsLoggedIn( $session->getCustomer() );


        }catch(Exception $e){

        }


    }

    private function getCity($regionId, $name){
        try{
            $resource  =  Mage::getSingleton('core/resource');
            $readConnection =  $resource->getConnection('core_read');
            $tableName   = $resource->getTableName('directory_city');
            $query = "SELECT * FROM {$tableName} WHERE region_id = ? and name like '%?'";
            $results =  $readConnection->fetchAll($query, $regionId, $name);

            if(count($results) > 0){
                return $results[0];
            }
        }catch(Exception $e) {
            //do nothing
        }
        return null;
    }
}

?>