<?php

/**
 * Class Teko_Amp_Model_Mysql4_Message
 */
class Teko_Amp_Model_Resource_Message extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {

        $this->_init('teko_amp/message', 'id');
    }
}
