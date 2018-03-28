<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/6/2016
 * Time: 2:57 PM
 */
class Ved_Checkout_Model_Checkout extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init("ved_checkout/quote");
    }
}
