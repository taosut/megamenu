<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/22/2016
 * Time: 5:53 AM
 */
class Ved_Customer_Block_Sms_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();


        $this->_objectId = 'sendsms';
        //$this->_blockGroup . ‘/’ . $this->_controller . ‘_’ . $this->_mode . ‘_form’}
        $this->_blockGroup = 'ved_customer';
        $this->_controller = 'sms';
        $this->_mode="edit";

//
//        $this->_updateButton('save', 'label', Mage::helper('ved_customer')->__('Save'));
//        $this->_updateButton('delete', 'label', Mage::helper('ved_customer')->__('Delete'));

//        $this->_addButton('saveandcontinue', array(
//            'label'     => Mage::helper('ved_customer')->__('Save And Continue Edit'),
//            'onclick'   => 'saveAndContinueEdit()',
//            'class'     => 'save',
//        ), -100);

//        $this->_formScripts[] = "";
    }

    public function getHeaderText()
    {
        return Mage::helper('ved_customer')->__('Send SMS');
    }
}