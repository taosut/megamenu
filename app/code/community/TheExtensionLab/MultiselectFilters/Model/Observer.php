<?php

/**
 * MultiselectFilters Observer Model
 *
 * @category    TheExtensionLab
 * @package     TheExtensionLab_MultiselectFilters
 * @copyright   Copyright (c) TheExtensionLab (http://www.theextensionlab.com)
 * @license     Open Software License (OSL 3.0)
 * @author      James Anelay @ TheExtensionLab <james@theextensionlab.com>
 */
class TheExtensionLab_MultiselectFilters_Model_Observer
{
    //getColumnFilters
    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid) {
            $filters = array();
            $filters['multiple'] = "theextensionlab_multiselectfilters/adminhtml_widget_grid_column_filter_multiselect";
            $block->setColumnFilters($filters);
        }
    }

    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid) {
            $transport = $observer->getTransport();
            //get the HTML
            $html = $transport->getHtml();

            //Get container so that js is only fired on this grid
            $uniqIdClass = 'grid-container-' . uniqid();

            //render the block
            $newHtml = $block->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('theextensionlab/multiselectfilters/chosen/init-js.phtml')->setUniqId($uniqIdClass)->toHtml();

            $transport->setHtml('<div class="' . $uniqIdClass . '">' . $html . ' ' . $newHtml . '</div>');
        }
    }

    public function salesEventOrderToQuote(Varien_Event_Observer $observer)
    {
        try {
            $quote = $observer->getEvent()->getQuote();
            /**
             * @var Mage_Sales_Model_Quote $quote
             */
            $order = $observer->getEvent()->getOrder();
            /**
             * @var Mage_Sales_Model_Order $order
             */
            $orderItems = $order->getAllVisibleItems();
            $listItem = [];
            foreach ($orderItems as $orderItem)
                $listItem[$orderItem->getSku()] = $orderItem->getPrice();
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                /**
                 * @var  Mage_Sales_Model_Quote_Item $quoteItem
                 */
                $oldPrice = $listItem[$quoteItem->getProduct()->getSku()];
                $quoteItem->setOriginalCustomPrice($oldPrice);
                $quoteItem->setCustomPrice(true);
            }
        } catch (Exception $e) {
            Mage::log($e->getTraceAsString(), null, 'edit_order_log.log');
        }
    }

    /**
     * Set forced canCreditmemo flag
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Payment_Model_Observer
     */
    public function salesOrderItemBeforeSave($observer)
    {
        /**
         * @var Mage_Sales_Model_Order_Item $item
         */
        $item = $observer->getEvent()->getItem();
        if ($item->isObjectNew() && (!$item->getData('standard_product_id') || !$item->getData('warehouse_sku'))) {
            $product = $item->getProduct();
            $standardProductId = Mage::getResourceModel('catalog/product')
                ->getAttributeRawValue(
                    $product->getId(),
                    'standard_product_id',
                    $item->getOrder()->getStoreId()
                );
            $warehouseSku = Mage::getResourceModel('catalog/product')
                ->getAttributeRawValue(
                    $product->getId(),
                    'warehouse_sku',
                    $item->getOrder()->getStoreId()
                );
            $unitPrice = Mage::getResourceModel('catalog/product')
                ->getAttributeRawValue(
                    $product->getId(),
                    'price',
                    $item->getOrder()->getStoreId()
                );
            $item->addData([
                'standard_product_id' => $standardProductId,
                'warehouse_sku' => $warehouseSku,
                'unit_price' => $unitPrice
            ]);
        }
        if (!$item->getData('unit_price'))
            $item->addData([
                'unit_price' => $item->getPrice(),
            ]);
    }

    public function customerSessionInit(Varien_Event_Observer $observer)
    {
        try {
            /**
             * @var Mage_Customer_Model_Session $session
             */
            $session = $observer->getEvent()->getCustomerSession();
            $request = Mage::app()->getRequest();
            if (!$session->getData('referer'))
                $session->setData('referer', $request->getServer('HTTP_REFERER'));

            if (isset($_COOKIE['c_utmsource']) && htmlspecialchars($_COOKIE["c_utmsource"]) == 'google') {
                $session->setData('utm_source', 'google');
            } else if (isset($_COOKIE['c_utmsource']) && htmlspecialchars($_COOKIE["c_utmsource"]) == 'facebook') {
                $session->setData('utm_source', 'facebook');
            }
            unset($_COOKIE['c_utmsource']);
            setcookie('c_utmsource', '', time() - 3600, '/');

            if ($request->getServer('HTTP_REFERER')) {
                if (strpos($request->getServer('HTTP_REFERER'), 'facebook') !== false) {
                    $session->setData('referer', $request->getServer('HTTP_REFERER'));
                    $session->setData('utm_source', 'facebook');
                }
                if (strpos($request->getServer('HTTP_REFERER'), 'google') !== false) {
                    $session->setData('referer', $request->getServer('HTTP_REFERER'));
                    $session->setData('utm_source', 'google');
                }
            }

            if (isset($_COOKIE['_c_agent'])) {
                $session->setData('c_agent', htmlspecialchars($_COOKIE["_c_agent"]));
            }

            foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $value) {
                if ($request->get($value))
                    $session->setData($value, $request->get($value));
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function salesOrderSaveAfter(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $observer->getEvent()->getOrder();
        if ($order->getTotalPaid() > $order->getGrandTotal())
            $order->setTotalPaid($order->getGrandTotal());
        try {
            if ($order->getUtm()) {
                $session = Mage::getSingleton('customer/session');
                if ($session->getData('utm_source') == null) {
                    if (strpos($session->getData('referer'), 'facebook') !== false) {
                        $session->setData('utm_source', 'facebook');
                    }
                    if (strpos($session->getData('referer'), 'google') !== false) {
                        $session->setData('utm_source', 'google');
                    }
                }
                Mage::getModel('TEKShop_PaymentOnline/utm')->addData([
                    'order_id' => $order->getId(),
                    'referer' => $session->getData('referer'),
                    'source' => $session->getData('utm_source'),
                    'medium' => $session->getData('utm_medium'),
                    'campaign' => $session->getData('utm_campaign'),
                    'agent' => $session->getData('c_agent'),
                    'created_at' => now()
                ])->save();
                $order->setUtm(false);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function salesOrderSaveBefore(Varien_Event_Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            if (!$order->getId())
                $order->setUtm(true);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}