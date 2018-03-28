<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/20/2016
 * Time: 2:08 PM
 */
require_once str_replace('\\', '/', dirname(__FILE__)) . '/../abstract.php';
class Ved_Assign_Group extends Mage_Shell_Abstract{
    public function run()
    {
        $this->assignGroup();
    }
    public function assignGroup(){
			
				$customers = array(1519707,1422158,1448077,1429220,1304942,1321173,1403921,1509111,1481032,1482293,1507509,
				1434346,1413892,1474746,1456422,1300250,1420307,1478441,1427329,1422080,1431468,1457735,1524797,1390032,1413576,
				1522351,1495956,1483638,1533571,1393033,1402556,1420335,1491915,1476355,1437298,1499568,1446063,1514446,1450450,
				1476197,1483799,1530330,1461485,1421526,1479190,1452599,1515866,1475924,1497737,1529973,1522800,1526373,1450699,
				1428390,1482364,1369703,1475169,1409982,1519257,1431937,1476454,1499025,1308159,1470551,1485477,1517302,1442091,
				1476452,1435625,1391789,1320548,1398540,1342223,1399082,1460885,1456138,1499013,1468409,1482692,1321465,1400512,
				1477195,1408419,1400215,1462770,1461835,
				1439359,1529539,1530344,1432014,1470584,1523651,1320434,1529089,1519276,1523245,1487978,1524107,1483089,1497815);
        print "Load all customers.\n";
				$update = 0;
				$non_update = 0;
        // we iterate through the list of products to get attribute values
        foreach ($customers as $customerId) {
					try{
            $customer = Mage::getModel('customer/customer')->load($customerId);
						$customer->setData('group_id', 4); // or whatever the group id should be
            $customer->save();
						$update++;
					}catch (Exception $e){
						$non_update++;
					}
        }
				print "update: " . $update . "\n";
				print "non update: " . $non_update . "\n";
    }
    
}

$shell = new Ved_Assign_Group();
$shell->run();