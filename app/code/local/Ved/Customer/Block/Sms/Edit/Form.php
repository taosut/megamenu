<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/22/2016
 * Time: 5:55 AM
 */

class Ved_Customer_Block_Sms_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return $this;
    }
    protected function _prepareForm()
    {
        return parent::_prepareForm();
    }
}