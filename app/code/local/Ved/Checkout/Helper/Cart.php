<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 3/18/2016
 * Time: 1:13 AM
 */
require_once(Mage::getModuleDir('Helper','Mage_Checkout').DS.'Helper'.DS.'Cart.php');
class  Ved_Checkout_Helper_Cart extends  Mage_Checkout_Helper_Cart{
    public function getHomeAddUrl($product, $additional = array())
    {
        $routeParams = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->_getHelperInstance('core')
                ->urlEncode($this->getCurrentUrl()),
            'product' => $product->getEntityId(),
            Mage_Core_Model_Url::FORM_KEY => $this->_getSingletonModel('core/session')->getFormKey()
        );

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product->hasUrlDataObject()) {
            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_store_to_url'] = true;
        }

        if ($this->_getRequest()->getRouteName() == 'checkout'
            && $this->_getRequest()->getControllerName() == 'cart') {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('checkout/cart/homeAdd', $routeParams);
    }
} 