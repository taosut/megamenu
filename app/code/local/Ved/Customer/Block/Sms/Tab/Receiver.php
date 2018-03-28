<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/22/2016
 * Time: 5:58 AM
 */

class Ved_Customer_Block_Sms_Tab_Receiver extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $form = new Varien_Data_Form();
        $this->setForm($form);
        return $this;
    }
    protected function _prepareForm()
    {
        //Load save value
        $data = null;
        if (Mage::getSingleton("adminhtml/session")->getSmsmessageData()) {
            $data = Mage::getSingleton("adminhtml/session")->getSmsmessageData(); //Get data from session
            Mage::getSingleton("adminhtml/session")->getSmsmessageData(null); //Clear session
        } elseif (Mage::registry("current_smsmessage")) {
            $data = Mage::registry("current_smsmessage")->getData();  //Get data from registry
        }
        
        $fieldset = $this->getForm()->addFieldset('sms_receiver',
            array('legend'=>Mage::helper('ved_customer')->__('Receiver information')));

        $fieldset->addField('receiver', 'textarea', array(
            'label'     => Mage::helper('ved_customer')->__('Receiver'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'receiver',
        ));

        if ($data) {
            $this->getForm()->setValues($data);
        }
        return parent::_prepareForm();
    }
}