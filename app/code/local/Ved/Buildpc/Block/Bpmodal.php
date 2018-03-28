<?php

/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 7/17/2017
 * Time: 10:08 AM
 */
class Ved_Buildpc_Block_Bpmodal extends Mage_Catalog_Block_Product_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/view/bp_modal.phtml');
//        $this->itemArray = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $category = Mage::getModel('catalog/category')->load(Mage::registry('catId'));
        $catProducts = $category->getProductCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('name', array(
                array('like' => '%'.Mage::registry('search').'%')
            ))
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', 4)
            ->setPageSize(12)
            ->setCurPage(Mage::registry('page'));

        $pager = $this->getLayout()->createBlock('page/html_pager', 'buildpc.pager')
            ->setCollection($catProducts); //call your own collection getter here, name it something better than getCollection, please; *or* your call to getResourceModel()
        $this->setChild('pager', $pager);
        return $this;
    }

    public function getCatUrl($catId) {
        return Mage::getModel('catalog/category')->load($catId)->getUrl();
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function wsGetFilter($categoryid){

        $layer = Mage::getModel("catalog/layer");

        $category = Mage::getModel("catalog/category")->load($categoryid);
        $layer->setCurrentCategory($category);
        $attributes = $layer->getFilterableAttributes();
        $filter = array();
        $count = 0;
        foreach ($attributes as $attribute)
        {
            if ($attribute->getAttributeCode() == 'price') {
                $filterBlockName = 'catalog/layer_filter_price';
            } elseif ($attribute->getBackendType() == 'decimal') {
                $filterBlockName = 'catalog/layer_filter_decimal';
            } else {
                $filterBlockName = 'catalog/layer_filter_attribute';
            }
            $filter[$count]["code"] = $attribute->attribute_code;
            $filter[$count]["type"] =  $attribute->frontend_input;
            $filter[$count]["label"] =  $attribute->frontend_label;
            $result = Mage::app()->getLayout()->createBlock($filterBlockName)->setLayer($layer)->setAttributeModel($attribute)->init();
            $innercount = 0;
            $filter_data = array();
            foreach($result->getItems() as $option) {
                $filter_data[$innercount]["count"] =  $option->getCount();
                $filter_data[$innercount]["label"] =  $option->getLabel();
                $filter_data[$innercount]["id"] =  $option->getValue();
                $filter_data[$innercount]["url"] =  $option->getUrl();
                $innercount++;
            }
            $filter[$count]["values"] = $filter_data;
            $count++;
        }
        return $filter;
    }

}