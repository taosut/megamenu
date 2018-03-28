<?php

class Ved_Compare_IndexController extends Mage_Core_Controller_Front_Action
{

    public function compareAction()
    {
        $params =$this->getRequest()->getParams();
        $localStorage = $params["data"];
        $params = json_decode(base64_decode($params["data"]),true);

        if($params) {
            $storeCode = Mage::app()->getStore()->getId();
            $compareList = $params['listItem'];
            $atrributeSet = $params['attributeCode'];
            if (count($compareList) <= 5) {
                $product = Mage::getModel('catalog/product')
                    ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                    ->getCollection()
                    ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                    ->addUrlRewrite()
                    ->addStoreFilter($storeCode)
                    ->addAttributeToFilter('entity_id', array('in' =>
                        $compareList));
                $productModel = Mage::getModel('catalog/product');
                $loadedProduct = array();
                foreach ($product->getItems() as $item) {
                    $current = $productModel->load($item->getId());
                    $data = $this->getAdditionalData($current);
                    array_push($loadedProduct, $data);
                }
//                $atrributes = array();
//                $label = array();
//                foreach ($loadedProduct as $load){
//                    $currentKeys = array_keys($load);
//                    foreach ($currentKeys as $key){
//                        if(!in_array($key,$atrributes))
//                        {
//                            array_push($atrributes,$key);
//                            array_push($label,$load[$key]['label']);
//                        }
//                    }
//                }
//                Mage::register('attributeKey',$atrributes);
                Mage::register('product', $product);
                Mage::register('attribute_set_id', $atrributeSet);
                Mage::register('loadedProduct', $loadedProduct);
                Mage::register('localStorage',$localStorage);
                $this->loadLayout();
                //$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('compare/index'));
                $this->renderLayout();
            } else {
                var_dump("Chỉ so sánh tối đa 5 sản phẩm");
            }
        }
        else
        {
            var_dump("Danh sách sản phẩm rỗng");
        }
    }

    /* Test Action for advanced compare */
    public function testAction()
    {
        $CATARRAY = ['553', '552', '548', '636', '637', '554', '541', '549', '555', '540', '543', '542', '544','545','546','547','551','550','621','622','556','557','558','559','560','561','539','538','536'];
        // 556 557 558 559 560 561
        //$params =$this->getRequest()->getParams();
        $params = 1;
        if ($params) {
            $storeCode = Mage::app()->getStore()->getId();
            // $compareList = $params['data'];
            $compareList = array([299, 302, 305, 330, 781]);
            //542,
            if (count($compareList) <= 5) {
                $products =
                    Mage::getModel('catalog/product')
                        ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                        ->getCollection()
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addUrlRewrite()
                        ->addStoreFilter($storeCode)
                        ->addAttributeToFilter('entity_id', array('in' =>
                            $compareList));
                $productArray = $products->getItems();
                $compareProduct = array();
                // refine the unique category id
                foreach ($productArray as $item) {
                    $Catids = array_reverse($item->getCategoryIds());
                    foreach ($Catids as $cat) {
                        if (in_array($cat,$CATARRAY)) {
                        array_push($compareProduct, [
                            'catId' => $cat,
                            'product' => $item
                        ]);
                            break;
                        }
                    }
                }
                // group category id
                $value = array();
                $key = array();
                $mark = array_fill(0,count($compareProduct),false);
                for ($i = 0; $i < count($compareProduct); $i++) {
                    if(!$mark[$i]){
                        $current = $compareProduct[$i]['catId'];
                        array_push($key, $current);
                        $currentCatItem = array();
                        array_push($currentCatItem,$compareProduct[$i]['product']->getId());
                        for ($j = $i+1; $j < count($compareProduct); $j++) {
                            if ($current == $compareProduct[$j]['catId'] && !$mark[$j]) {
                                array_push($currentCatItem, $compareProduct[$j]['product']->getId());
                                $mark[$j] = true;
                            }
                        }
                        array_push($value,$currentCatItem);
                    }
                }
                if(count($key) == count($value)){
                    $result = array_combine($key,$value);
                    var_dump($result);

                }else{

                    var_dump("Có lỗi khi tách danh mục, vui lòng chọn cùng loại sản phẩm");
                }
            } else {
                var_dump(" Chỉ so sánh tối đa 5 sản phẩm");
            }
        }else{
            var_dump("Danh sách so sánh rỗng");
        }

    }
    public function getAdditionalData($sampleProduct)
    {
        $excludeAttr = array();
        $data = array();
        $product = $sampleProduct;
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
//            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = Mage::helper('catalog')->__('N/A');
                } elseif ((string)$value == '') {
                    $value = Mage::helper('catalog')->__('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = Mage::app()->getStore()->convertPrice($value, true);
                }

                if (is_string($value) && strlen($value)) {
                    $data[$attribute->getAttributeCode()] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code'  => $attribute->getAttributeCode()
                    );
                }
            }
        }
        return $data;
    }

    public function mixAction(){
        //$params =$this->getRequest()->getParams();
        //$localStorage = $params["data"];
        //$params = json_decode(base64_decode($params["data"]),true);
        $params = true;
        if($params){
            $storeCode = Mage::app()->getStore()->getId();
            //$compareList = $params['listItem'];
            //var_dump($compareList);
            $compareList = array([299, 337, 670, 802, 1001]);
            if (count($compareList) <= 5) {
                $product = Mage::getModel('catalog/product')
                    ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                    ->getCollection()
                    ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                    ->addUrlRewrite()
                    ->addStoreFilter($storeCode)
                    ->addAttributeToFilter('entity_id', array('in' =>
                        $compareList));
                $productModel = Mage::getModel('catalog/product');
                $loadedProduct = array();
                foreach ($product->getItems() as $item) {
                    $current = $productModel->load($item->getId());
                    $data = $this->getAdditionalData($current);
                    array_push($loadedProduct, $data);
                }
                Mage::register('loadedProduct', $loadedProduct);
                $this->loadLayout();
                $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('compare/index'));
                $this->renderLayout();
            } else {
                var_dump("Chỉ so sánh tối đa 5 sản phẩm");
            }
        }else{
            var_dump('Danh sách sản phẩm rỗng');
        }
    }
}