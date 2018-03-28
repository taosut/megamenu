<?php

class Teko_VAT_Block_Adminhtml_Sales_Order_Edit_Vat_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/vatSave', array('order_id' => $this->getRequest()->getParam('order_id'))),
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
        $fieldset = $form->addFieldset('vat_form', array('legend' => 'Order VAT Information'));
        $fieldset->addField('is_vat', 'select', array(
            'label' => 'Yêu cầu viết hóa đơn',
            'required' => true,
            'value' => $order->getData('is_vat'),
            'name' => 'is_vat',
            'values' => [0 => 'Không', 1 => "Có"],
        ));
        $fieldset->addField('vat_name', 'text', array(
            'label' => 'Tên công ty',
            'name' => 'vat_name',
            'required' => true,
            'value' => $order->getData('vat_name'),
        ));
        $fieldset->addField('vat_id', 'text', array(
            'label' => 'Mã số thuế',
            'name' => 'vat_id',
            'required' => true,
            'value' => $order->getData('vat_id'),
        ));
        $fieldset->addField('vat_address', 'textarea', array(
            'label' => 'Địa chỉ trên hóa đơn',
            'name' => 'vat_address',
            'required' => true,
            'value' => $order->getData('vat_address')
        ));
        $fieldset->addField('vat_address_to', 'textarea', array(
            'label' => 'Địa chỉ nhận hóa đơn',
            'name' => 'vat_address_to',
            'required' => true,
            'value' => $order->getData('vat_address_to')
        ));
        return parent::_prepareForm();
    }
}