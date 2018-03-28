<?php
require_once  "app/Mage.php";
umask(0);
Mage::app();
$accessToken = $_GET['accessToken'];
$apiUrlSso = (string)Mage::getConfig()->getNode('global/sso_url') . 'validate_access_token';
$client = new Varien_Http_Client($apiUrlSso);
$client->setMethod(Varien_Http_Client::GET);
$client->setParameterGet('accessToken', $accessToken);
try {
    $response = $client->request();
    if ($response->isSuccessful()) {
        $response = json_decode($response->getBody());
        $user = $response;
        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('sso/sso'));
        $this->renderLayout();
    } else {
        var_dump("Có lỗi xảy ra, xin vui lòng thử lại sau !");
        die;
    }
} catch (Exception $e) {
    var_dump($e->getTraceAsString());
    die;
}
?>