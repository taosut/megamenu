<?php

class Ved_Productqc_Block_Adminhtml_Catalog_Productqc_Warehouse extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'warehouse_edit_form', 'action' => '', 'method' => 'post'));

        $form->addField('content','text', array(
            'name'      => 'content',
            'value'     => $this->getData('content'),
            'required'  => true,
            'style'   => "width:130px",
            'force_load' => true,
        ))->setNoSpan(true);

        $form->addField('category_id', 'select', array(
            'name'      => 'category_id',
            'value'     => '0',
            'style'   => "width:180px",
            'values'     => (array('0' => 'Chọn Category') + $this->getData('categories')),
        ))->setNoSpan(true);

        $form->addField('manufacturer_id', 'select', array(
            'name'      => 'manufacturer_id',
            'value'     => '0',
            'style'   => "width:180px",
            'values'     => (array('0' => 'Chọn Manufacturer') + $this->getData('manufacturers')),
        ))->setNoSpan(true);

        $form->addField('button', 'button', array(
            'name'      => 'button',
            'value'     => 'Tìm mã SKU',
            'class'     => 'form-button  ok_button',
            'required'  => true,
            'force_load' => true,
            'onclick' => 'getSKU()'
        ))->setNoSpan(true);
        

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
