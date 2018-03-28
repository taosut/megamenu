<?php

/**
 * Created by PhpStorm.
 * User: Linh
 * Date: 3/13/2017
 * Time: 5:43 PM
 * @property bool|Mage_Core_Model_Abstract|mixed _faq
 * @property TV_Faq_Model_Category _faqCategory
 */
class Ved_Customercare_Block_List extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Customer Care Question List');
        $this->setTemplate('customercare/listqa.phtml');
    }

    /**
     * Function to gather the current faq item
     *
     * @return TV_Faq_Model_Faq The current faq item
     */
    public function getFaq()
    {
        if (!$this->_faq) {
            $id = intval($this->getRequest()->getParam('faq'));
            try {
                $this->_faq = Mage:: getModel('tv_faq/faq')->load($id);

                if ($this->_faq->getIsActive() != 1) {
                    Mage::throwException('Faq Item is not active');
                }
            } catch (Exception $e) {
                $this->_faq = false;
            }
        }

        return $this->_faq;
    }

    /**
     * @return TV_Faq_Model_Category The current faq item
     *
     *
     * @throws Exception
     */
    public function getFaqCategory()
    {
        if (!$this->_faqCategory) {
            $id = intval($this->getRequest()->getParam('faq_category'));
            try {
                $this->_faqCategory = Mage:: getModel('tv_faq/category')->load($id);

                if ($this->_faqCategory->getIsActive() != 1) {
                    Mage::throwException('Faq Item is not active');
                }
            } catch (Exception $e) {
                $this->_faqCategory = false;
            }
        }

        return $this->_faqCategory;
    }

    public function getHeader()
    {
        if ($this->isQuery()) {
            return "Kết quả tìm kiếm";
        }

        return $this->_faqCategory ? $this->_faqCategory->getName() : null;
    }

    public function getItems()
    {
        if ($this->isQuery()) {
            $collection = $this->getData('item_collection');
            if (is_null($collection)) {
                $collection = Mage::getSingleton('tv_faq/faq')->getCollection()
                    ->addQueryNameFilter(trim($this->getRequest()->getParam('query')));
                $this->setData('item_collection', $collection);
            }
            return $collection;
        }
        return $this->_faqCategory->getItemCollection();
    }

    public function isQuery()
    {
        return $this->getRequest()->has('query');
    }
}