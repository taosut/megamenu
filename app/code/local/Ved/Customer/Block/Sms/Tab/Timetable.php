<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/22/2016
 * Time: 5:58 AM
 */

class Ved_Customer_Block_Sms_Tab_Timetable extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset = $this->getForm()->addFieldset('sms_timetable',
            array('legend'=>Mage::helper('ved_customer')->__('Timetable information')));

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $fieldset->addField('timetable', 'date',array(
            'name'      =>    'timetable', /* should match with your table column name where the data should be inserted */
            'time'      =>    true,
            'class'     =>    'required-entry',
            'required'  =>    false,
            'format'    =>    $this->escDates(),
            'label'     =>    Mage::helper('ved_customer')->__('Timetable:'),
            'image'     =>    $this->getSkinUrl('images/grid-cal.gif'),
            'value' => date("Y-m-d H:i:s",time()),
            'style'=>'width: 120px !important;'
        ));

        if ($data) {
            $this->getForm()->setValues($data);
        }

        return parent::_prepareForm();
    }
    private function escDates() {
        return 'yyyy-MM-dd HH:mm:ss';
    }
}