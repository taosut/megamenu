<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/7/2016
 * Time: 4:46 PM
 */
class Ved_Checkout_Model_Resource_Checkout extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("ved_checkout/quote", "entity_id");
    }
}