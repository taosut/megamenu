<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 8/31/2017
 * Time: 5:31 PM
 */

class Ved_Sso_Block_Sso extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sso/index.phtml');
    }
}

