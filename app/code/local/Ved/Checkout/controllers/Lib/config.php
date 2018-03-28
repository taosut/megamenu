<?php

//Thông tin cấu hình
$config = array(
    "apiKey" => (string)Mage::getConfig()->getNode('global/alepay/apiKey'),
    "encryptKey" => (string)Mage::getConfig()->getNode('global/alepay/encryptKey'),
    "checksumKey" => (string)Mage::getConfig()->getNode('global/alepay/checksumKey'),
    "callbackUrl" => (string)Mage::getConfig()->getNode('global/alepay/callbackUrl'),
    "env" => (string)Mage::getConfig()->getNode('global/alepay/env'),
);
?>

