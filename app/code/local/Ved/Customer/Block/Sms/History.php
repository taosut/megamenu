<?php


class Ved_Customer_Block_Sms_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = "sms_history";

    $this->_blockGroup = "ved_customer";
    $this->_headerText = Mage::helper("ved_customer")->__("SMS Manager");
    $this->_addButtonLabel = Mage::helper("ved_customer")->__("Add New Sms");

    parent::__construct();
  }
}