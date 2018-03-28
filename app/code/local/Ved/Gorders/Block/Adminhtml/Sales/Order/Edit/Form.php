<?php

class Ved_Gorders_Block_Adminhtml_Sales_Order_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/affiliateSave', array('order_id' => $this->getRequest()->getParam('order_id'))),
            'method' => 'post'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        $fieldset = $form->addFieldset('affiliate_form', array('legend' => 'Order Affiliate Information'));

        $fieldset->addField('affiliate', 'text', array(
            'label' => 'Order Affiliate',
            'name'  => 'affiliate',
            'required' => false,
            'value' => $order->getAffiliateCode(),
        ));


        return parent::_prepareForm();
    }
}
