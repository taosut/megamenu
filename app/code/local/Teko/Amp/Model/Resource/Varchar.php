<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 5/11/2017
 * Time: 4:54 PM
 */
class Teko_Amp_Model_Resource_Varchar extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('teko_amp/varchar', 'value_id');
    }
}