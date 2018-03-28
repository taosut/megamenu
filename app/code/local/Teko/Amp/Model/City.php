<?php

/**
 * Class Teko_Amp_Model_Queue
 * @method string getName()
 * @method string getRegionId()
 * @method Teko_Amp_Model_Region getRegion()
 * @method $this setRegion(Teko_Amp_Model_Region $region)
 * #
 */
class Teko_Amp_Model_City extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'teko_amp_city';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('teko_amp/city');
    }
}
