<?php

class Ved_Agent_Block_Adminhtml_Gift_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('redemption_gift_form', array('legend' => Mage::helper('agent')->__('Redemption Gift Information')));

        if (!$this->getRequest()->getParam('id')) {
            $fieldset->addField('rule_id_show', 'label', array(
                'label' => Mage::helper('agent')->__('Rule Id'),
                'required' => true,
            ));
            $fieldset->addField('rule_name_show', 'label', array(
                'label' => Mage::helper('agent')->__('Rule Name'),
                'required' => true,
            ));
            $fieldset->addField('rule_id', 'hidden', array(
                'label' => Mage::helper('agent')->__('Rule Id'),
                'name' => 'rule_id',
                'required' => true,
            ));
        } else {
            $fieldset->addField('rule_id', 'hidden', array(
                'label' => Mage::helper('agent')->__('Rule Id'),
                'name' => 'rule_id',
                'required' => true,
            ));

            $fieldset->addField('rule_name', 'label', array(
                'label' => Mage::helper('agent')->__('Rule Name'),
                'name' => 'rule_name',
                'required' => true,
            ));
        }

        $fieldset->addField('redemption_gift_name', 'text', array(
            'label' => Mage::helper('agent')->__('Redemption Gift Name'),
            'name' => 'redemption_gift_name',
            'required' => true,
        ));

        $fieldset->addField('point', 'text', array(
            'label' => Mage::helper('agent')->__('Point to Trade'),
            'name' => 'point',
            'required' => true,
            'class' => 'validate-digits validate-greater-than-zero',
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

        if (Mage::registry('redemption_gift')) {
            $form->setValues(Mage::registry('redemption_gift')->getData());
        }

        return parent::_prepareForm();
    }
}
