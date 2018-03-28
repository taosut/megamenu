<?php
/**
 * @author hoangpt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Helper_Data extends Mage_Core_Helper_Abstract
{
    const TYPE_THIS    = 1;

    public function getBoolOptions()
    {
		return array(
			1 => Mage::helper('cms')->__('Yes'),
			0 => Mage::helper('cms')->__('No')
		);
    }

    public function getUrlPath($url)
    {
        $store = Mage::app()->getStore();
        $storeCode = preg_quote($store->getCode());
        $base = str_replace('http://', '', $store->getBaseUrl());
        $res = str_replace($base, '', $url);

        return preg_replace("/^$storeCode\//", '', trim($res));
    }
}