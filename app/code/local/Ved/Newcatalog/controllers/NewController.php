<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 11/22/2017
 * Time: 1:48 PM
 */

class Ved_Newcatalog_NewController extends Mage_Core_Controller_Front_Action
{
    public function getCatProductsAction()
    {
        $catId = $this->getRequest()->getParam('catId');
        $_category = Mage::getModel('catalog/category')->load($catId);

        $todayStartOfDayDate = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $_products = $_category->getProductCollection()
            ->addAttributeToFilter('news_from_date', array('or' => array(
                0 => array('date' => true, 'to' => $todayEndOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter('news_to_date', array('or' => array(
                0 => array('date' => true, 'from' => $todayStartOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'news_from_date', 'is' => new Zend_Db_Expr('not null')),
                    array('attribute' => 'news_to_date', 'is' => new Zend_Db_Expr('not null'))
                )
            )
            ->addAttributeToSort('news_from_date', 'desc')
            ->addAttributeToFilter('price', array('gt' => 0))
            ->addAttributeToFilter('instock', array('in' => array(1, 6)))
            ->setPageSize(8)->setCurPage(1);
        Mage::getModel('catalog/layer')->prepareProductCollection($_products);

        Mage::register('cat_products', $_products);

        $catProductsBlock = $this->getLayout()->createBlock('newcatalog/catproducts');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(['cat_products' => $catProductsBlock->toHtml(), 'cat_products_count' => count($_products)]));
    }
}