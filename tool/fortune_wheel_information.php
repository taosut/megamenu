
<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 1/15/2018
 * Time: 11:49 AM
 */
require_once '../app/Mage.php';

Mage::app()->setCurrentStore('admin');
$writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

$rule_array = array(
    array('VQPV_20000', 2000, 4.9),
    array('VQPV_50000', 1000, 40),
    array('VQPV_100000', 500, 30),
    array('VQPV_200000', 100, 5),
    array('VQPV_300000', 50, 5),
    array('VQPV_500000', 10, 5),
    array('VQPV_2000000', 5, 0.1)
);

try {
    $writeConnection->beginTransaction();
    foreach ($rule_array as $rule_info) {
        $rule_name = $rule_info[0];
        $count = $rule_info[1];
        echo "Processing rule_name : " . $rule_name . "\n";
        $rule = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('name', array('eq' => $rule_name))
            ->getFirstItem();

        if ($rule->getId()) {
            $rule_id = $rule->getId();
            $query = "INSERT INTO wheel_info_salesrule (`rule_id`, `rule_name`, `percent`, `count`) VALUES ($rule_id, '$rule_name', $rule_info[2], $count)";
            $writeConnection->query($query);
            echo "Done save information for rule_id: " . $rule->getId() . " with ". $count ." coupons\n";
        }
    }
    $writeConnection->commit();
}
catch (Exception $e) {
    $writeConnection->rollback();
    var_dump($e->getMessage());
}
