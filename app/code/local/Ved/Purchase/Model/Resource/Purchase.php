<?php

/**
 * Class Ved_Purchase_Model_Resource_Purchase
 * @method $this setStatus(int $int)
 * @method int getId()
 */
class Ved_Purchase_Model_Resource_Purchase extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("ved_purchase/purchase", "id");
    }
}