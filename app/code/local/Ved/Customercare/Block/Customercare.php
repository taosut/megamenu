<?php

/**
 * Created by PhpStorm.
 * User: Linh
 * Date: 3/13/2017
 * Time: 4:59 PM
 */
class Ved_Customercare_Block_Customercare extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Customer Care');
        $this->setTemplate('customercare/index.phtml');
    }

    /**
     * Returns all active categories
     *
     * @return TV_Faq_Model_Mysql4_Category_Collection
     */
    public function getCategoryCollection()
    {
        $categories = $this->getData('category_collection');
        if (is_null($categories)) {
            $categories = Mage::getResourceSingleton('tv_faq/category_collection')
                ->addStoreFilter(Mage::app()->getStore())
                ->addIsActiveFilter();
            $this->setData('category_collection', $categories);
        }
        return $categories;
    }
}