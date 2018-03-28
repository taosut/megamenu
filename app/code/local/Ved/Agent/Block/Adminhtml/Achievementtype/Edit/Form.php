<?php

class Ved_Agent_Block_Adminhtml_Achievementtype_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('agent_achievement_type_form', array('legend' => Mage::helper('agent')->__('Achievement Type Information')));

        if ($this->getRequest()->getParam('id')) {
            $fieldset->addField('channel_id', 'hidden', array(
                'name' => 'channel_id',
            ));
            $fieldset->addField('channel_name', 'label', array(
                'label' => Mage::helper('agent')->__('Channel Name'),
                'name' => 'channel_name',
                'required' => true,
            ));
        } else {
            $channels = Mage::getModel('Ved_Agent/agentchannel')
                ->getCollection()
                ->addFieldToFilter('main_table.is_deleted', 0);
            $select = array();
            foreach ($channels as $channel) {
                $select[$channel->getChannelId()] = $channel->getChannelName();
            }

            $fieldset->addField('channel_id', 'select', array(
                'label' => Mage::helper('agent')->__('Channel Name'),
                'name' => 'channel_id',
                'required' => true,
                'values' => $select
            ));
        }

        $fieldset->addField('achievement_type_name', 'text', array(
            'label' => Mage::helper('agent')->__('Achievement Type Name'),
            'name' => 'achievement_type_name',
            'required' => true,
        ));

        $fieldset->addField('point', 'text', array(
            'label' => Mage::helper('agent')->__('Point'),
            'name' => 'point',
            'class' => 'validate-digits validate-greater-than-zero',
            'required' => true,
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

        if (Mage::registry('achievement_type_data')) {
            $form->setValues(Mage::registry('achievement_type_data')->getData());
        }

        return parent::_prepareForm();
    }
}
