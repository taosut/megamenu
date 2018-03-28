<?php

class Ved_Gorders_Block_Adminhtml_Sales_Order_View_Items extends Mage_Adminhtml_Block_Sales_Order_View_Items
{
    private $_stock;

    function __construct()
    {
        $this->addItemRender('default', 'ved_gorders/adminhtml_sales_order_view_items_renderer_product', 'sales/order/view/items/renderer/default.phtml');

        $this->_stock = Mage::getModel('ved_gorders/orderitemstock')
            ->getCollection()
            ->addFieldToFilter('order_id', $this->getRequest()->get('order_id'))
            ->load();
        parent::__construct();
    }


    function getItemHtml(Varien_Object $item)
    {

        return parent::getItemHtml($item);

    }

    public function getStock()
    {
        return $this->_stock;
    }
}