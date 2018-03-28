<?php

/**
 * Class Teko_Amp_Model_Resource_Log
 */
class Teko_Amp_Model_Resource_Log extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {

        $this->_init('teko_amp/log', 'id');
    }
}
