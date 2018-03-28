<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 12/1/16
 * Time: 11:50
 */
class Ved_Purchase_Model_Resource_Stocktransfer extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("ved_purchase/stocktransfer", "id");
    }
}