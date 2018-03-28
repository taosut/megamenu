<?php

class Ved_Agent_Block_Adminhtml_Info_Edit_General_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $this->setForm($form);

        $fieldset = $form->addFieldSet('agent_info_form', array('legend' => Mage::helper('agent')->__('Agent Information')));
        $fieldset->addField('firstname', 'label', array(
            'label' => Mage::helper('agent')->__('Name'),
            'name' => 'firstname',
            'required' => true,
        ));

        $fieldset->addField('phone_number', 'label', array(
            'label' => Mage::helper('agent')->__('Telephone'),
            'name' => 'phone_number',
            'required' => true,
        ));

        $fieldset->addField('email', 'text', array(
            'label' => Mage::helper('agent')->__('Email'),
            'name' => 'email',
            'required' => true,
            'class' => 'validate-email'
        ));

        $fieldset->addField('address', 'textarea', array(
            'label' => Mage::helper('agent')->__('Address'),
            'name' => 'address',
            'required' => true,
        ));

        $fieldset->addField('dob', 'date', array(
            'label' => Mage::helper('agent')->__('Date of Birth'),
            'name' => 'dob',
            'required' => true,
            'format' => 'dd/MM/yyyy',
            'class' => 'validate-date-au',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
        ));

        $fieldset->addField('gender', 'select', array(
            'label' => Mage::helper('agent')->__('Gender'),
            'name' => 'gender',
            'required' => true,
            'values' => array('Nam' => Mage::helper('agent')->__('Male'), 'Nữ' => Mage::helper('agent')->__('Female'))
        ));

        if (Mage::registry('agent_data')) {
            $form->setValues(Mage::registry('agent_data')->getData());
        }

        return parent::_prepareForm();
    }
}
