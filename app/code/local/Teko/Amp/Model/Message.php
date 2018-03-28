<?php

/**
 * Class Teko_Amp_Model_Message
 * @method string getRoutingKey()
 * @method string getMessage()
 */
class Teko_Amp_Model_Message extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('teko_amp/message');
    }
}
