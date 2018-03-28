<?php
class Ved_Vandon_Block_Adminhtml_Confirmvd_Confirm extends Mage_Adminhtml_Block_Abstract
{
    public function __construct()
    {
        die('aaaaaaa');
        parent::__construct();
    }
    
        //use to get Url in confirm shipment 
    public function getSaveUrl($order_id)
    {
        return $this->getUrl('*/sales_order_shipment/sendconfirm', array('order_id' => $order_id));
    }
    
    public function getBackUrl($order_id)
    {
        return $this->getUrl('*/sales_order/view', array('order_id' => $order_id));
    }
}
?>