<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 7/12/2017
 * Time: 9:57 AM
 */

class Ved_Buildpc_Block_Buildpc extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('buildpc/index.phtml');
    }

    public function getCatUrl($catId) {
        return Mage::getModel('catalog/category')->load($catId)->getUrl();
    }
}