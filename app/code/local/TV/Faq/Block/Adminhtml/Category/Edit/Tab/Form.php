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
class TV_Faq_Block_Adminhtml_Category_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Preparation of current form
     *
     * @return TV_Faq_Block_Adminhtml_Category_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('faq_category');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('faq_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('tv_faq')->__('General information'),
            'class' => 'fieldset-wide'));

        if ($model->getCategoryId()) {
            $fieldset->addField('category_id', 'hidden', array(
                'name' => 'category_id'
            ));
        }

        $fieldset->addField('category_name', 'text', array(
            'name' => 'category_name',
            'label' => Mage::helper('tv_faq')->__('Category Name'),
            'title' => Mage::helper('tv_faq')->__('Category Name'),
            'required' => true,
        ));

        $fieldset->addField('category_image', 'radios', array(
            'name' => 'category_image',
            'label' => Mage::helper('tv_faq')->__('Category Image'),
            'title' => Mage::helper('tv_faq')->__('Category Image'),
            'onclick' => "",
            'onchange' => "",
            'value' => '1',
            'values' => $this->getImageIcons(),
            'disabled' => false,
            'readonly' => false,
            'tabindex' => 1
        ));

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

    function getImageIcons()
    {
        $images = array(
            'images/menu-icon/cau-hoi-hot.png',
            'images/menu-icon/donhang-thanhtoan.png',
            'images/menu-icon/giaonhanhang.png',
            'images/menu-icon/doitrabaohanh.png',
            'images/menu-icon/khuyenmai.png',
            'images/menu-icon/taikhoan.png',
            'images/menu-icon/thongtinchung.png',
        );
        $data = array();
        foreach ($images as $image) {
            $data[] = array(
                'value' => $image,
                'label' => '<img src="' . $this->getSkinUrl($image) . '"'
            );
        }
        return $data;
    }
}
