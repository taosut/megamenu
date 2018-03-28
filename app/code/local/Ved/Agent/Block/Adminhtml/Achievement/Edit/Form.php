<?php

class Ved_Agent_Block_Adminhtml_Achievement_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('agent_achievement_form', array('legend' => 'Achievement Information'));

        $fieldset->addField('fullname', 'label', array(
            'label' => Mage::helper('agent')->__('Name'),
            'name' => 'fullname',
            'required' => true,
        ));

        $fieldset->addField('achievement_type_name', 'label', array(
            'label' => Mage::helper('agent')->__('Achievement Type'),
            'name' => 'achievement_type_name',
            'required' => true,
        ));

        $fieldset->addField('account_name', 'link', array(
            'label' => Mage::helper('agent')->__('Account'),
            'name' => 'account_name',
            'required' => true
        ));

        $fieldset->addField('link', 'textarea', array(
            'label' => Mage::helper('agent')->__('Link'),
            'name' => 'link',
            'required' => true,
        ));

        $fieldset->addField('status_label', 'label', array(
            'label' => Mage::helper('agent')->__('Status'),
            'name' => 'status_label',
            'required' => true,
        ));

        $fieldset->addField('status', 'hidden', array(
            'name' => 'status',
        ));

        $fieldset->addField('comment', 'textarea', array(
            'label' => Mage::helper('agent')->__('Admin Comment'),
            'name' => 'comment',
            'required' => false,
        ));

        $fieldset->addField('created_at', 'label', array(
            'label' => Mage::helper('agent')->__('Created At'),
            'name' => 'created_at',
            'required' => true,
        ));

        $fieldset->addField('updated_at', 'label', array(
            'label' => Mage::helper('agent')->__('Updated At'),
            'name' => 'updated_at',
            'required' => true,
        ));

        $fieldset->addField('updated_user', 'label', array(
            'label' => Mage::helper('agent')->__('Updated By'),
            'name' => 'updated_user',
            'required' => true,
        ));

        if (Mage::registry('achievement_data')) {
            $form->setValues(Mage::registry('achievement_data')->getData());
        }

        return parent::_prepareForm();
    }
}
