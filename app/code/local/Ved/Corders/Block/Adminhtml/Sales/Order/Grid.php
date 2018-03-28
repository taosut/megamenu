<?php
 
class Ved_Corders_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ved_corders_grid');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_grid_collection');
        //$collection
//                ->join(array('oi'=>'sales/order'), 'oi.entity_id=main_table.entity_id AND oi.status="complete"'
//                            , array('qty_ordered' => 'oi.total_qty_ordered', 'ncp' => 'main_table.pl' , 'cp_status' => 'main_table.shipping_status'), null,'left')
//                ->join(array('shipping' => 'sales/order_address'),'main_table.entity_id = shipping.parent_id AND shipping.address_type != "billing"', array(
//                            'shipping_telephone'       => 'shipping.telephone'
//                        ), null,'left')
//                ->join(array('billing' => 'sales/order_address'),'main_table.entity_id = billing.parent_id AND billing.address_type = "billing"', array(
//                            'billing_telephone'       => 'billing.telephone'
//                        ), null,'left')                
//            ; 

        $collection
                ->join(array('oi'=>'sales/order'), 'oi.entity_id=main_table.entity_id AND (oi.status="complete" AND oi.shipping_id in (4,5,6))'
                     , array('qty_ordered' => 'oi.total_qty_ordered' ,'dvcp' => 'oi.pl', 'ttcp' => 'oi.shipping_status' ), null,'left')
                ->join(array('shipping' => 'sales/order_address'),'main_table.entity_id = shipping.parent_id AND shipping.address_type != "billing"', array(
                            'shipping_telephone'       => 'shipping.telephone'
                        ), null,'left')
                ->join(array('billing' => 'sales/order_address'),'main_table.entity_id = billing.parent_id AND billing.address_type = "billing"', array(
                            'billing_telephone'       => 'billing.telephone'
                        ), null,'left')

                
            ; 
     
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
        $helper = Mage::helper('ved_corders');
        $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
 
        $this->addColumn('increment_id', array(
            'header' => $helper->__('Order #'),
            'index'  => 'increment_id'
        ));
 
        $this->addColumn('purchased_on', array(
            'header' => $helper->__('Purchased On'),
            'type'   => 'datetime',
            'index'  => 'created_at'
        ));
 
        //$this->addColumn('products', array(
//            'header'       => $helper->__('Products Purchased'),
//            'index'        => 'products',
//            'filter_index' => '(SELECT GROUP_CONCAT(\' \', x.name) FROM sales_flat_order_item x WHERE main_table.entity_id = x.order_id AND x.product_type != \'configurable\')'
//        ));
 
        //$this->addColumn('fullname', array(
//            'header'       => $helper->__('Name'),
//            'index'        => 'fullname',
//            'filter_index' => 'CONCAT(customer_firstname, \' \', customer_lastname)'
//        ));
// 
//        $this->addColumn('city', array(
//            'header' => $helper->__('City'),
//            'index'  => 'city'
//        ));
// 
//        $this->addColumn('country', array(
//            'header'   => $helper->__('Country'),
//            'index'    => 'country_id',
//            'renderer' => 'adminhtml/widget_grid_column_renderer_country'
//        ));
// 
//        $this->addColumn('customer_group', array(
//            'header' => $helper->__('Customer Group'),
//            'index'  => 'customer_group_code'
//        ));
 
         $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));
        
        $this->addColumn('billing_telephone', array(
            'header' => Mage::helper('sales')->__('Billing Telephone'),
            'index' => 'billing_telephone',
            'type'  => 'text'
        ));
        
        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));
 
        $this->addColumn('shipping_telephone', array(
            'header' => Mage::helper('sales')->__('Shipping Telephone'),
            'index' => 'shipping_telephone',
            'type'  => 'text'
        ));
        
        $this->addColumn('qty_ordered', array(
            'header'    => Mage::helper('sales')->__('Items Ordered'),
            'index'     => 'qty_ordered',
            'type'      => 'number',
            'total'     => 'sum'
        ));
 
 
        //$this->addColumn('base_grand_total', array(
//            'header' => Mage::helper('sales')->__('G.T. (Base)'),
//            'index' => 'base_grand_total',
//            'type'  => 'currency',
//            'currency' => 'base_currency_code',
//        ));
 
        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
        
        $this->addColumn('dvcp', array(
            'header' => Mage::helper('sales')->__('Đơn vị CP'),
            'index' => 'dvcp'
        ));
 
        $this->addColumn('ttcp', array(
            'header' => Mage::helper('sales')->__('Trạng thái CP'),
            'index' => 'ttcp'
        ));
        
        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Trạng thái Zomart'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
 
        //$this->addColumn('shipping_method', array(
//            'header' => $helper->__('Shipping Method'),
//            'index'  => 'shipping_description'
//        ));
// 

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }
        
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));  
        $this->addExportType('*/*/exportInchooCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportInchooExcel', $helper->__('Excel XML'));
 
        return parent::_prepareColumns();
    }
 
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                 'label'=> Mage::helper('sales')->__('Cancel'),
                 'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                 'label'=> Mage::helper('sales')->__('Hold'),
                 'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                 'label'=> Mage::helper('sales')->__('Unhold'),
                 'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
             'label'=> Mage::helper('sales')->__('Print Invoices'),
             'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
             'label'=> Mage::helper('sales')->__('Print Packingslips'),
             'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
             'label'=> Mage::helper('sales')->__('Print Credit Memos'),
             'url'  => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
             'label'=> Mage::helper('sales')->__('Print All'),
             'url'  => $this->getUrl('*/sales_order/pdfdocs'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
             'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
             'url'  => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

        return $this;
    }
    
    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}