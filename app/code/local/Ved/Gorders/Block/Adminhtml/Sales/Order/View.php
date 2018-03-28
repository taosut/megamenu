<?php

class Ved_Gorders_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{
    protected function _isAllowedAction($action)
    {
        $order = $this->getOrder();
        if (Mage::helper('ved_gorders')->isPVEnv() && $order->getIsSendQueue()) return false;
        return parent::_isAllowedAction($action);
    }
}