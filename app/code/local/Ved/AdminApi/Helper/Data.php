<?php

/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/5/2016
 * Time: 5:25 PM
 */
class Ved_AdminApi_Helper_Data extends Mage_Core_Helper_Abstract
{
    private function createSignature($time)
    {
        $callApiKey = Mage::getStoreConfig('adminapi/general/call_api_key');
        $constantDefault = Mage::getStoreConfig('adminapi/general/constant_default');

        return md5($time . $callApiKey . $constantDefault);
    }

    public function checkKeyAndSignature($clientKey, $clientSign, $time)
    {
        $signature = $this->createSignature($time);

        if ($clientKey == Mage::getStoreConfig('adminapi/general/call_api_key') && $clientSign == $signature) {
            return true;
        }

        return false;
    }

    public function checkTime($time)
    {
        if (time() - $time > 2 * 60) return false;
        return true;
    }

    private function createSignatureByCustomerId($time, $customerId)
    {
        $callApiKey = Mage::getStoreConfig('adminapi/general/call_api_key');
        $constantDefault = Mage::getStoreConfig('adminapi/general/constant_default');

        return md5($customerId . $time . $callApiKey . $constantDefault);
    }

    public function checkKeyAndSignatureByCustomerId($clientKey, $clientSign, $time, $customerId)
    {
        $signature = $this->createSignatureByCustomerId($time, $customerId);

        if ($clientKey == Mage::getStoreConfig('adminapi/general/call_api_key') && $clientSign == $signature) {
            return true;
        }

        return false;
    }

    public function validateMainDataInput($key, $sign, $time)
    {
        if (empty($key) || empty($sign) || empty($time))
            return false;

        return true;
    }

    /**
     * @param $code
     * @return Teko_Amp_Model_City
     * @throws Exception
     */
    public function getRegionFromCityCode($code = '')
    {
        /**
         * @var Teko_Amp_Model_City $city
         */
        $city = Mage::getModel('teko_amp/city')
            ->load($code, 'code');
        if (!$city->getId())
            throw  new Exception('Vui lòng cập nhật phiên bản mới', 111);
        return $city;
    }
}