<?php
/**
 * FAQ accordion for Magento
 */

/**
 * FAQ accordion for Magento
 *
 * Website: www.abc.com
 * Email: honeyvishnoi@gmail.com
 */
class TV_Faq_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns config data
     *
     * @param string $field Requested field
     * @return array config Configuration information
     */
    public function getConfigData($field)
    {
        $path = 'faq/config/' . $field;
        $config = Mage::getStoreConfig($path, Mage::app()->getStore());
        return $config;
    }

    public function validateRequest(array  $rules, Mage_Core_Controller_Request_Http $request)
    {
        $data = [];
        foreach ($rules as $key => $rule) {
            if (!Zend_Validate::is($request->get($key), $rule['rule']))
                $data[$key]['error'] = $rule['message'];
            else
                $data[$key]['error'] = "";
            $data[$key]['value'] = $request->get($key, '');
        }
        return $data;
    }
}
