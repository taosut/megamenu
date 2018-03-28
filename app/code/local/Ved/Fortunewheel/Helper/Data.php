<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 12/27/2017
 * Time: 5:56 PM
 */
class Ved_Fortunewheel_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSaleRule()
    {
        $wheel_rules = Mage::getModel('Ved_Fortunewheel/wheelInfoSalesrule')->getCollection();
        $probabilities = array(0 => 100);
        foreach ($wheel_rules as $rule) {
            $probabilities[$rule->getRuleId()] = $rule->getPercent() * 10;
        }
        $random = array();
        foreach ($probabilities as $key => $value) {
            for ($i = 0; $i < $value; $i++) {
                $random[] = $key;
            }
        }
//        shuffle($random);
        return $random[mt_rand(0, 999)];
    }

    public function saveResult($customer_id, $rule_id, $coupon_id = null)
    {
        try {
            $couponUser = Mage::getModel('Ved_Fortunewheel/wheelDetail');
            $couponUser->setData(array(
                'customer_id' => $customer_id,
                'rule_id' => $rule_id,
                'coupon_id' => $coupon_id,
                'count' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ))->save();
            return $couponUser;
        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }
}