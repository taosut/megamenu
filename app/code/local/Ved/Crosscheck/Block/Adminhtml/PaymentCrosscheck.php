<?php
 
class Ved_Crosscheck_Block_Adminhtml_PaymentCrosscheck extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ved_crosscheck';
        $this->_controller = 'adminhtml_PaymentCrosscheck';
        $this->_removeButton('add');
        //$this->_headerText = Mage::helper('crosscheck')->__('Orders - Over Due');
        parent::__construct();
        $this->setTemplate('crosscheck/payment.phtml');

    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('ved_crosscheck/Adminhtml_PaymentCrosscheck_Grid', 'ved_crosscheck_grid'));
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

    public function getAllowAmount(){
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $helper = Mage::helper("crosscheck");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);

        $collection = Mage::getModel("ved_crosscheck/paymentcrosscheck")->getCollection()
            ->addFieldToFilter('status', array('neq' => 0))
            ->addFieldToFilter('main_table.website_id', array_merge(array(-1), $websiteIds))
            ->addExpressionFieldToSelect('total_allow_amount',
                new Zend_Db_Expr('sum(total_amount - total_imported_amount)'),
                []);
        $collection->getSelect()->group(array('main_table.store_id'));
        $collection->getSelect()
            ->joinLeft(
                array('store' => $collection->getTable('core/store')),
                'store.store_id = main_table.store_id', array('store.name as store_name', 'store.code as store_code'));
        //var_dump((string) $collection->getSelect());die();
        return $collection->getData();
    }

}