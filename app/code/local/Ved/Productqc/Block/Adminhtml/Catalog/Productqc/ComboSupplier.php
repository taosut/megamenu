<?php

class Ved_Productqc_Block_Adminhtml_Catalog_Productqc_ComboSupplier extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'combo_supplier_edit_form', 'action' => '', 'method' => 'post'));

        $form->addField('content','text', array(
            'name'      => 'content',
            'value'     => $this->getData('content'),
            'required'  => true,
            'style'   => "width:130px",
            'force_load' => true,
        ))->setNoSpan(true);

        $form->addField('code','text', array(
            'name'      => 'content',
            'value'     => $this->getData('code'),
            'required'  => true,
            'style'   => "width:130px",
            'force_load' => true,
        ))->setNoSpan(true);

        $form->addField('button', 'button', array(
            'name'      => 'button',
            'value'     => 'Tìm mã SKU',
            'class'     => 'form-button  ok_button',
            'required'  => true,
            'force_load' => true,
            'onclick' => 'getSupplierSKU()'
        ))->setNoSpan(true);
        

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
