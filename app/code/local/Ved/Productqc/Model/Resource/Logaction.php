<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 12/1/16
 * Time: 11:50
 */
class Ved_Productqc_Model_Resource_Logaction extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("ved_productqc/logaction", "id");
    }
}