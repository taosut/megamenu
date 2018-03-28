<?php

class TEKShop_PaymentOnline_Block_Checkout_Payment extends Mage_Page_Block_Html
{
    /**
     * @return TEKShop_PaymentOnline_Model_Resource_Bank_Collection
     */
    public function getBanks()
    {
        return Mage::getModel('TEKShop_PaymentOnline/bank')
            ->getCollection()
            ->load();
    }

    public function isEnableInstalment()
    {
        return !!Mage::getModel('core/variable')
            ->loadByCode('is_enable_instalment')
            ->getId();
    }

    public function isEnableOnline()
    {
        return !!Mage::getModel('core/variable')
            ->loadByCode('is_enable_online')
            ->getId();
    }
}
