<?php

class Ved_Productqc_Block_Adminhtml_Catalog_Product_Attribute_Set extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_catalog_product_attribute_set';
        $this->_blockGroup = 'ved_productqc';
        $this->_mode = 'edit';
        parent::__construct();
        $this->_updateButton('save', 'label', Mage::helper('sales')->__('Chỉnh sửa Attribute'));
        $this->_removeButton('delete');
        $this->_removeButton('reset');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {

        return "";
    }

    /**
     * Back button url getter
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            '*/*/view',
            array('order_id' => $this->getRequest()->getParam('order_id'))
        );
    }
}