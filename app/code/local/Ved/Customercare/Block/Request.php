<?php

/**
 * Created by PhpStorm.
 * User: Linh
 * Date: 3/13/2017
 * Time: 5:43 PM
 */
class Ved_Customercare_Block_Request extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Customer Care Request');
        $this->setTemplate('customercare/request.phtml');
    }

    /**
     * @return array
     */
    public function getForm()
    {
        $form = Mage::getSingleton('core/session');

        return $form->getData('form');
    }

    public function resetFrom()
    {
        Mage::getSingleton('core/session')->setData('form', array());
    }
}