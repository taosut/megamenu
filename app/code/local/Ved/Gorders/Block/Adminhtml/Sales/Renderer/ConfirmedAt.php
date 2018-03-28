<?php
/**
 * Created by PhpStorm.
 * User: tranlinh
 * Date: 7/11/2016
 * Time: 5:50 PM
 */

class Ved_Gorders_Block_Adminhtml_Sales_Renderer_ConfirmedAt extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $orderid =  $row->getData($this->getColumn()->getIndex());
        $order = Mage::getModel('sales/order')->load($orderid);
        $statuses = $order->getStatusHistoryCollection();
        foreach($statuses as $status){
            if($status->getStatus() == 'telephone_confirm'){
                /**
                 * @var Zend_Date $date;
                 */
                $date = $status->getCreatedAtStoreDate();
                return $date->toString('H:m:s d-M-Y');
            }
        }
        return "";
    }

    public function renderExport(Varien_Object $row)
    {
        $orderid =  $row->getData($this->getColumn()->getIndex());
        $order = Mage::getModel('sales/order')->load($orderid);
        $statuses = $order->getStatusHistoryCollection();
        foreach($statuses as $status){
            if($status->getStatus() == 'telephone_confirm'){
                /**
                 * @var Zend_Date $date;
                 */
                $date = $status->getCreatedAtStoreDate();
                return $date->toString('H:m:s d-M-Y');
            }
        }
        return "";
    }
}