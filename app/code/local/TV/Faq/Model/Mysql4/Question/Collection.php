<?php

class TV_Faq_Model_Mysql4_Question_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('tv_faq/question');
    }
}
