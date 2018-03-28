<?php
class Ved_Checkout_Block_Shipping_Item extends Mage_Core_Block_Template
{
    public function getShippingAddressInfo(){
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $address = $quote->getShippingAddress();
        return array(
            'id'=>$address->getId(),
            'name'=>$address->getName(),
            'full_address'=>$address->getStreetFull().', '.ucfirst(mb_strtolower($address->getCity(), 'UTF-8')).', '.$address->getRegion(),
            'country'=>$address->getCountry(),
            'phone'=>$address->getTelephone()
        );
    }
}