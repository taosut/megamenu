<?php


class Ved_Compare_Block_Index extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('compare/index.phtml');
    }
}