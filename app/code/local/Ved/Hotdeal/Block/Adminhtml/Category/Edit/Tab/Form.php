<?php
/**
 * FAQ accordion for Magento
 */

/**
 * FAQ accordion for Magento
 *
 * Website: www.abc.com
 * Email: honeyvishnoi@gmail.com
 */
class Ved_Hotdeal_Block_Adminhtml_Category_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Preparation of current form
     *
     * @return Ved_Hotdeal_Block_Adminhtml_Category_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('hotdeal_category');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('faq_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('tv_faq')->__('General information'),
            'class' => 'fieldset-wide'));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id'
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('tv_faq')->__('Category Name'),
            'title' => Mage::helper('tv_faq')->__('Category Name'),
            'required' => true,
        ));

        $fieldset->addField('position', 'select',
            array(
                'label' => Mage::helper('cms')->__('Position'),
                'title' => Mage::helper('tv_faq')->__('Position'),
                'name' => 'position',
                'required' => true,
                'options' => array(
                    '1' => Mage::helper('cms')->__('Top'),
                    '2' => Mage::helper('cms')->__('Bottom'),
                    '3' => Mage::helper('cms')->__('Left'),
                    '4' => Mage::helper('cms')->__('Right'))));



        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect',
                array(
                    'name' => 'stores[]',
                    'label' => Mage::helper('cms')->__('Store view'),
                    'title' => Mage::helper('cms')->__('Store view'),
                    'required' => true,
                    'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)));
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId()));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('is_active', 'select',
            array(
                'label' => Mage::helper('cms')->__('Status'),
                'title' => Mage::helper('tv_faq')->__('Category Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => array(
                    '1' => Mage::helper('cms')->__('Enabled'),
                    '0' => Mage::helper('cms')->__('Disabled'))));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
