<?php
/**
 * FAQ accordion for Magento
 *

 * @copyright  Copyright (c) 2010 TV GmbH & Co. KG <magento@tv.de>
 */

/**
 * Category Model for FAQ Items
 *
 * Website: www.abc.com 
 * Email: honeyvishnoi@gmail.com
 */
class TV_Faq_Model_Category extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('tv_faq/category');
    }
    
    public function getName()
    {
        return $this->getCategoryName();
    }
    
    public function getItemCollection()
    {
        $collection = $this->getData('item_collection');
        if (is_null($collection)) {
            $collection = Mage::getSingleton('tv_faq/faq')->getCollection()
                ->addCategoryFilter($this);
            $this->setData('item_collection', $collection);
        }
        return $collection;
    }
}
