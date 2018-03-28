<?php

class Ved_Search_Block_Resultpos extends  Mage_Catalog_Block_Product_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('search/result_pos.phtml');
    }
}