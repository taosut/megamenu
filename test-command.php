<?php
require_once 'vendor/autoload.php';
require_once 'app/Mage.php';
require_once 'app/cron/ProcessQueue.php';

Mage::app()->setCurrentStore('admin');
$response = [];
try {
    $properties = $_SERVER['HTTP_PROPERTIES'];
    $data = $HTTP_RAW_POST_DATA;
    $routing_key = $_GET['routing_key'];
    $queue = new ProcessQueue();
    $queue->runTest($data, $properties, $routing_key);
    $response = [
        'status' => "success",
        'message' => "",
    ];
} catch (Exception $e) {
    $response = [
        'status' => "error",
        'message' => $e->getMessage(),
    ];
}
header('Content-Type: json');
echo json_encode($response);
