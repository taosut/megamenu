<?php

/**
 * Created by PhpStorm.
 * User: buivandung
 * Date: 03/11/2017
 * Time: 10:07
 */
class Teko_Amp_Model_Resource_Queue_Property extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('teko_amp/queue_property', 'id');
    }
}