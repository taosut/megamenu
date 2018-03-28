<?php
 
class Ved_Crosscheck_Block_Adminhtml_OverdueOrder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ved_crosscheck';
        $this->_controller = 'adminhtml_OverdueOrder';
        $this->_removeButton('add');
        //$this->_headerText = Mage::helper('crosscheck')->__('Orders - Over Due');
        parent::__construct();
        $this->setTemplate('crosscheck/overdue.phtml');

    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('ved_crosscheck/Adminhtml_OverdueOrder_Grid', 'ved_overdue_grid'));
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