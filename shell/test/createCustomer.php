<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/20/2016
 * Time: 2:08 PM
 */
require_once str_replace('\\', '/', dirname(__FILE__)) . '/../abstract.php';
class Ved_Create_Customer  extends Mage_Shell_Abstract
{
    public function run()
    {
        $this->createCustomer();
    }
    public function createCustomer(){

        $customers = array(
            1519707,1422158,1448077,1429220,1304942,1321173,1403921,1509111,1481032,1482293,1507509,1434346,1413892,
            1474746,1456422,1300250,1420307,1478441,1427329,1422080,1431468,1457735,1524797,1390032,1413576,1522351,
            1495956,1483638,1533571,1393033,1402556,1420335,1491915,1476355,1437298,1499568,1446063,1514446,1450450,
            1476197,1483799,1530330,1461485,1421526,1479190,1452599,1515866,1475924,1497737,1529973,1522800,1526373,
            1450699,1428390,1482364,1369703,1475169,1409982,1519257,1431937,1476454,1499025,1308159,1470551,1485477,
            1517302,1442091,1476452,1435625,1391789,1320548,1398540,1342223,1399082,1460885,1456138,1499013,1468409,
            1482692,1321465,1400512,1477195,1408419,1400215,1462770,1461835,1439359,1529539,1530344,1432014,1470584,
            1523651,1320434,1529089,1519276,1523245,1487978,1524107,1483089,1497815
        );
        print "Start Create" . count($customers) . " Customer.\n";
        $new_db_resource = Mage::getSingleton('core/resource');
        $connection = $new_db_resource->getConnection('gcafe_database');
        $update = 0;
        $non_update_test = 0;
        $non_update = 0;
        $already = 0;
        foreach($customers as $id){

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
                }

                $store = Mage::getModel('core/store')->load(1);
                if(isset($regionCode) && $store->getCode() == strtolower($regionCode)){
                    $customerData = Mage::getModel('customer/customer')->load($id)->getData();
                    //var_dump($results[0]['districtname'],$customerData);
                    if(!isset($customerData['entity_id'])){
                        //create new user and login
                        $websiteId = Mage::app()->getWebsite()->getId();
                        //var_dump($websiteId, $store->getId());die();
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
                        $customer->setGcafeRegion($results[0]['districtname']);
                        $customer->setStore($store);
                        try {
                            if($customer->save()){
                                $address = Mage::getModel("customer/address");
                                $address->setCustomerId($id);
                                $address->firstname = $results[0]['contact_name'];
                                $address->country_id = "VN";
                                $address->street = $results[0]['address'];
                                $address->postcode = $results[0]['postcode'];
                                $address->city = $results[0]['areaname'];
                                $address->region = $results[0]['districtname'];
                                $address->region_id = isset($regionId)?$regionId:null;
                                $address->setIsDefaultBilling('1')
                                    ->setIsDefaultShipping('1')
                                    ->setSaveInAddressBook('1');
                                $address->telephone = $results[0]['contact_mobile']? $results[0]['contact_mobile'] : ($results[0]['contact_phone']?$results[0]['contact_phone']:'');
                                //$address->company = “MyCompany”;
                                //var_dump($address);die();

                                try {
                                    $address->save();
                                }
                                catch (Exception $ex) {
                                    var_dump($ex);
                                    $non_update++;
                                }
                            }

                        }
                        catch (Exception $ex) {
                            var_dump($ex);
                            $non_update++;
                        }

                        $update++;
                    }else{
                        $already++;
                    }
                }
            }else{
                $non_update_test++;
            }
        }

        print "Save " . $update ." customer.\n";
        print "Already had " . $already ." customer.\n";
        print "Not found " . $non_update_test ." customer.\n";
        print "Not save " . $non_update ." customer.\n";
        //}
    }

}

$shell = new Ved_Create_Customer();
$shell->run();