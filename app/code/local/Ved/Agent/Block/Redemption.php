<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/12/2017
 * Time: 1:53 PM
 */

class Ved_Agent_Block_Redemption extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Tekshop Agent Redemption');
        $this->setTemplate('agent/redemption.phtml');
    }
}
