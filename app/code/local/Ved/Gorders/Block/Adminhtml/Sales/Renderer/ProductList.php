<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/15/2016
 * Time: 11:00 AM
 */

class Ved_Gorders_Block_Adminhtml_Sales_Renderer_ProductList extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $orderid =  $row->getData($this->getColumn()->getIndex());
        $order = Mage::getModel('sales/order')->load($orderid);
        $items = $order->getAllVisibleItems();
        $html = "<table>";
        foreach($items as $item){
            $html.="<tr><td>".intval($item->getQtyOrdered())." X </td><td>". $item->getName()."</td></tr>";
        }
        $html.= "</table>";

        return $html;
    }
		
		public function renderExport(Varien_Object $row)
    {
        $orderid =  $row->getData($this->getColumn()->getIndex());
        $order = Mage::getModel('sales/order')->load($orderid);
        $items = $order->getAllVisibleItems();
        $html = "";
        foreach($items as $item){
            $html.=intval($item->getQtyOrdered())." X ". $item->getSku().' - '.$item->getName().";\n";
        }
        $html.= "";
        return $html;
    }
} 