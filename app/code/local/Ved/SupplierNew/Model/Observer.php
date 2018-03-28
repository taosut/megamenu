<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/20/2016
 * Time: 5:02 PM
 */

class Ved_SupplierNew_Model_Observer  {
    public function detectProductChanges($observer) {
//        Mage::log('detectProductChanges even');
//        $product = $observer->getEvent()->getProduct();
//        if ($product->hasDataChanges())
//        {
//            $productid = $product->getData('entity_id');
//            $supplier_price = $product->getData('supplier_price');
//            $orig_supplier_price = $product->getOrigData('supplier_price');
//            if ($supplier_price != $orig_supplier_price) {
////                $metaDescription = "Supplier Price change from: ".$orig_supplier_price." to ".$supplier_price;
////                $product->setMetaDescription($metaDescription);
//                $user = Mage::getSingleton('admin/session')->getUser();
//                if(isset($user)){
//                    $change = Mage::getModel('suppliernew/pricechange');
//										Mage::log($change, null, 'price_changes.log', true);
//                    $change->product_id = $productid;
//                    $change->old_price = $orig_supplier_price;
//                    $change->new_price = $supplier_price;
//                    $change->user_id = ($user) ? $user->getId() : NULL;
//
//                    date_default_timezone_set('Asia/Ho_Chi_Minh');
//                    $change->created = date("Y-m-d H:i:s",time());
//                    $change->save();
//                }
//            }
//        }


        return $this;
    }
    public function detectProductAttributeChanges($observer)
    {
//        Mage::log("detectProductAttributeChanges even");
//        $attributesData = $observer->getEvent()->getAttributesData();
//        $productIds     = $observer->getEvent()->getProductIds();
//
//        $user  = Mage::getSingleton('admin/session')->getUser();
//        foreach ($productIds as $id) {
//            $change             = Mage::getModel('suppliernew/pricechange');
//            $change->product_id = $id;
//            $change->new_values = print_r($attributesData, true);
//            $change->user_id    = ($user) ? $user->getId() : NULL;
//            $change->created    = now();
//            $change->save();
//        }
        return $this;
    }

} 