<?php

/**
 * Created by PhpStorm.
 * User: buivandung
 * Date: 08/11/2017
 * Time: 15:30
 */
class Teko_VAT_Model_Observer
{
    public function salesConvertQuoteToOrder($observer)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         * @var Mage_Sales_Model_Quote $quote
         */
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        if ($quote->getData('is_vat')) {
            $order->addData([
                'is_vat' => $quote->getData('is_vat'),
                'vat_name' => $quote->getData('vat_name'),
                'vat_address' => $quote->getData('vat_address'),
                'vat_id' => $quote->getData('vat_id'),
                'vat_address_to' => $quote->getData('vat_address_to'),
            ]);
        }
    }
}