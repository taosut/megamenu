<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11/28/16
 * Time: 11:30
 */

class Ved_Productqc_Block_Adminhtml_Catalog_Renderer_ProductImage extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $productid =  $row->getData($this->getColumn()->getIndex());
        $product = Mage::getModel('catalog/product')->load($productid);
        $html = '<ul class="enlarge"> ';
        foreach ($product->getMediaGalleryImages() as $image) {

            $html .= '<li><img width="100px" height="100px" src="' . $image->getUrl() . '"/> 
            <span><img width="400px" src="' . $image->getUrl() . '"/> </span> </li>';
        }

        $html .= '</ul> ';
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