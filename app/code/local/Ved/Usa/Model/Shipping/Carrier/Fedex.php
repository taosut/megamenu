<?php

class Ved_Usa_Model_Shipping_Carrier_Fedex extends Mage_Usa_Model_Shipping_Carrier_Fedex
{
    public function isTrackingAvailable()
    {
        return false;
    }
}