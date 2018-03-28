<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 8/31/2017
 * Time: 5:31 PM
 */

class Ved_Favourite_Block_FavouriteMore extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('favourite/more.phtml');
    }
}