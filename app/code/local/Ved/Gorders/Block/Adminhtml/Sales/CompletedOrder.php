<?php
 
class Ved_Gorders_Block_Adminhtml_Sales_CompletedOrder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ved_gorders';
        $this->_controller = 'adminhtml_sales_CompletedOrder';
        $this->_headerText = Mage::helper('ved_gorders')->__('Orders - Completed');
        $this->_addButtonLabel = Mage::helper('sales')->__('Create New Order');
        parent::__construct();
        $this->_removeButton('add');
    }
    
    public function getCreateUrl()
    {
        return $this->getUrl('*/sales_order_create/start');
    }
}