 <?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 3/18/2016
 * Time: 12:56 AM
 */
require_once(Mage::getModuleDir('','Mage_Checkout').DS.'/Block/Cart.php');
class Ved_Checkout_Block_Cart_Panel extends Mage_Core_Block_Template{
    public function getCartInfo(){
        $cartQty = $this->helper('checkout/cart')->getSummaryCount();

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $shipping_address = $quote->getShippingAddress();

        $shipping_amount = -1;
        if(isset($shipping_address)){
            $samount = $shipping_address->getShippingAmount();
            if(isset($samount) && !empty($samount) && ($samount >0)){
                $shipping_amount = $samount;
            }
        }

        $quoteData= $quote->getData();
        $totalDiscount = 0;


        $grandTotal = 0;
        $products = array();
        $cartItems = $quote->getAllVisibleItems();
        foreach ($cartItems as $item) {
            $productid = $item->getProductId();
            $grandTotal += $item->getPriceInclTax()*$item->getQty();
            $totalDiscount += $item->getDiscountAmount();
            $product = Mage::getModel('catalog/product')->load($productid);

            $productMediaConfig = Mage::getModel('catalog/product_media_config');
            $thumbnailUrl = $productMediaConfig->getMediaUrl($product->getThumbnail());
            $p = array(
                'id'=>$productid,
                'name'=>$item->getName(),
                'sku'=>$item->getSku(),
                'quantity'=>$item->getQty(),
                'price'=>$item->getPrice(),
                'grand_total'=>$item->getPriceInclTax()*$item->getQty(),
                'url'=>$product->getProductUrl(),
                'image' => $thumbnailUrl,
                'old_price' => $product->getPrice(),
                'new_price' => $product->getFinalPrice()
            );
            $products[]=$p;
        }

        if($shipping_amount == -1){
            $cod = $grandTotal - $totalDiscount ;
        }
        else{
            $cod = $grandTotal - $totalDiscount + $shipping_amount;
        }

        return array(
            'cart_qty'=>$cartQty,
            'products'=>$products,
            'total_discount'=>$totalDiscount,
            'grand_total'=>$grandTotal,
            'shipping_amount'=>$shipping_amount,
            'cod'=>$cod
        );
    }

    public function getCartInfoMoblie(){
        $cartQty = $this->helper('checkout/cart')->getSummaryCount();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $shipping_address = $quote->getShippingAddress();

        $shipping_amount = -1;
        if(isset($shipping_address)){
            $samount = $shipping_address->getShippingAmount();
            if(isset($samount) && !empty($samount) && ($samount >0)){
                $shipping_amount = $samount;
            }
        }

        $quoteData= $quote->getData();
        $totalDiscount = 0;


        $grandTotal = 0;
        $products = array();
        $cartItems = $quote->getAllVisibleItems();

        foreach ($cartItems as $item) {
            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            $customOptions = $options['options'];
            $options = [];
            if (!empty($customOptions)) {
                foreach ($customOptions as $key => $option) {
//                    $opt = Mage::getModel('catalog/product_option')->load($option['option_id']);
                    $options[$key]['label'] = $option['label'];
                    $options[$key]['option_id'] = $option['option_id'];
                    $options[$key]['type'] = $option['type'];
                    $options[$key]['value'] = $option['value'];
                }
            }

            $productid = $item->getProductId();
            $grandTotal += $item->getPriceInclTax()*$item->getQty();
            $totalDiscount += $item->getDiscountAmount();
            $product = Mage::getModel('catalog/product')->load($productid);

            $productMediaConfig = Mage::getModel('catalog/product_media_config');
            $thumbnailUrl = $productMediaConfig->getMediaUrl($product->getThumbnail());
            $p = array(
                'id'=>$productid,
                'name'=>$item->getName(),
                'sku'=>$item->getSku(),
                'quantity'=>$item->getQty(),
                'price'=>$item->getPrice(),
                'grand_total'=>$item->getPriceInclTax()*$item->getQty(),
                'url'=>$product->getProductUrl(),
                'image' => $thumbnailUrl,
                'old_price' => $product->getPrice(),
                'new_price' => $item->getPrice(),
                'options' => $options
            );
            $products[]=$p;
        }

        if($shipping_amount == -1){
            $cod = $grandTotal - $totalDiscount ;
        }
        else{
            $cod = $grandTotal - $totalDiscount + $shipping_amount;
        }

        return array(
            'cart_qty'=>$cartQty,
            'products'=>$products,
            'total_discount'=>$totalDiscount,
            'grand_total'=>$grandTotal,
            'shipping_amount'=>$shipping_amount,
            'cod'=>$cod
        );
    }
} 