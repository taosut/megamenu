<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/22/2016
 * Time: 5:56 AM
 */
class Ved_Customer_Block_Sms_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form'); // this should be same as the form id define above
        $this->setTitle(Mage::helper('ved_customer')->__('SMS Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('sender', array(
            'label'     => Mage::helper('ved_customer')->__('Receiver'),
            'title'     => Mage::helper('ved_customer')->__('Receiver'),
            'content'   => $this->getLayout()->createBlock('ved_customer/sms_tab_receiver')->toHtml(),
        ));
        $this->addTab('content', array(
            'label'     => Mage::helper('ved_customer')->__('Content'),
            'title'     => Mage::helper('ved_customer')->__('Content'),
            'content'   => $this->getLayout()->createBlock('ved_customer/sms_tab_content')->toHtml(),
        ));
        $this->addTab('schedule', array(
            'label'     => Mage::helper('ved_customer')->__('Timetable'),
            'title'     => Mage::helper('ved_customer')->__('Timetable'),
            'content'   => $this->getLayout()->createBlock('ved_customer/sms_tab_timetable')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}