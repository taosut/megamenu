<?php

/**
 * Create order instalment form
 *
 * @author      Magento Core Team Loz <core@magentocommerce.com>
 */
class Ved_Instalment_Block_Adminhtml_Instalment_Order_Instalment extends Mage_Adminhtml_Block_Abstract
{
    protected $_form;


    public function getHeaderCssClass()
    {
        return 'head-instalment';
    }

    public function getHeaderText()
    {
        return Mage::helper('sales')->__('Instalment Order');
    }


    public function getInstalmentData()
    {
        return $this->getFormValues();
    }

    public function getEditLink()
    {
        if ($this->getParentBlock()->getOrder()->getIsSendQueue() ||
            $this->getParentBlock()->getOrder()->getIsInstalment() != "1"
        )
            return "";
        if (empty($label)) {
            $label = $this->__('Edit');
        }
        $url = $this->getUrl('*/instalment_order/create', array('order_id' => $this->getOrder()->getId()));
        return '<a href="' . $url . '">' . $label . '</a>';
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return Ved_Instalment_Model_Order_Instalment
     */
    public function getOrderInstalment()
    {
        return Mage::getModel('ved_instalment/order_instalment')
            ->getCollection()
            ->addFieldToFilter('order_id', $this->getOrder()->getId())
            ->getFirstItem();
    }
}
