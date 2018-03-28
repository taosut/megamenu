<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/6/2016
 * Time: 2:59 PM
 */
class Ved_Purchase_Block_Adminhtml_Purchase extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Set template
     */
//    public function __construct()
//    {
//        vả
//        $this->_blockGroup = 'ved_productqc';
//        //$this->_controller = 'adminhtml_catalog_ProductQc';
//        $this->_headerText = Mage::helper('catalog')->__('QC Product');
//        //$this->_addButtonLabel = Mage::helper('sales')->__('Create New Order');
//        parent::__construct();
////        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
////            $this->_removeButton('add');
////        }
//    }
//
//    public function getCreateUrl()
//    {
//        return $this->getUrl('*/sales_order_create/start');
//    }

    public function __construct()
    {
        //var_dump(1);die();
        parent::__construct();
        //$this->_headerText = Mage::helper('ved_purchase')->__('Manage Purchase');
        $this->setTemplate('purchase/purchase.phtml');
    }

    /**
     * Prepare button and grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Product
     */
    protected function _prepareLayout()
    {
//        $this->_addButton('add_new', array(
//            'label'   => Mage::helper('catalog')->__('Add Product'),
//            'onclick' => "setLocation('{$this->getUrl('*/*/new')}')",
//            'class'   => 'add'
//        ));

        $this->setChild('grid', $this->getLayout()->createBlock('ved_purchase/Adminhtml_Purchase_Grid', 'ved_purchase_grid'));
        return parent::_prepareLayout();
    }


    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            return false;
        }
        return true;
    }
}
