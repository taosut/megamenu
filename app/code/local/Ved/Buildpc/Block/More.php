<?php
/**
 * Created by PhpStorm.
 * User: Mei Ling
 * Date: 9/29/2017
 * Time: 10:20 AM
 */
class Ved_Buildpc_Block_More extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('buildpc/more_detail.phtml');
    }
}