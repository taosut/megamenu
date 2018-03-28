<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/15/2016
 * Time: 11:00 AM
 */

class Ved_SupplierNew_Block_Catalog_Renderer_Sold extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
        $productId = $this->getColumn()->getIndex();

        // Your code
        $itemsCollection= Mage::getResourceModel('sales/order_item_collection')
            ->join('order', 'order_id=entity_id')
            ->addAttributeToFilter('product_id', array('eq' => $productId));
        // sum qty ordered - the canceled qty as it's not really sold if it's canceled.
        // refunded also is debatable
        $itemsCollection->addExpressionFieldToSelect("total_sold" , 'SUM({{qty_ordered}} - {{qty_canceled}})',array("qty_ordered" => "qty_ordered",
            "qty_canceled" => "qty_canceled"));
        $items = $itemsCollection->getSelect()->group("product_id"); // make sure we

        foreach($items as $item) {
            return  $item->getTotalSold();
        }
        return 10;
    }
} 