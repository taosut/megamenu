<?php

class Ved_Productqc_Block_Adminhtml_Catalog_Product_Attribute_Set_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function _construct()
    {
        parent::_construct();
    }

    protected function _prepareForm()
    {
        $session = Teko::getSession();
        /** @var Mage_Eav_Model_Entity_Attribute_Set $attributeSets $attributeSets */
        $attributeSets = Mage::getModel('eav/entity_attribute_set')->getCollection()
            ->addFieldToFilter('entity_type_id ', 4)
            ->setOrder('sort_order')
            ->load();
        $options = array_column($attributeSets->getData(), 'attribute_set_name', 'attribute_set_id');

        $products = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(['warehouse_sku', 'name'])
            ->addFieldToFilter('entity_id', ['in' => $session->getData('current_product_edit_ids')])
            ->load();
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/massEditSubmit', array('_current' => true)),
            'method' => 'post',
        ));

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('my_fieldset', array('legend' => 'Thay đổi Attribute cho sản phẩm'));
        foreach ($products as $key => $product) {
            $fieldset->addField('product_' . $product->getWarehouseSku(), 'label', array(
                'label' => $product->getWarehouseSku(),
                'value' => $product->getName(),
            ));

        }
        $fieldset->addField('attribute_set_id', 'select', array(
            'label' => 'Attribute',
            'name' => 'attribute_set_id',
            'required' => true,
            'options' => $options,
        ));

        return parent::_prepareForm();
    }
}