<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 7/12/2017
 * Time: 6:54 PM
 */

class Ved_Buildpc_ConfirmController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('buildpc/confirm'));
        $this->renderLayout();
    }
}