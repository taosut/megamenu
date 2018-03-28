<?php
//echo "áds";
require_once('app/Mage.php');
require_once('vendor/autoload.php');
Mage::app()->setCurrentStore('admin');
$order = Mage::getModel('sales/order')->load($_REQUEST['id']);
/**
 * @var Mage_Sales_Model_Order $order
 */
try {
   echo  json_encode($order->getDataMessageQueue());
} catch (Exception $exception) {

}
