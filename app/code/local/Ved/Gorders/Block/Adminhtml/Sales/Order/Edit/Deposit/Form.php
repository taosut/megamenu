<?php

class Ved_Gorders_Block_Adminhtml_Sales_Order_Edit_Deposit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/depositSave', array('order_id' => $this->getRequest()->getParam('order_id'))),
                'method' => 'post'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $orderId = $this->getRequest()->getParam('order_id');
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = Mage::getModel('sales/order')->load($orderId);

        $fieldset = $form->addFieldset('affiliate_form', array('legend' => 'Order Affiliate Information'));

        $fieldset->addField('deposit_amount', 'text', array(
            'label' => 'Order Deposit Amount',
            'name' => 'deposit_amount',
            'class' => 'validate-not-negative-number input-text required-entry',
            'required' => true,
            'value' => (int)$order->getDepositAmount(),
        ));
        $fieldset->addField('select', 'select', array(
            'label' => 'Deposit Method',
            'class' => 'required-entry',
            'required' => true,
            'value' => $order->getDepositMethod(),
            'name' => 'deposit_method',
            'onclick' => "",
            'onchange' => "",
            'values' => array_merge(['' => 'Please Select..'], $order->getDepositLabels()),
            'disabled' => false,
            'readonly' => false,
            'tabindex' => 1
        ));
        $fieldset->addField('deposit_description', 'textarea', array(
            'label' => 'Deposit Description',
            'name' => 'deposit_description',
            'required' => false,
            'value' => $order->getDepositDescription(),
        ));
        return parent::_prepareForm();
    }
}
