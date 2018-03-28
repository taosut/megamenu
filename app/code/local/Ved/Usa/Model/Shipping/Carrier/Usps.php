<?php

class Ved_Usa_Model_Shipping_Carrier_Usps extends Mage_Usa_Model_Shipping_Carrier_Usps
{
    public function isTrackingAvailable()
    {
        return false;
    }
}