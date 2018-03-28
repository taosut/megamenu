<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 7/12/2017
 * Time: 6:55 PM
 */

class Ved_Buildpc_Block_Confirm extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Confirm Build PC');
        $this->setTemplate('buildpc/confirm.phtml');
    }
}