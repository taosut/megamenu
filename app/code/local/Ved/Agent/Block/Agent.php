<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/6/2017
 * Time: 11:03 AM
 */

class Ved_Agent_Block_Agent extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Tekshop Agent');
        $this->setTemplate('agent/index.phtml');
    }
}