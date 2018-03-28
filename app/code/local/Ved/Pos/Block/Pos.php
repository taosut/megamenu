<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 10/19/2017
 * Time: 2:52 PM
 */

class Ved_Pos_Block_Pos extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Point Of Sale');
        $this->setTemplate('pos/index.phtml');
    }
}