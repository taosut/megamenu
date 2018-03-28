<?php

class Ved_Usa_Model_Shipping_Carrier_Ups extends Mage_Usa_Model_Shipping_Carrier_Ups
{
    public function isTrackingAvailable()
    {
        return false;
    }
}