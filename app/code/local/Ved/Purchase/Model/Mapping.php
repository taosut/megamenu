<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 12/1/16
 * Time: 11:50
 */
class Ved_Purchase_Model_Mapping extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init("ved_purchase/mapping", "id");
    }
}