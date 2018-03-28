<?php

class Ved_Usa_Model_Shipping_Carrier_Dhl_International extends Mage_Usa_Model_Shipping_Carrier_Dhl_International
{
    public function isTrackingAvailable()
    {
        return false;
    }
}