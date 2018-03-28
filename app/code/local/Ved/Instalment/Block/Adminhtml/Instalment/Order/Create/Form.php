<?php

class Ved_Instalment_Block_Adminhtml_Instalment_Order_Create_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('order_id' => $this->getRequest()->getParam('order_id'))),
                'method' => 'post'
            )
        );
        $orderInstalment = Mage::registry('orderInstalment');
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('instalment_form', array('legend' => 'Thông tin trả góp'));
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset->addField('status', 'select', array(
            'label' => 'Trạng thái',
            'class' => 'required-entry',
            'required' => false,
            'value' => $orderInstalment->getData('status'),
            'name' => 'status',
            'onclick' => "",
            'onchange' => "",
            'values' => [0 => 'Chờ xác nhận', 1 => 'Đã xác nhận', 2 => 'Hồ sơ bị từ chối'],
            'disabled' => false,
            'readonly' => false,
            'tabindex' => 1
        ));

        $fieldset->addField('transaction_id', 'text', array(
            'label' => 'Số hợp đồng',
            'class' => 'required-entry',
            'required' => true,
            'value' => $orderInstalment->getData('transaction_id'),
            'name' => 'transaction_id',
            'tabindex' => 1
        ));
        $fieldset->addField('prepaid_amount', 'hidden', array(
            'value' => (int)$orderInstalment->getData('prepaid_amount'),
            'name' => 'prepaid_amount',
            'tabindex' => 1
        ));
        $fieldset->addField('prepaid_amount_mask', 'text', array(
            'label' => 'Số tiền trả trước',
            'value' => (int)$orderInstalment->getData('prepaid_amount'),
            'name' => 'prepaid_amount_mask',
            'tabindex' => 1
        ));
        $fieldset->addField('prepaid_method', 'select', array(
            'label' => 'Phương thức trả trước',
            'value' => $orderInstalment->getData('prepaid_method'),
            'name' => 'prepaid_method',
            'onclick' => "",
            'onchange' => "",
            'values' => ([
                '' => 'Please Select..',
                'INSTALMENT_BANK_TRANSFER' => 'Chuyển khoản ngân hàng',
                'INSTALMENT_COD' => "Trả tiền mặt"
            ]),
            'disabled' => false,
            'readonly' => false,
            'tabindex' => 1
        ));
        $fieldset->addField('fee', 'hidden', array(
            'value' => (int)$orderInstalment->getData('fee'),
            'name' => 'fee',
            'tabindex' => 1
        ));
        $fieldset->addField('fee_mask', 'text', array(
            'label' => 'Phí hồ sơ',
            'value' => (int)$orderInstalment->getData('fee'),
            'name' => 'fee_mask',
            'tabindex' => 1
        ));
        $fieldset->addField('description', 'textarea', array(
            'label' => 'Mô tả',
            'value' => $orderInstalment->getData('description'),
            'name' => 'description',
            'tabindex' => 1
        ));
        $fieldset->addField('customer_name', 'label', array(
            'label' => "Tên khách hàng",
            'value' => $orderInstalment->getData('customer_name'),
        ));
        $fieldset->addField('customer_id', 'label', array(
            'label' => "CMND",
            'value' => $orderInstalment->getData('customer_id'),
        ));
        $fieldset->addField('customer_telephone', 'label', array(
            'label' => "Số điện thoại",
            'value' => $orderInstalment->getData('customer_telephone'),
        ));
        $fieldset->addField('term', 'label', array(
            'label' => "Kỳ hạn",
            'value' => $orderInstalment->getData('term'),
            'after_element_html' => ' <small>Tháng</small>',
        ));
        $fieldset->addField('label', 'label', array(
            'value' => "",
            'after_element_html' => ' <h5 class="accent">Lưu ý: Với đơn hàng khoản tiền khách còn phải trả góp <=20 triệu (Teko thu phí trả trước và đặt cọc) chỉ xác nhận khi khách đã đặt đủ tiền cọc và phí hồ sơ.</h5>',
        ));
        return parent::_prepareForm();
    }
}
