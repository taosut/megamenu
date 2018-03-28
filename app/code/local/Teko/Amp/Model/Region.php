<?php

/**
 * Class Teko_Amp_Model_Queue
 * @method string getDefaultName()
 * @method string getArea()
 * @method string getCode()
 * @method string getRegionId()
 */
class Teko_Amp_Model_Region extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('teko_amp/region');
    }
}
