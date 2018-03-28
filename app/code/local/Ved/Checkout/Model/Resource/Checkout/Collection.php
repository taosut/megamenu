<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 11/30/2017
 * Time: 4:27 PM
 */
class Ved_Checkout_Model_Resource_Checkout_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
        $this->_init("ved_checkout/quote");
    }

}