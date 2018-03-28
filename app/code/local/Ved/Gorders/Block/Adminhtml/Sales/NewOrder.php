<?php
 
class Ved_Gorders_Block_Adminhtml_Sales_NewOrder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ved_gorders';
        $this->_controller = 'adminhtml_sales_NewOrder';
        $this->_headerText = Mage::helper('ved_gorders')->__('Orders - New');
        $this->_addButtonLabel = Mage::helper('sales')->__('Create New Order');
        parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->_removeButton('add');
        }
    }
    
    public function getCreateUrl()
    {
        return $this->getUrl('*/sales_order_create/start');
    }
}