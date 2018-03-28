<?php

class Ved_Crosscheck_Block_Adminhtml_OverdueOrder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ved_overdue_grid');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        //var_dump($this);die();
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_grid_collection');



        //var_dump($select->getData());die();

        $collection->getSelect()
            ->join(array('oi'=>'sales_flat_order'),
                'oi.entity_id=main_table.entity_id AND oi.state="processing" and oi.status!="payment_checking"',
                array(
                    'qty_ordered' => 'oi.total_qty_ordered'
                ),
                null,'inner')
            ->join(array('shipping' => 'sales_flat_order_address'),
                'main_table.entity_id = shipping.parent_id AND shipping.address_type != "billing"',
                array(
                    'shipping_telephone'       => 'shipping.telephone',
                    'shipping_address'=> new Zend_Db_Expr('concat(shipping.street,",",shipping.city,",",shipping.region) '),
                    'shipping_city'       => 'shipping.city',
                    'shipping_region' => 'shipping.region'
                ),
                null,'left')
            ->join(array('billing' => 'sales_flat_order_address'),
                'main_table.entity_id = billing.parent_id AND billing.address_type = "billing"',
                array(
                    'billing_telephone'       => 'billing.telephone'
                ),
                null,'left')
            ->join(array('order_status_history' => new Zend_Db_Expr('(SELECT
                                                                            `sales_flat_order_status_history`.`parent_id`,
                                                                            min(created_at) AS `shipment_started_at`
                                                                        FROM
                                                                            `sales_flat_order_status_history`
                                                                        GROUP BY
                                                                            `sales_flat_order_status_history`.`parent_id` )')),
                'main_table.entity_id = order_status_history.parent_id',
                array(
                    'shipment_started_at'       => 'order_status_history.shipment_started_at'
                ),
                null,'inner');
        $collection
            ->addFieldToFilter('order_status_history.shipment_started_at', array('lt' => new Zend_Db_Expr('DATE_SUB(now(),INTERVAL 7 DAY)')));
        //echo ($collection->getSelect());die();

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('crosscheck');
        $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->addColumn('increment_id', array(
            'header' => $helper->__('Order #'),
            'index'  => 'increment_id',
            'filter_index'=>'oi.increment_id',
        ));

        $this->addColumn('purchased_on', array(
            'header' => $helper->__('Purchased On'),
            'type'   => 'datetime',
            'index'  => 'created_at',
            'filter_index'=>'oi.created_at',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'filter_index'=>'oi.store_id',
            ));
        }

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
            'filter_index'=>'shipping_name',
        ));

        $this->addColumn('shipping_telephone', array(
            'header' => Mage::helper('sales')->__('Shipping Telephone'),
            'index' => 'shipping_telephone',
            'type'  => 'text',
            'filter_index'=>'shipping.telephone',
        ));
        $this->addColumn('shipping_address', array(
            'header' => $helper->__('Shipping Address'),
            'index'  => 'shipping_address',
            'type'  => 'text',
            'filter' => false
        ));
        $this->addColumn('shipping_region', array(
            'header' => $helper->__('Province'),
            'index'  => 'shipping_region',
            'type'  => 'text',
            'filter_index'=>'shipping.region'
        ));
        $this->addColumn('city', array(
            'header' => $helper->__('City'),
            'index'  => 'shipping_city',
            'type' =>'text',
            'filter_index'=>'shipping.city'
        ));

        $this->addColumn('product_lists', array(
            'header'       => $helper->__('Items Ordered'),
            'index'        => 'entity_id',
            'type'  => 'text',
            'renderer'  => 'Ved_Gorders_Block_Adminhtml_Sales_Renderer_ProductList',
            'filter' => false
        ));


        $this->addColumn('qty_ordered', array(
            'header'    => Mage::helper('sales')->__('Items Total'),
            'index'     => 'qty_ordered',
            'type'      => 'number',
            'total'     => 'sum',
            'filter'=> false
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'number',
            'filter_index'=>'oi.grand_total',
        ));

        $this->addColumn('shipment_started_at', array(
            'header' => $helper->__('Ngày xuất kho'),
            'type'   => 'datetime',
            'index'  => 'shipment_started_at',
            'filter_index'=>'order_status_history.shipment_started_at',
        ));

        $this->addColumn('order_status', array(
            'header' => Mage::helper('sales')->__('Trạng thái'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            //'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'options'=>array(
                'processing'=>'Chờ vận chuyển',
                'third_party_ship'=>'Chuyển phát bởi đơn vị vận chuyển',
                'gcafe_ship'=>'Kỹ thuật vận chuyển',
                'redelivery'=>'Chuyển phát lại',
                'delivery_failed'=>'Giao không thành công',
                'delivered'=>'Đã giao hàng',
                'sent_to_province'=>'Đã chuyển đi tỉnh',
                'province_received'=>'VP Tỉnh đã nhận được hàng'
            ),
            'filter_index'=>'oi.status',
        ));

        $this->addExportType('*/*/exportOverdueCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportOverdueExcel', $helper->__('Excel XML'));
        $this->addExportType('*/*/exportOverdueXls', $helper->__('Excel'));

        return parent::_prepareColumns();
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
        return $this->getUrl('*/*/gridOverdueOrder', array('_current'=>true));
    }

    public function getXls()
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = array();
        $xl_data = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $data[] = ''.$column->getExportHeader().'';
            }
        }
        $xl_data[] = $data;

        foreach ($this->getCollection() as $item) {
            $data = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = $column->getRowFieldExport($item);
                }
            }
            $xl_data[] = $data;
        }

        if ($this->getCountTotals())
        {
            $data = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = $column->getRowFieldExport($this->getTotals());
                }
            }
        }
        return $xl_data;
    }
}