<?php
 
class Ved_Vandon_Block_Adminhtml_Confirmvd extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        $this->_blockGroup = 'ved_vandon';
        $this->_controller = 'adminhtml_sales_order';
        $this->_headerText = Mage::helper('ved_vandon')->__('Thong tin don hang');

        parent::__construct();

    }
    
    public function getCreateUrl()
    {
        return $this->getUrl('*/sales_order_create/start');
    }
}