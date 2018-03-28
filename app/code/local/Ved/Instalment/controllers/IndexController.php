<?php
require('config.php');
require('Lib/Alepay.php');

class Ved_Instalment_IndexController extends Mage_Core_Controller_Front_Action
{


    public function pushAlepayAction()
    {
        $params =$this->getRequest()->getParams();
        $client = new Varien_Http_Client('');
        $client->setMethod(Varien_Http_Client::POST);
        // Set parameter
        $client->setParameterPost('name', 1);
        $client->setParameterPost('address', 1);

    }
    public function testAction()
    {

    }
}