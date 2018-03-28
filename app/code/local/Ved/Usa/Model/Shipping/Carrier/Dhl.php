<?php

class Ved_Usa_Model_Shipping_Carrier_Dhl extends Mage_Usa_Model_Shipping_Carrier_Dhl
{
    public function isTrackingAvailable()
    {
        return false;
    }
}