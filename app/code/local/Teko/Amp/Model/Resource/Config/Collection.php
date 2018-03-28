<?php

class Teko_Amp_Model_Resource_Config_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_previewFlag;

    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('teko_amp/config');
    }
}
