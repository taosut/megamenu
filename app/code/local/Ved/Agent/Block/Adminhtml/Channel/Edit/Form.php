<?php

class Ved_Agent_Block_Adminhtml_Channel_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('agent_channel_form', array('legend' => Mage::helper('agent')->__('Channel Information')));

        $fieldset->addField('channel_name', 'text', array(
            'label' => Mage::helper('agent')->__('Channel Name'),
            'name' => 'channel_name',
            'required' => true,
        ));

        $fieldset->addField('channel_type', 'select', array(
            'label' => Mage::helper('agent')->__('Channel Type'),
            'name' => 'channel_type',
            'required' => true,
            'values' => array(
                1 => Mage::helper('agent')->__('Social Network'),
                2 => Mage::helper('agent')->__('Forum'))
        ));

        $fieldset->addField('channel_note', 'note', array(
            'label' => Mage::helper('agent')->__('Note'),
            'text' => 'Với hệ thống Diễn đàn/Website: Tên kênh chỉ nhập theo định dạng domain.xyz, không cần nhập http hay www; VÍ DỤ: tinhte.vn, tekshop.vn; KHÔNG NHẬP http://tinhte.vn hay http://www.tekshop.vn'
        ));

        if ($this->getRequest()->getParam('id')) {
            $fieldset->addField('created_user', 'label', array(
                'label' => Mage::helper('agent')->__('Created By'),
                'name' => 'created_by',
                'required' => true,
            ));

            $fieldset->addField('updated_user', 'label', array(
                'label' => Mage::helper('agent')->__('Updated By'),
                'name' => 'updated_by',
                'required' => true,
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

        }

        if (Mage::registry('channel_data')) {
            $form->setValues(Mage::registry('channel_data')->getData());
        }

        return parent::_prepareForm();
    }
}
