<?php

/**
 * Class Teko_Amp_Model_Mysql4_Queue
 */
class Teko_Amp_Model_Resource_Product extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {

        $this->_init('teko_amp/product', 'entity_id');
    }
}
