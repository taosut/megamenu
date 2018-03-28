<?php
/**
 * Created by PhpStorm.
 * User: tranlinh
 * Date: 7/11/2016
 * Time: 5:50 PM
 */

class Ved_Gorders_Block_Adminhtml_Sales_Renderer_Comments extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $orderid =  $row->getData($this->getColumn()->getIndex());
        $order = Mage::getModel('sales/order')->load($orderid);
        $history = $order->getStatusHistoryCollection()->getFirstItem();
        return $history->getComment();
        //var_dump($history);die();
//        $html = "<table>";
//        foreach($history as $item){
//            if($item->getComment()){
//                $html.="<tr><td>".$item->getComment()."</td></tr>";
//            }
//        }
//        $html.= "</table>";
//        return $html;
        // return '<span style="color:red;">'.$value.'</span>';
    }

    public function renderExport(Varien_Object $row)
    {
        $orderid =  $row->getData($this->getColumn()->getIndex());
        $order = Mage::getModel('sales/order')->load($orderid);
        $history = $order->getStatusHistoryCollection(true);
        $html = "";
        foreach($history as $item){
            if($item->getComment()) {
                $html .= $item->getComment() . ";\n";
            }
        }
        $html.= "";
        return $html;
    }
}