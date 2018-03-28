<?php

/**
 * Edit order affiliate code form container block
 */
class Ved_Gorders_Block_Adminhtml_Sales_Order_Deposit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_sales_order';
        $this->_blockGroup = 'ved_gorders';
        $this->_mode = 'edit_deposit';
        parent::__construct();
        $this->_updateButton('save', 'label', Mage::helper('sales')->__('Save Order Deposit'));
        $this->_removeButton('delete');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $incrementId = $order->getIncrementId();

        return Mage::helper('sales')->__('Edit Order %s Deposit', $incrementId);
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
