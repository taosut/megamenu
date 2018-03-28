<?php
require_once(Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php');

class Ved_Checkout_CartController extends Mage_Checkout_CartController
{
    protected $ajaxCartIsUpdated = false;

    public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }

    public function homeAddAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }

        //Linh add: Get Rules
        $rules = Mage::getResourceModel('salesrule/rule_collection')->load();
        $website_id = Mage::app()->getWebsite()->getId();

        $cart = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $addedProduct = $product->getData();
            //var_dump($addedProduct["entity_id"]);die();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            //Linh start: add check rule

            $_product = Mage::getModel('catalog/product')->load($addedProduct["entity_id"]);
            $productCheck = Mage::getModel('sales/quote_item')->setQuote($cart->getQuote())->setProduct($_product);
            $productCheck->setAllItems(array($_product));
            $productCheck->getProduct()->setProductId($_product->getEntityId());

            //Get current amount item added in current cart
            $currentQty = 0;
            foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
                if ($item->getProductId() == $addedProduct["entity_id"]) {
                    $currentQty = $item->getData('qty');
                    $cartItem = $item;
                }
            }

            foreach ($rules as $rule) {
                if ($rule->getIsActive()) {
                    $websites = $rule->getWebsiteIds();

                    if (in_array($website_id, $websites) && $rule->getConditions()->validate($productCheck) && $rule->getData('simple_action') == "buy_x_get_y"
                        && $rule->getData('discount_step') > 0 && ($currentQty + $params['qty']) / $rule->getData('discount_step') >= 1
                    ) {
                        $qty = floor(($currentQty + $params['qty']) / $rule->getData('discount_step'));
                        $params['qty'] += $qty * $rule->getData('discount_amount');
                    }

                    // Nam start: buy x get y different product
                    if (in_array($website_id, $websites) && $rule->getConditions()->validate($productCheck) && $rule->getData('simple_action') == "ved_buyxgety" && $rule->getData('discount_step') > 0 && ($currentQty + $params['qty']) / $rule->getData('discount_step') >= 1
                    ) {
                        $qty = floor(($currentQty + $params['qty']) / $rule->getData('discount_step')) * $rule->getData('discount_amount');
                        $quote = $cart->getQuote()->setIsSuperMode(true);
                        $promo_sku = $rule->getData('promo_sku');
                        $promo_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $promo_sku);

                        if ($promo_product) {
                            $customOptionValue = array(
                                'free_message' => array(
                                    'label' => '(Sản phẩm khuyến mãi)',
                                    'value' => ''),
                            );
                            $promo_product->addCustomOption('additional_options', serialize($customOptionValue));
                            $cartPromoItem = $cart->getQuote()->getItemByProduct($promo_product);
                            if ($cartPromoItem && $cartPromoItem->getPrice() == 0) {
                                $free_item = unserialize($cartPromoItem->getAdditionalData());

                                // Kiem tra x co cung san pham hay la san pham khac
                                $search = array_search(strval($_product->getId()), array_column($free_item['parent'], 'product_id', 'product_id'));
                                $pre_qty = $cartPromoItem->getQty();
                                if ($search) {
                                    $cartPromoItem->setQty($pre_qty - $free_item['parent'][$search]['qty'] + $qty);
                                    $free_item['parent'][$search]['qty'] = $qty;
                                } else {
                                    $free_item['parent'][$cartItem->getProduct()->getId()] = array(
                                        'product_id' => $cartItem->getProduct()->getId(),
                                        'product_name' => $cartItem->getProduct()->getName(),
                                        'quote_id' => $cartItem->getId(),
                                        'qty' => $qty);
                                    $cartPromoItem->setQty($pre_qty + $qty);
                                }
                                $cartPromoItem->setAdditionalData(serialize($free_item));
                                $cartPromoItem->save();
                            } else {

                                $free_item = array(
                                    'type' => 'Free',
                                    'parent' => array($cartItem->getProduct()->getId() => array(
                                        'product_id' => $cartItem->getProduct()->getId(),
                                        'product_name' => $cartItem->getProduct()->getName(),
                                        'quote_id' => $cartItem->getId(),
                                        'qty' => $qty))
                                );
                                $quote->addProductAdvanced($promo_product, $qty)
                                    ->setCustomPrice(0)
                                    ->setOriginalCustomPrice(0)
                                    ->setAdditionalData(serialize($free_item));
                            }
                        }
                    }
                    //Nam end
                }
            }

            //Linh end: add check rule

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

//            //Linh start: add check rule
//            foreach($rules as $rule)
//            {
//                if ($rule->getIsActive()) {
//                    foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
//                        $productId = $item->getProductId();
//                        if($productId == $addedProduct["entity_id"]){
//                            $_product = Mage::getModel('catalog/product')->load($productId);
//                            $product = Mage::getModel('sales/quote_item')->setQuote($cart->getQuote())->setProduct($_product);
//                            $product->setAllItems(array($_product));
//                            $product->getProduct()->setProductId($_product->getEntityId());
//														$websites = $rule->getWebsiteIds();
//                            if (in_array($website_id, $websites) && $rule->getConditions()->validate($product) && $rule->getData('simple_action') == "buy_x_get_y" && $rule->getData('discount_step') > 0 && $item->getData('qty') / $rule->getData('discount_step') >= 1)
//                            {
//                                $qty = floor($item->getData('qty') / $rule->getData('discount_step'));
//                                $cart->addProduct($productId, $qty * $rule->getData('discount_amount'));
//                                //var_dump($rule->getData());die();
//                            }
//                        }
//                    }
//
//                }
//            }
//
//            //Linh end: add check rule

            $cart->save();
            $this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }

    /** Add to cart **/
    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }

        //Linh add: Get Rules
        $rules = Mage::getResourceModel('salesrule/rule_collection')->load();
        $website_id = Mage::app()->getWebsite()->getId();

        $cart = $this->_getCart();

        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
                $params['qty'] = ($params['qty'] == '0') ? '1' : $params['qty'];
                $params['qty'] = ($params['qty'] > 500) ? '500' : $params['qty'];
            } else {
                $params['qty'] = 1;
            }

            $product = $this->_initProduct();
            $addedProduct = $product->getData();
            //var_dump($addedProduct["entity_id"]);die();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

//            //Linh start: add check rule
//
//            $_product = Mage::getModel('catalog/product')->load($addedProduct["entity_id"]);
//            $productCheck = Mage::getModel('sales/quote_item')->setQuote($cart->getQuote())->setProduct($_product);
//            $productCheck->setAllItems(array($_product));
//            $productCheck->getProduct()->setProductId($_product->getEntityId());
//
//            //Get current amount item added in current cart
//            $currentQty = 0;
//            foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
//                if ($item->getProductId() == $addedProduct["entity_id"]) {
//                    $currentQty = $item->getData('qty');
//                }
//            }
//
//            foreach ($rules as $rule) {
//                if ($rule->getIsActive()) {
//                    $websites = $rule->getWebsiteIds();
//
//                    if (in_array($website_id, $websites) && $rule->getConditions()->validate($productCheck) && $rule->getData('simple_action') == "buy_x_get_y"
//                        && $rule->getData('discount_step') > 0 && ($currentQty + $params['qty']) / $rule->getData('discount_step') >= 1
//                    ) {
//                        $qty = floor(($currentQty + $params['qty']) / $rule->getData('discount_step'));
//                        $params['qty'] += $qty * $rule->getData('discount_amount');
//                    }
//                }
//            }
//
//            //Linh end: add check rule
//
//            $cart->addProduct($product, $params);

            //Linh start: add check rule

            $cart->addProduct($product, $params);

            $_product = Mage::getModel('catalog/product')->load($addedProduct["entity_id"]);

            $productCheck = Mage::getModel('sales/quote_item')->setQuote($cart->getQuote())->setProduct($_product);
            $productCheck->setAllItems(array($_product));
            $productCheck->getProduct()->setProductId($_product->getEntityId());
            $cartItem = $cart->getQuote()->getItemByProduct($_product);

            //Get current amount item added in current cart
            $currentQty = 0;
            $currentFreeQty = 0;
            $itemProductIdCheck = '';
            foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
                if ($item->getProductId() == $addedProduct["entity_id"] && $itemProductIdCheck !== $item->getProductId()) { // Loop 1 time if Buy X get X
                    $itemProductIdCheck = $item->getProductId();
                    $currentQty = $item->getData('qty') - $params['qty'];
                    $cartItem = $item;
                    $currentFree = unserialize($cartItem->getAdditionalData());
                    $currentFreeQty = (isset($currentFree['quantity']) && $currentFree['quantity']) ? $currentFree['quantity'] : 0;
                }
            }

            $currentRealQty = $currentQty - $currentFreeQty;
            $updatedQty = $currentRealQty + $params['qty'];

            foreach ($rules as $rule) {
                if ($rule->getIsActive()) {
                    $websites = $rule->getWebsiteIds();
                    // Buy X Get X
//                    if (in_array($website_id, $websites) && $rule->getConditions()->validate($productCheck) && $rule->getData('simple_action') == "buy_x_get_y"
//                        && $rule->getData('discount_step') > 0 && ($updatedQty) / $rule->getData('discount_step') >= 1
//                    ) {
//
//                        $qty = floor(($updatedQty) / $rule->getData('discount_step')) * $rule->getData('discount_amount');
//
//                        $free_item = array(
//                            'type' => 'Free',
//                            'product_id' => $cartItem->getProduct()->getId(),
//                            'product_name' => $cartItem->getProduct()->getName(),
//                            'price' => $cartItem->getProduct()->getFinalPrice(),
//                            'quantity' => $qty
//                        );
//
//                        $cartItem->setQty($qty + $updatedQty);
//                        $cartItem->setAdditionalData(serialize($free_item));
//                        $cart->save();
//                        $cartItem->save();
//                    }

                    // Nam start: buy x get y different product
                    if (in_array($website_id, $websites) && $rule->getConditions()->validate($productCheck) && $rule->getData('simple_action') == "ved_buyxgety"
                        && $rule->getData('discount_step') > 0 && ($updatedQty) / $rule->getData('discount_step') >= 1
                    ) {
                        $qty = floor(($updatedQty) / $rule->getData('discount_step')) * $rule->getData('discount_amount');
                        $quote = $cart->getQuote()->setIsSuperMode(true);
                        $promo_sku = $rule->getData('promo_sku');
                        $promo_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $promo_sku);
                        if ($promo_product) {
                            $customOptionValue = array(
                                'free_message' => array(
                                    'label' => '(Sản phẩm khuyến mãi)',
                                    'value' => ''),
                            );
                            $promo_product->addCustomOption('additional_options', serialize($customOptionValue));
                            $cartPromoItem = $cart->getQuote()->getItemByProduct($promo_product);

                            if ($cartPromoItem && $cartPromoItem->getPrice() == 0) {
                                $free_item = unserialize($cartPromoItem->getAdditionalData());

                                // Kiem tra x co cung san pham hay la san pham khac
                                $search = array_search(strval($_product->getId()), array_column($free_item['parent'], 'product_id', 'product_id'));
                                $pre_qty = $cartPromoItem->getQty();
                                if ($search) {
                                    $cartPromoItem->setQty($pre_qty - $free_item['parent'][$search]['qty'] + $qty);
                                    $free_item['parent'][$search]['qty'] = $qty;
                                } else {
                                    $free_item['parent'][$cartItem->getProduct()->getId()] = array(
                                        'product_id' => $cartItem->getProduct()->getId(),
                                        'product_name' => $cartItem->getProduct()->getName(),
                                        'quote_id' => $cartItem->getId(),
                                        'qty' => $qty);
                                    $cartPromoItem->setQty($pre_qty + $qty);
                                }
                                $cartPromoItem->setAdditionalData(serialize($free_item));
                                $cartPromoItem->save();
                            } else {
                                $free_item = array(
                                    'type' => 'Free',
                                    'parent' => array($cartItem->getProduct()->getId() => array(
                                        'product_id' => $cartItem->getProduct()->getId(),
                                        'product_name' => $cartItem->getProduct()->getName(),
                                        'quote_id' => $cartItem->getId(),
                                        'qty' => $qty))
                                );
                                $quote->addProductAdvanced($promo_product, $qty)
                                    ->setCustomPrice(0)
                                    ->setOriginalCustomPrice(0)
                                    ->setAdditionalData(serialize($free_item));
                            }
                        }
                    }
                    //Nam end
                }
            }

            //Linh end: add check rule

            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

//            //Linh start: add check rule
//            foreach($rules as $rule)
//            {
//                if ($rule->getIsActive()) {
//                    foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
//                        $productId = $item->getProductId();
//                        if($productId == $addedProduct["entity_id"]){
//                            $_product = Mage::getModel('catalog/product')->load($productId);
//                            $product = Mage::getModel('sales/quote_item')->setQuote($cart->getQuote())->setProduct($_product);
//                            $product->setAllItems(array($_product));
//                            $product->getProduct()->setProductId($_product->getEntityId());
//														$websites = $rule->getWebsiteIds();
//                            if (in_array($website_id, $websites) && $rule->getConditions()->validate($product) && $rule->getData('simple_action') == "buy_x_get_y" && $rule->getData('discount_step') > 0 && $item->getData('qty') / $rule->getData('discount_step') >= 1)
//                            {
//                                $qty = floor($item->getData('qty') / $rule->getData('discount_step'));
//                                $cart->addProduct($productId, $qty * $rule->getData('discount_amount'));
//                                //var_dump($rule->getData());die();
//                            }
//                        }
//                    }
//
//                }
//            }
//
//            //Linh end: add check rule

            $cart->save();
            $this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }

    public function addAjaxAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirectReferer();
            return;
        }
        //Linh add: Get Rules
        //only check rule active and still in valid duration
        $website_id = Mage::app()->getWebsite()->getId();
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $rules = Mage::getResourceModel('salesrule/rule_collection')
            ->addWebsiteGroupDateFilter($website_id, $groupId, date("Y-m-d"))
            ->load();

        $cart = $this->_getCart();
        $params = $this->getRequest()->getParams();
        $super_attribute_id = '';
        if (isset($params['super_attribute'])) {
            $super_attribute_id = array_values($params['super_attribute'])[0];
        }

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
                $params['qty'] = ($params['qty'] == '0') ? '1' : $params['qty'];
                $params['qty'] = ($params['qty'] > 500) ? '500' : $params['qty'];
            }

            $product = $this->_initProduct();
            $addedProduct = $product->getData();
            //var_dump($addedProduct["entity_id"]);die();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_redirectReferer();
                return;
            }

            //Linh start: add check rule
            $cart->addProduct($product, $params);
            $_product = Mage::getModel('catalog/product')->load($addedProduct["entity_id"]);

            $productCheck = Mage::getModel('sales/quote_item')->setQuote($cart->getQuote())->setProduct($_product);
            $productCheck->setAllItems(array($_product));
            $productCheck->getProduct()->setProductId($_product->getEntityId());
            $cartItem = $cart->getQuote()->getItemByProduct($_product);

            //Get current amount item added in current cart
            $currentQty = 0;
            $currentFreeQty = 0;
            $itemProductIdCheck = '';
            foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
                if ($item->getProductId() == $addedProduct["entity_id"] && $itemProductIdCheck !== $item->getProductId()) { // Loop 1 time if Buy X get X
                    $itemProductIdCheck = $item->getProductId();
                    $currentQty = $item->getData('qty') - $params['qty'];
                    $cartItem = $item;
                    $currentFree = unserialize($cartItem->getAdditionalData());
                    $currentFreeQty = (isset($currentFree['quantity']) && $currentFree['quantity']) ? $currentFree['quantity'] : 0;
                }
            }

            $currentRealQty = $currentQty - $currentFreeQty;
            $updatedQty = $currentRealQty + $params['qty'];

            foreach ($rules as $rule) {

                if ($rule->getIsActive()) {
                    $websites = $rule->getWebsiteIds();
                    // Buy X get X
//                    if (in_array($website_id, $websites) && $rule->getConditions()->validate($productCheck) && $rule->getData('simple_action') == "buy_x_get_y"
//                        && $rule->getData('discount_step') > 0 && ($updatedQty) / $rule->getData('discount_step') >= 1
//                    ) {
//                        $qty = floor(($updatedQty) / $rule->getData('discount_step')) * $rule->getData('discount_amount');
//
//                        $free_item = array(
//                            'type' => 'Free',
//                            'product_id' => $cartItem->getProduct()->getId(),
//                            'product_name' => $cartItem->getProduct()->getName(),
//                            'price' => $cartItem->getProduct()->getFinalPrice(),
//                            'quantity' => $qty
//                        );
//
//                        $cartItem->setQty($qty + $updatedQty);
//                        $cartItem->setAdditionalData(serialize($free_item));
//                        $cart->save();
//                        $cartItem->save();
//                    }

                    // Nam start: buy x get y different product
                    if (in_array($website_id, $websites) && $rule->getConditions()->validate($productCheck) && $rule->getData('simple_action') == "ved_buyxgety"
                        && $rule->getData('discount_step') > 0 && ($updatedQty) / $rule->getData('discount_step') >= 1
                    ) {
                        $qty = floor(($updatedQty) / $rule->getData('discount_step')) * $rule->getData('discount_amount');
                        $quote = $cart->getQuote()->setIsSuperMode(true);
                        $promo_sku = $rule->getData('promo_sku');

                        $promo_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $promo_sku);
                        if ($promo_product) {
                            $customOptionValue = array(
                                'free_message' => array(
                                    'label' => '(Sản phẩm khuyến mãi)',
                                    'value' => ''),
                            );
                            $promo_product->addCustomOption('additional_options', serialize($customOptionValue));
                            $cartPromoItem = $cart->getQuote()->getItemByProduct($promo_product);
                            if ($cartPromoItem && $cartPromoItem->getPrice() == 0) {
                                $free_item = unserialize($cartPromoItem->getAdditionalData());
                                // Kiem tra x co cung san pham hay la san pham khac
                                $search = array_search(strval($_product->getId()), array_column($free_item['parent'], 'product_id', 'product_id'));

                                $pre_qty = $cartPromoItem->getQty();

                                if ($search) { // Cung san pham
                                    if ($super_attribute_id != '') { // Neu san pham co chon thuoc tinh
                                        $cartPromoItem->setQty($pre_qty + $params['qty']);
                                    } else { // Neu san pham khong co chon thuoc tinh
                                        $cartPromoItem->setQty($pre_qty - $free_item['parent'][$search]['qty'] + $qty);
                                    }
                                    $free_item['parent'][$search]['qty'] = $qty;
                                } else { // Khac san pham
                                    $free_item['parent'][$cartItem->getProduct()->getId()] = array(
                                        'product_id' => $cartItem->getProduct()->getId(),
                                        'product_name' => $cartItem->getProduct()->getName(),
                                        'quote_id' => $cartItem->getId(),
                                        'qty' => $qty);
                                    $cartPromoItem->setQty($pre_qty + $qty);
                                }
                                $cartPromoItem->setAdditionalData(serialize($free_item));
                                $cartPromoItem->save();
                            } else {
                                $free_item = array(
                                    'type' => 'Free',
                                    'parent' => array($cartItem->getProduct()->getId() => array(
                                        'product_id' => $cartItem->getProduct()->getId(),
                                        'product_name' => $cartItem->getProduct()->getName(),
                                        'quote_id' => $cartItem->getId(),
                                        'qty' => $qty))
                                );

                                $quote->addProductAdvanced($promo_product, $qty)
                                    ->setCustomPrice(0)
                                    ->setOriginalCustomPrice(0)
                                    ->setAdditionalData(serialize($free_item));
                            }
                        }
                    }
                    //Nam end
                }
            }

            //Linh end: add check rule

            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);
            $message = '';
            $error_flg = 0;
            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $error_flg = 0;
                    $grand_total = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
                    $sub_total = Mage::helper('checkout/cart')->getQuote()->getSubtotal();
                    $block = $this->getLayout()->createBlock('ved_checkout/excart');
                    $block_ex_header = $this->getLayout()->createBlock('ved_checkout/sidebar');
                    $block_footer_cart = $this->getLayout()->createBlock('ved_checkout/footercart');
                    $result = array('grand_total' => $grand_total, 'sub_total' => $sub_total, 'message' => $message, 'ex_html' => $block->toHtml(), 'ex_header_html' => $block_ex_header->toHtml(), 'footer_cart_html' => $block_footer_cart->toHtml(), 'error_flg' => $error_flg);
                    $this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                }

            }

        } catch (Mage_Core_Exception $e) {
            $message = '';
            if ($this->_getSession()->getUseNotice(true)) {
                $message = Mage::helper('core')->escapeHtml($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $msg) {
                    $message .= $msg;
                }
            }
            $grand_total = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
            $sub_total = Mage::helper('checkout/cart')->getQuote()->getSubtotal();
            $block = $this->getLayout()->createBlock('ved_checkout/excart');
            $block_ex_header = $this->getLayout()->createBlock('ved_checkout/sidebar');
            $block_footer_cart = $this->getLayout()->createBlock('ved_checkout/footercart');
            $error_flg = 1;
            $result = array('grand_total' => $grand_total, 'sub_total' => $sub_total, 'message' => $message, 'ex_html' => $block->toHtml(), 'ex_header_html' => $block_ex_header->toHtml(), 'footer_cart_html' => $block_footer_cart->toHtml(), 'error_flg' => $error_flg);
            $this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        } catch (Exception $e) {

            Mage::logException($e);
            $message = $this->__('Cannot add the item to shopping cart.');
            $error_flg = 1;
            $grand_total = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
            $sub_total = Mage::helper('checkout/cart')->getQuote()->getSubtotal();
            $block = $this->getLayout()->createBlock('ved_checkout/excart');
            $block_ex_header = $this->getLayout()->createBlock('ved_checkout/sidebar');
            $block_footer_cart = $this->getLayout()->createBlock('ved_checkout/footercart');
            $result = array('grand_total' => $grand_total, 'sub_total' => $sub_total, 'message' => $message, 'ex_html' => $block->toHtml(), 'ex_header_html' => $block_ex_header->toHtml(), 'footer_cart_html' => $block_footer_cart->toHtml(), 'error_flg' => $error_flg);
            $$this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        }
    }

    public function addMultipleAction()
    {
        $productIds = $this->getRequest()->getParam('products');
        if (!is_array($productIds)) {
            $result = array('error' => true);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }

        $cart = $this->_getCart();

        $website_id = Mage::app()->getWebsite()->getId();
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $rules = Mage::getResourceModel('salesrule/rule_collection')
            ->addWebsiteGroupDateFilter($website_id, $groupId, date("Y-m-d"))
            ->load();

        try {
            foreach ($productIds as $productId) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);

                $productQty = $this->getRequest()->getParam('qty' . $productId, 0);
                if ($productQty <= 0) continue; // nothing to add

                //Linh start: add check rule

                $cart->addProduct($product, $productQty);
                $_product = Mage::getModel('catalog/product')->load($productId);

                $productCheck = Mage::getModel('sales/quote_item')->setQuote($cart->getQuote())->setProduct($_product);
                $productCheck->setAllItems(array($_product));
                $productCheck->getProduct()->setProductId($_product->getEntityId());
                $cartItem = $cart->getQuote()->getItemByProduct($_product);


                //Get current amount item added in current cart
                $currentQty = 0;
                $currentFreeQty = 0;
                $itemProductIdCheck = '';

                foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
                    if ($item->getProductId() == $productId && $itemProductIdCheck !== $item->getProductId()) {
                        $itemProductIdCheck = $item->getProductId();
                        $currentQty = $item->getData('qty') - $productQty;
                        $cartItem = $item;
                        $currentFree = unserialize($cartItem->getAdditionalData());
                        $currentFreeQty = (isset($currentFree['quantity']) && $currentFree['quantity']) ? $currentFree['quantity'] : 0;
                    }
                }

                $currentRealQty = $currentQty - $currentFreeQty;
                $updatedQty = $currentRealQty + $productQty;

                foreach ($rules as $rule) {
                    if ($rule->getIsActive()) {
                        $websites = $rule->getWebsiteIds();

                        // Buy X Get X
//                        if (in_array($website_id, $websites) && $rule->getConditions()->validate($productCheck) && $rule->getData('simple_action') == "buy_x_get_y"
//                            && $rule->getData('discount_step') > 0 && ($updatedQty) / $rule->getData('discount_step') >= 1
//                        ) {
//                            $qty = floor(($updatedQty) / $rule->getData('discount_step')) * $rule->getData('discount_amount');
//
//                            $free_item = array(
//                                'type' => 'Free',
//                                'product_id' => $cartItem->getProduct()->getId(),
//                                'product_name' => $cartItem->getProduct()->getName(),
//                                'price' => $cartItem->getProduct()->getFinalPrice(),
//                                'quantity' => $qty
//                            );
//
//                            $cartItem->setQty($qty + $updatedQty);
//                            $cartItem->setAdditionalData(serialize($free_item));
//                            $cart->save();
//                            $cartItem->save();
//                        }

                        // Nam start: buy x get y different product
                        if (in_array($website_id, $websites) && $rule->getConditions()->validate($productCheck) && $rule->getData('simple_action') == "ved_buyxgety" && $rule->getData('discount_step') > 0 && ($updatedQty) / $rule->getData('discount_step') >= 1
                        ) {
                            $qty = floor(($updatedQty) / $rule->getData('discount_step')) * $rule->getData('discount_amount');
                            $quote = $cart->getQuote()->setIsSuperMode(true);
                            $promo_sku = $rule->getData('promo_sku');

                            $promo_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $promo_sku);
                            if ($promo_product) {
                                $customOptionValue = array(
                                    'free_message' => array(
                                        'label' => '(Sản phẩm khuyến mãi)',
                                        'value' => ''),
                                );
                                $promo_product->addCustomOption('additional_options', serialize($customOptionValue));
                                $cartPromoItem = $cart->getQuote()->getItemByProduct($promo_product);
                                if ($cartPromoItem && $cartPromoItem->getPrice() == 0) {
                                    $free_item = unserialize($cartPromoItem->getAdditionalData());
                                    // Kiem tra x co cung san pham hay la san pham khac
                                    $search = array_search(strval($_product->getId()), array_column($free_item['parent'], 'product_id', 'product_id'));
                                    $pre_qty = $cartPromoItem->getQty();

                                    if ($search) { // Cung san pham
                                        $cartPromoItem->setQty($pre_qty - $free_item['parent'][$search]['qty'] + $qty);

                                        $free_item['parent'][$search]['qty'] = $qty;
                                    } else { // Khac san pham
                                        $free_item['parent'][$cartItem->getProduct()->getId()] = array(
                                            'product_id' => $cartItem->getProduct()->getId(),
                                            'product_name' => $cartItem->getProduct()->getName(),
                                            'quote_id' => $cartItem->getId(),
                                            'qty' => $qty);
                                        $cartPromoItem->setQty($pre_qty + $qty);
                                    }
                                    $cartPromoItem->setAdditionalData(serialize($free_item));
                                    $cartPromoItem->save();
                                } else {
                                    $free_item = array(
                                        'type' => 'Free',
                                        'parent' => array($cartItem->getProduct()->getId() => array(
                                            'product_id' => $cartItem->getProduct()->getId(),
                                            'product_name' => $cartItem->getProduct()->getName(),
                                            'quote_id' => $cartItem->getId(),
                                            'qty' => $qty))
                                    );

                                    $quote->addProductAdvanced($promo_product, $qty)
                                        ->setCustomPrice(0)
                                        ->setOriginalCustomPrice(0)
                                        ->setAdditionalData(serialize($free_item));
                                }
                            }
                        }
                        //Nam end
                    }
                }
                //Linh end: add check rule

                if ($product->getStatus() == '1') {
                    $message = $this->__('%s đã được thêm vào giỏ hàng thành công.', $product->getName());
                    Mage::getSingleton('checkout/session')->addSuccess($message);
                } else {
                    $messageError = $this->__('%s hiện đang hết hàng, vui lòng liên hệ để được hỗ trợ.', $product->getName());
                    Mage::getSingleton('checkout/session')->addError($messageError);
                }
                $this->_getSession()->setCartWasUpdated(true);
            }
            $cart->save();

        } catch (Mage_Core_Exception $e) {
            if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                $message = $e->getMessage();
                if ($message == 'Please specify the product\'s option(s).') {
                    $message = 'Sản phẩm ' . $product->getName() . ' không được thêm vào giỏ hàng do có chứa thuộc tính, vui lòng vào chi tiết sản phẩm để chọn thuộc tính.';
                }
                Mage::getSingleton('checkout/session')->addNotice($product->getName() . ': ' . $message);
                $result = array('error' => true);
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } else {
                Mage::getSingleton('checkout/session')->addError($product->getName() . ': ' . $e->getMessage());
                $result = array('error' => true);
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        } catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addException($e, $this->__('Can not add item to shopping cart'));
            $result = array('error' => true);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }

        $result = array('success' => true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    /** End Add to cart **/

    /** Apply coupon **/
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */

        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = trim(htmlspecialchars($this->getRequest()->getParam('coupon_code'), ENT_QUOTES, 'UTF-8'));

        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();
            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                } else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_redirectReferer();
    }

    public function couponPostAjaxAction()
    {
        /**
         * No reason continue with empty shopping cart
         */

        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = trim(htmlspecialchars($this->getRequest()->getParam('coupon_code'), ENT_QUOTES, 'UTF-8'));

        $remove = $this->getRequest()->getParam('remove');

        if ($remove == 1) {
            $couponCode = '';
        }

//        $oldCouponCode = $this->_getQuote()->getCouponCode();


        $error_message = '';
        $is_success = false;
        try {
            $codeLength = strlen($couponCode);

            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();
            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $is_success = true;
                    $this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
//                    $this->_getSession()->addSuccess(
//                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
//                    );
                } else {
                    $error_message = $this->__('Coupon code "%s" is not valid.', strtoupper(Mage::helper('core')->escapeHtml($couponCode)));
//                    $this->_getSession()->addError(
//                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
//                    );
                }
            } else {
//                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }


        } catch (Mage_Core_Exception $e) {
            $error_message = $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $error_message = $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $grand_total = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
        $sub_total = Mage::helper('checkout/cart')->getQuote()->getSubtotal();
        $discount_value = $sub_total - $grand_total;
        $result = array('grand_total' => $grand_total, 'discount_value' => $discount_value, 'error_message' => $error_message, 'is_remove' => $remove, 'is_success' => $is_success);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }
    /** End Apply coupon **/

    /** Update shopping cart  **/
    public function updatePostAction()
    {
        if (!$this->_validateFormKey()) {
            echo "Error key";
            die();
            $this->_redirect('*/*/');
            return;
        }

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');
        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateNewShoppingCart();
                break;
            default:
                $this->_updateNewShoppingCart();
        }
        $this->_goBack();
    }

    public function updatePostAjaxAction()
    {
        if (!$this->_validateFormKey()) {
            echo "Error key";
            die();
            $this->_redirect('*/*/');
            return;
        }

        $this->ajaxCartIsUpdated = false;
        $this->_updateNewShoppingCart();

        $message = ($this->ajaxCartIsUpdated) ? 'Cập nhật thông tin giỏ hàng thành công.' : 'Có lỗi xảy ra khi cập nhật giỏ hàng.';

        $grand_total = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
        $sub_total = Mage::helper('checkout/cart')->getQuote()->getSubtotal();
        $block = $this->getLayout()->createBlock('ved_checkout/excart');
        $block_ex_header = $this->getLayout()->createBlock('ved_checkout/sidebar');
        $block_footer_cart = $this->getLayout()->createBlock('ved_checkout/footercart');
        $result = array('grand_total' => $grand_total, 'sub_total' => $sub_total, 'message' => $message, 'ex_html' => $block->toHtml(), 'ex_header_html' => $block_ex_header->toHtml(), 'footer_cart_html' => $block_footer_cart->toHtml(), 'ajax_cart_is_updated' => $this->ajaxCartIsUpdated);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    protected function _updateNewShoppingCart()
    {
        try {
            $total_free_item_price = 0;
            $free_items = array();
            $cartData = $this->getRequest()->getParam('cart');

            //Linh add: Get Rules
            $rules = Mage::getResourceModel('salesrule/rule_collection')->load();
            $website_id = Mage::app()->getWebsite()->getId();

            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $cart = $this->_getCart();
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        if (isset($data['qty'])) {
                            $data['qty'] = (trim($data['qty']) == '0') ? '1' : trim($data['qty']);
                            $data['qty'] = ($data['qty'] > 500) ? '500' : $data['qty'];
                            $cartData[$index]['qty'] = $filter->filter($data['qty']);
                        }
                    }
                    if (isset($data['previous_qty'])) {
                        $cartData[$index]['previous_qty'] = $filter->filter(trim($data['previous_qty']));
                    }
                    $cartItem = Mage::getModel('sales/quote_item')->load($index);

                    if ($cartItem && $cartItem->getPrice() == 0 && $cartData[$index]['is_free_item'] == 1) {
                        $cart->removeItem($index);
                        unset($cartData[$index]);
                    }
                }
//                foreach ($cart->getQuote()->getAllVisibleItems() as $cartItem) {
//                    if ($cartItem->getPrice() == 0) {
//                        $cart->removeItem($cartItem->getId());
//                    }
//                }
                if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                //Linh start: add check rule
                foreach ($cartData as $index => $data) {
                    $cartData[$index]['product_id'] = $filter->filter(trim($data['product_id']));
                    $_product = Mage::getModel('catalog/product')->load($cartData[$index]['product_id']);
                    $product = Mage::getModel('sales/quote_item')->setQuote($cart->getQuote())->setProduct($_product);
                    $product->setAllItems(array($_product));
                    $product->getProduct()->setProductId($_product->getEntityId());
                    $item = $cart->getQuote()->getItemByProduct($_product);

                    foreach ($cart->getQuote()->getAllVisibleItems() as $cartItem) {
                        if ($cartItem->getItemId() == $index) {
                            $item = $cartItem;
                        }
                    }

                    //$item->setAdditionalData(null);

                    foreach ($rules as $rule) {
                        if ($rule->getIsActive()) {
                            $websites = $rule->getWebsiteIds();
                            // Buy X get X
//                            if (in_array($website_id, $websites) && $rule->getConditions()->validate($product) && $rule->getData('simple_action') == "buy_x_get_y" && $rule->getData('discount_step') > 0 && $cartData[$index]['qty'] / $rule->getData('discount_step') >= 1) {
//                                $qty = floor($cartData[$index]['qty'] / $rule->getData('discount_step'));
//
//                                $cartData[$index]['qty'] += $qty * $rule->getData('discount_amount');
//
//                                $free_item = array(
//                                    'type' => 'Free',
//                                    'product_id' => $item->getProduct()->getId(),
//                                    'product_name' => $item->getProduct()->getName(),
//                                    'price' => $item->getProduct()->getFinalPrice(),
//                                    'quantity' => $qty
//                                );
//                                $item->setAdditionalData(serialize($free_item));
//                            }

                            // Nam start: buy x get y different product
                            if (in_array($website_id, $websites) && $rule->getConditions()->validate($product) && $rule->getData('simple_action') == "ved_buyxgety" && $rule->getData('discount_step') > 0 && $cartData[$index]['qty'] / $rule->getData('discount_step') >= 1
                            ) {
                                $quote = $cart->getQuote()->setIsSuperMode(true);
                                $promo_sku = $rule->getData('promo_sku');
                                $promo_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $promo_sku);
                                $qty = floor($cartData[$index]['qty'] / $rule->getData('discount_step')) * $rule->getData('discount_amount');
                                if ($promo_product) {
                                    $customOptionValue = array(
                                        'free_message' => array(
                                            'label' => '(Sản phẩm khuyến mãi)',
                                            'value' => ''),
                                    );
                                    $promo_product->addCustomOption('additional_options', serialize($customOptionValue));
                                    $cartPromoItem = $cart->getQuote()->getItemByProduct($promo_product);
                                    if ($cartPromoItem && $cartPromoItem->getPrice() == 0) {
                                        $free_item = unserialize($cartPromoItem->getAdditionalData());
                                        $free_item['parent'][$item->getProduct()->getId()] = array(
                                            'product_id' => $item->getProduct()->getId(),
                                            'product_name' => $item->getProduct()->getName(),
                                            'quote_id' => $item->getId(),
                                            'qty' => $qty);
                                        $cartPromoItem->setQty($cartPromoItem->getQty() + $qty);
                                        $cartPromoItem->setAdditionalData(serialize($free_item));
                                        $cartPromoItem->save();
                                    } else {
                                        $free_item = array(
                                            'type' => 'Free',
                                            'parent' => array($item->getProduct()->getId() => array(
                                                'product_id' => $item->getProduct()->getId(),
                                                'product_name' => $item->getProduct()->getName(),
                                                'quote_id' => $item->getId(),
                                                'qty' => $qty))
                                        );
                                        $quote->addProductAdvanced($promo_product, $qty)
                                            ->setCustomPrice(0)
                                            ->setOriginalCustomPrice(0)
                                            ->setAdditionalData(serialize($free_item));
                                    }
                                }
                            }
                            //Nam end
                        }
                    }
                    $item->save();

                    $freeItem = unserialize($item->getAdditionalData());

                    if ($freeItem && isset($freeItem['quantity']) && isset($freeItem['price'])) {
                        $total_free_item_price += $freeItem['quantity'] * $freeItem['price'];
                        $free_item = [
                            'item' => $free_item,
                            'item_id' => $index
                        ];
                        array_push($free_items, $free_item);
                    } else {
                        $free_item = [
                            'item' => '',
                            'item_id' => $index
                        ];
                        array_push($free_items, $free_item);
                    }
                }
                //Linh end
                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
                $this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
            $this->ajaxCartIsUpdated = true;

        } catch (Mage_Core_Exception $e) {
            $this->ajaxCartIsUpdated = false;
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->ajaxCartIsUpdated = false;
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }

        $response['total_money'] = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
        $response['sub_money'] = Mage::helper('checkout/cart')->getQuote()->getSubtotal();
        $response['save_money'] = $response['sub_money'] - $response['total_money'] - $total_free_item_price;
        $response['total_products'] = Mage::helper('checkout/cart')->getSummaryCount();
        $response['free_item'] = $free_items;
        $results = $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        //$this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
        return $results;
    }
    /** End Update shopping cart  **/

    /** Delete item from shopping cart **/
    public function deleteAction()
    {
        $cart = $this->_getCart();
        $id = (int)$this->getRequest()->getParam('id');
        $delete_qty = $this->getRequest()->getParam('delete_qty');

        $mainItem = Mage::getModel('sales/quote_item')->load($id);

        if ($id) {
            try {
                // Remove san pham khuyen mai
                foreach ($cart->getQuote()->getAllVisibleItems() as $cartItem) {
                    $freeItem = unserialize($cartItem->getAdditionalData());
                    if (isset($freeItem['parent'])) {
                        $search = array_search(strval($mainItem->getProductId()), array_column($freeItem['parent'], 'product_id', 'product_id'));
                        if ($search) {
//                            $updateQty = $cartItem->getQty() - $freeItem['parent'][$search]['qty'];
                            $updateQty = $cartItem->getQty() - intval($delete_qty);

                            if ($updateQty > 0) {
                                $cartItem->setAdditionalData(serialize($freeItem));
                                $cartItem->setQty($updateQty);
                                $cartItem->save();
                            } else {
                                unset($freeItem['parent'][$search]);
                                $cart->removeItem($cartItem->getId());
                            }
                        }
                    }
                }
                // Remove san pham chinh
                $cart->removeItem($id)->save();
                $this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
                Mage::logException($e);
            }
        }
        $this->_redirectReferer(Mage::getUrl('*/*'));
    }

    public function deleteAjaxAction()
    {
        $cart = $this->_getCart();
        $id = (int)$this->getRequest()->getParam('id');
        $delete_qty = $this->getRequest()->getParam('delete_qty');

        $message = '';
        $mainItem = Mage::getModel('sales/quote_item')->load($id);
        $error_flg = 0;
        if ($id) {
            try {
                // Remove san pham khuyen mai

                foreach ($cart->getQuote()->getAllVisibleItems() as $cartItem) {
                    $freeItem = unserialize($cartItem->getAdditionalData());

                    if (isset($freeItem['parent'])) {

                        $search = array_search(strval($mainItem->getProductId()), array_column($freeItem['parent'], 'product_id', 'product_id'));

                        if ($search) {
                            if (intval($delete_qty) > intval($freeItem['parent'][$search]['qty'])) {
                                $updateQty = $cartItem->getQty() - intval($delete_qty);
                            } else {
                                $updateQty = $cartItem->getQty() - intval($freeItem['parent'][$search]['qty']);
                            }

                            if ($updateQty > 0) {
                                $cartItem->setAdditionalData(serialize($freeItem));
                                $cartItem->setQty($updateQty);
                                $cartItem->save();
                            } else {
                                unset($freeItem['parent'][$search]);
                                $cart->removeItem($cartItem->getId());
                            }
                        }
                    }

                }

                // Remove san pham chinh
                $cart->removeItem($id)->save();
                $message = 'Xóa sản phẩm khỏi giỏ hàng thành công.';
            } catch (Exception $e) {
                $error_flg = 1;
                $message = 'Có lỗi xảy ra khi xóa sản phẩm.';
                Mage::logException($e);
            }
        }

        $grand_total = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
        $sub_total = Mage::helper('checkout/cart')->getQuote()->getSubtotal();
        $block = $this->getLayout()->createBlock('ved_checkout/excart');
        $block_ex_header = $this->getLayout()->createBlock('ved_checkout/sidebar');

        $block_footer_cart = $this->getLayout()->createBlock('ved_checkout/footercart');
        $result = array('grand_total' => $grand_total, 'sub_total' => $sub_total, 'message' => $message, 'ex_html' => $block->toHtml(), 'ex_header_html' => $block_ex_header->toHtml(), 'footer_cart_html' => $block_footer_cart->toHtml(), 'error_flg' => $error_flg);

        $this->_getQuote()->setOldGrandTotal($this->_getQuote()->getBaseSubtotalWithDiscount())->save();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    /** End Delete item from shopping cart **/

    /** Export price sheet functions */
    /** Export excel */
    public function exportExcelAction()
    {
        // create new folder in media to save excel file
        $today = getdate();
        $today_folder = $today['year'] . '-' . $today['mon'] . '-' . $today['mday'];
        if (!file_exists('media/export_price_sheet/' . $today_folder)) {
            mkdir('media/export_price_sheet/' . $today_folder, 0777, true);
        }

        // create file name
        $time = time();
        $position = rand(0, 10);
        $tmp = substr(hash('md5', $time), $position, $position + rand(1, 10));
        $fileName = date("H-i-s", $time + 7 * 60 * 60) . '_' . $tmp . "_price-sheet.xlsx";

        // get data price sheet
        $cartItems = $this->getRequest()->getParam('cartItems');
        $discountValue = $this->getRequest()->getParam('discountValue');

        // Load excel
        include Mage::getBaseDir("lib") . DS . "Excel" . DS . "PHPExcel.php";

        $objPHPExcel = PHPExcel_IOFactory::load("./template/tekshop/bao_gia_cart_template.xlsx");

        // Set title of sheet
        $objPHPExcel->getActiveSheet()->setTitle("Bao gia san pham");
        $objPHPExcel->setActiveSheetIndex(0);

        // set date
        $objPHPExcel->getActiveSheet(0)
            ->setCellValue('G9', PHPExcel_Shared_Date::PHPToExcel($time));
        $objPHPExcel->setActiveSheetIndex(0)
            ->getStyle('G9')
            ->getNumberFormat()
            ->setFormatCode('dd-mm-yyyy');

        // set currency format
        $objPHPExcel->setActiveSheetIndex(0)
            ->getStyle('F12:H25')
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $objPHPExcel->setActiveSheetIndex(0)
            ->getStyle('H26:H28')
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        $totalPrice = 0;
        $rowCount = 12;

        // append data to cell
        foreach ($cartItems as $index => $item) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->getRowDimension($rowCount)
                ->setRowHeight(22.5);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B' . $rowCount, $index + 1)
                ->setCellValue('C' . $rowCount, $item['name'])
                ->setCellValue('F' . $rowCount, $item['quantity'])
                ->setCellValue('G' . $rowCount, $item['price'])
                ->setCellValue('H' . $rowCount, $item['price'] * $item['quantity']);
            $totalPrice += $item['price'] * $item['quantity'];
            $rowCount++;
        }

        // Append total price
        $totalPrice = $totalPrice - $discountValue;
        $shippingFee = ($totalPrice < 500000) ? 11000 : 0;
        $totalFinalPrice = $totalPrice + $shippingFee;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('H22', $discountValue)
            ->setCellValue('H23', $shippingFee)
            ->setCellValue('H24', 0)
            ->setCellValue('H25', $totalFinalPrice);

        // save file and return response to client
        header('Content-Type: application/json');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('media/export_price_sheet/' . $today_folder . '/' . $fileName);
        $this->getResponse()->setBody(json_encode(['url' => '/media/export_price_sheet/' . $today_folder . '/' . $fileName]));
    }

    /** Export image **/
    public function exportImageAction()
    {
        $cartItems = json_decode($this->getRequest()->getParam('cartItems'), true);
        $screen = $this->getRequest()->getParam('screen');
        $discountValue = $this->getRequest()->getParam('discountValue');

        if (!file_exists('media/export_price_sheet/images')) {
            mkdir('media/export_price_sheet/images', 0777, true);
        }

        $fileName = "PriceSheet_" . date("d-m-H-i-s", time() + 7 * 60 * 60) . '.png';

        header('Content-Description: File Transfer');
        header('Content-Type: image/png');
        header("Content-disposition: attachment; filename=$fileName");

        $newImage = $this->export($cartItems, $screen, $discountValue);

        imagepng($newImage);
        imagedestroy($newImage);
        die;
    }

    private function export($cartItems, $screen, $discountValue)
    {
        $webRowBaseSize = count($cartItems) <= 9 ? 120 : 70;
        $mobileRowBaseSize = count($cartItems) <= 9 ? 195 : 120;
        $headerSize = 200;
        $width = $screen == 'mobile' ? 993 : 1200;
        $height = $screen == 'mobile' ? ($mobileRowBaseSize * count($cartItems) + $headerSize * 3)
            : ($webRowBaseSize * count($cartItems) + $headerSize * 3);

        $newImage = @imagecreatetruecolor($width, $height) or die("Cannot Initialize new GD image stream");

        $backgroundColor = imagecolorallocate($newImage, 255, 255, 255);
        $logoTextColor = imagecolorallocate($newImage, 42, 51, 122);
        $titleColor = imagecolorallocate($newImage, 0, 0, 0);
        $headerColor = imagecolorallocate($newImage, 235, 236, 238);
        $priceColor = imagecolorallocate($newImage, 65, 64, 62);
        $finalPriceColor = imagecolorallocate($newImage, 212, 35, 51);
        imagefill($newImage, 0, 0, $backgroundColor);

        $totalPrice = 0;
        $rowSize = ($height - $headerSize * 2) / count($cartItems) + 1;
        $rowDraw = $headerSize;
        $columnDraw = 30;
        $padding = $screen == 'mobile' ? 20 : 30;
        $fontSize = $screen == 'mobile' ? 22 : 18;
        $priceFontSize = $screen == 'mobile' ? 24 : 20;
        $logoFontSize = 54;
        $finalFontSize = $screen == 'mobile' ? 32 : 28;
        $shippingFontSize = $screen == 'mobile' ? 26 : 22;
        $textPaddingTop = $screen == 'mobile' ? 30 : 30;
        $textPaddingBottom = $screen == 'mobile' ? 15 : -10;
        $titlePaddingRight = $screen == 'mobile' ? 120 : 30;
        if ($screen == 'web' && count($cartItems) <= 9) {
            $textPaddingBottom += 40;
        }
        if ($screen == 'mobile' && count($cartItems) <= 9) {
            $textPaddingTop += 20;
        }
        $font = Mage::getDesign()->getSkinBaseDir() . '/lib/fonts/Roboto-Medium.ttf';
        $imgSize = $rowSize - $padding;

        // Header
        imagefilledrectangle($newImage, 0, 0, $width, $headerSize - 10, $headerColor);
        $logoUrl = Mage::getDesign()->getSkinUrl('images/logoPV.png');
        $logoImage = imagecreatefrompng($logoUrl);
        $logoInfo = getimagesize($logoUrl);
        $box = imagettfbbox($logoFontSize, 0, $font, 'BÁO GIÁ SẢN PHẨM');
        imagecopy($newImage, $logoImage, ($width - $logoInfo[0]) / 2, 20, 0, 0, $logoInfo[0], $logoInfo[1]);
        imagettftext($newImage, $logoFontSize, 0, ($width - $box[2]) / 2, 100 + $logoInfo[1], $logoTextColor, $font, 'BÁO GIÁ SẢN PHẨM');

        // Loop each item
        foreach ($cartItems as $item) {
            $product = Mage::getModel('catalog/product')->load($item['id']);
            $imageUrl = $product->getSmallImageUrl();
            $bpPrice = number_format($item['price'], 0, ',', '.');
            $bpSumPrice = number_format($item['price'] * $item['quantity'], 0, ',', '.');

            $imageInfo = getimagesize($imageUrl);
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            if ($mimeType == 'image/jpeg') { // include .jpg and .jpeg extensions
                $originalImage = imagecreatefromjpeg($imageUrl);
            } elseif ($mimeType == 'image/png') { // .png extension
                $originalImage = imagecreatefrompng($imageUrl);
            } elseif ($mimeType == 'image/gif') { // .gif extension
                $originalImage = imagecreatefromgif($imageUrl);
            } else {
                continue;
            }

            imagecopyresampled($newImage, $originalImage, $columnDraw, $rowDraw + $padding / 2, 0, 0, $imgSize, $imgSize, $originalWidth, $originalHeight);

            // Explode text by words
            $textTmp = explode(' ', $item['name']);
            $textNew = '';
            foreach ($textTmp as $word) {
                // Create a new text, add the word, and calculate the parameters of the text
                $box = imagettfbbox($fontSize, 0, $font, $textNew . ' ' . $word);
                // If the line fits to the specified width, then add the word with a space, if not then add word with new line
                if ($box[2] > $width - $titlePaddingRight - $rowSize) {
                    $textNew .= "\n" . $word;
                } else {
                    $textNew .= " " . $word;
                }
            }
            // Trip spaces
            $textNew = trim($textNew);

            imagettftext($newImage, $fontSize, 0, $columnDraw + $rowSize + 20, $rowDraw + $textPaddingTop,
                $titleColor, $font, $textNew);

            $nameDimensions = imagettfbbox($fontSize, 0, $font, $textNew);
            $nameHeight = abs($nameDimensions[7] - $nameDimensions[1]) + 20;

            imagettftext($newImage, $priceFontSize, 0, $columnDraw + $rowSize + 20, $rowDraw + $textPaddingTop + $nameHeight,
                $priceColor, $font, $bpPrice . ' đ    x    ' . $item['quantity']);

            // Print total price
            $totalText = '    =    ' . $bpSumPrice . ' đ';
            $dimensions = imagettfbbox($fontSize, 0, $font, $totalText);
            $textWidth = abs($dimensions[4] - $dimensions[0]);
            imagettftext($newImage, $priceFontSize, 0, $width - $padding - $textWidth - 80, $rowDraw + $textPaddingTop + $nameHeight,
                $finalPriceColor, $font, $totalText);
            $totalPrice += $item['price'] * $item['quantity'];

            $rowDraw += $rowSize;
        }

        $totalPrice = $totalPrice - $discountValue;
        $shippingFee = ($totalPrice < 500000) ? 11000 : 0;
        $totalFinalPrice = $totalPrice + $shippingFee;

        // Add discount value
        if ($discountValue > 0) {
            $discountText = 'Giảm giá: ' . number_format($discountValue, 0, ',', '.') . ' đ';
            $dimensions = imagettfbbox($shippingFontSize, 0, $font, $discountText);
            $textWidth = abs($dimensions[4] - $dimensions[0]);
            imagettftext($newImage, $shippingFontSize, 0, ($width - $textWidth) / 2, $rowDraw + ($height - $rowDraw) / 2,
                $priceColor, $font, $discountText);
        }

        // Add shipping fee
        $shippingText = 'Phí vận chuyển: ' . number_format($shippingFee, 0, ',', '.') . ' đ';
        $dimensions = imagettfbbox($shippingFontSize, 0, $font, $shippingText);
        $textWidth = abs($dimensions[4] - $dimensions[0]);
        imagettftext($newImage, $shippingFontSize, 0, ($width - $textWidth) / 2, $rowDraw + ($height - $rowDraw) / 2 + 37,
            $priceColor, $font, $shippingText);

        // Draw total price
        $finalText = 'Tổng chi phí: ' . number_format($totalFinalPrice, 0, ',', '.') . ' đ';
        $dimensions = imagettfbbox($finalFontSize, 0, $font, $finalText);
        $textWidth = abs($dimensions[4] - $dimensions[0]);
        imagettftext($newImage, $finalFontSize, 0, ($width - $textWidth) / 2, $rowDraw + ($height - $rowDraw) / 2 + 80,
            $finalPriceColor, $font, $finalText);

        // Adding watermark
        $resizePercent = 0.7;
        $logoUrl = Mage::getDesign()->getSkinUrl('images/logo-export.png');
        $logoImage = imagecreatefrompng($logoUrl);
        $logoInfo = getimagesize($logoUrl);
        $stamp = imagecreatetruecolor($logoInfo[0] * $resizePercent, $logoInfo[1] * $resizePercent);
        imagefilledrectangle($stamp, 0, 0, imagesx($stamp), imagesy($stamp), $backgroundColor);
        imagecopyresampled($stamp, $logoImage, 0, 0, 0, 0, imagesx($stamp), imagesy($stamp), $logoInfo[0], $logoInfo[1]);
        imagecopymerge($newImage, $stamp, ($width - imagesx($stamp)) / 2, ($height - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp), 7);

        return $newImage;
    }
    /** End Export price sheet functions */
}
