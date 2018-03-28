<?php

class CheckSumQueue
{

    function run()
    {
        /**
         * @var Teko_Amp_Model_Resource_Log_Collection $logCollection
         */
        $logCollection = Mage::getModel('teko_amp/log')->getCollection();
        $client = new Varien_Http_Client((string)Mage::getConfig()->getNode('global/url_check_sum_queue'));
        $response = $client->setMethod(Varien_Http_Client::POST);
        $timeZone = new DateTimeZone('Asia/Ho_Chi_Minh');
        $rawData = $logCollection->getDataCheckSum(new DateTime("yesterday", $timeZone), new DateTime("today", $timeZone));
        $response->setRawData($rawData);
        $response->setHeaders('content-type', 'application/json');
        $result = $response->request();
        Mage::log($result->getBody(), null, 'check_sum_message_queue');
        Mage::log($rawData, null, 'check_sum_message_queue');
    }

}