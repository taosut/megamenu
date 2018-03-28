<?php

/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 10/19/2017
 * Time: 2:58 PM
 */
class Ved_Pos_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('pos/pos'));
        $this->renderLayout();
    }
}