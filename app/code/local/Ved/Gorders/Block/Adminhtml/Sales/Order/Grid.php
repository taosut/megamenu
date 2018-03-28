<?php

class Ved_Gorders_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ved_gorders_grid');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /**
         * @var Mage_Sales_Model_Resource_Order_Grid_Collection $collection
         */
        $collection = Mage::getResourceModel('sales/order_grid_collection');
        $collection
            ->join(array('oi' => 'sales/order'),
                'oi.entity_id=main_table.entity_id',
                array(
                    'qty_ordered' => 'oi.total_qty_ordered',
                    'deposit_amount' => 'oi.deposit_amount',
                    'updated_at_order' => 'oi.updated_at',
                    'coupon_code' => 'oi.coupon_code',
                    'total_canceled' => 'oi.total_canceled'
                ),
                null, 'inner')
            ->join(array('shipping' => 'sales/order_address'),
                'main_table.entity_id = shipping.parent_id AND shipping.address_type != "billing"',
                array(
                    'shipping_telephone' => 'shipping.telephone',
                    'shipping_address' => new Zend_Db_Expr('concat(shipping.street,",",shipping.city,",",shipping.region) '),
                    'shipping_city' => 'shipping.city',
                    'shipping_region' => 'shipping.region'
                ),
                null, 'left')
            ->join(array('billing' => 'sales/order_address'),
                'main_table.entity_id = billing.parent_id AND billing.address_type = "billing"',
                array(
                    'billing_telephone' => 'billing.telephone'
                ),
                null, 'left')
            ->join(array('payment' => 'sales/order_payment'),
                'main_table.entity_id = payment.parent_id',
                array(
                    'po_number' => 'payment.po_number'
                ),
                null, 'left');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /**
         * @var Mage_Core_Model_Resource_Store_Collection $stores
         */
        $stores = Mage::getModel('core/store')->getCollection()->load();
        $helper = Mage::helper('ved_gorders');
        $this->addColumn('entity_id', array(
            'header' => $helper->__('ID #'),
            'index' => 'entity_id',
            'filter_index' => 'oi.entity_id',
        ));

        $this->addColumn('increment_id', array(
            'header' => $helper->__('Order #'),
            'index' => 'increment_id',
            'filter_index' => 'oi.increment_id',
        ));

        $this->addColumn('purchased_on', array(
            'header' => $helper->__('Purchased On'),
            'type' => 'datetime',
            'index' => 'created_at',
            'filter_index' => 'oi.created_at',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sales')->__('Purchased From (Store)'),
                'index' => 'store_id',
                'type' => 'multiple',
                'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                'filter_index' => 'oi.store_id',
                'options' => $this->getOptionsFromCollection($stores->toOptionArray()),
            ));
        }

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
            'filter_index' => 'shipping_name',
        ));

        $this->addColumn('shipping_telephone', array(
            'header' => Mage::helper('sales')->__('Shipping Telephone'),
            'index' => 'shipping_telephone',
            'type' => 'text',
            'filter_index' => 'shipping.telephone',
        ));

        $this->addColumn('shipping_address', array(
            'header' => $helper->__('Shipping Address'),
            'index' => 'shipping_address',
            'type' => 'text',
            'filter' => false
        ));

        $this->addColumn('shipping_region', array(
            'header' => $helper->__('Province'),
            'index' => 'shipping_region',
            'type' => 'text',
            'filter_index' => 'shipping.region'
        ));

        $this->addColumn('city', array(
            'header' => $helper->__('City'),
            'index' => 'shipping_city',
            'type' => 'text',
            'filter_index' => 'shipping.city'
        ));

        $this->addColumn('product_lists', array(
            'header' => $helper->__('Items Ordered'),
            'index' => 'entity_id',
            'type' => 'text',
            'renderer' => 'Ved_Gorders_Block_Adminhtml_Sales_Renderer_ProductList',
            'filter' => false
        ));

        $this->addColumn('po_number', array(
            'header' => $helper->__('VnPay Trans No'),
            'index' => 'po_number',
            'type' => 'text',
            'filter_index' => 'payment.po_number',
        ));

        $this->addColumn('coupon_code', array(
            'header' => $helper->__('Coupon Code'),
            'index' => 'coupon_code',
            'type' => 'text',
            'filter_index' => 'oi.coupon_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
            'filter_index' => 'oi.grand_total',
        ));

        $this->addColumn('total_canceled', array(
            'header' => Mage::helper('sales')->__('Total Canceled'),
            'index' => 'total_canceled',
            'type' => 'currency',
            'currency' => 'order_currency_code',
            'filter_index' => 'oi.total_canceled',
        ));

        $this->addColumn('order_status', array(
            'header' => $helper->__('Status'),
            'index' => 'status',
            'type' => 'multiple',
            'filter_index' => 'oi.status',
            'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('comments', array(
            'header' => $helper->__('Comments'),
            'index' => 'entity_id',
            'type' => 'text',
            'renderer' => 'Ved_Gorders_Block_Adminhtml_Sales_Renderer_Comments',
            'filter' => false
        ));

        $this->addColumn('updated_at', array(
            'header' => $helper->__('Updated At'),
            'type' => 'datetime',
            'index' => 'updated_at_order',
            'filter_index' => 'oi.updated_at',
        ));

        $this->addColumn('confirmed_at', [
            'header' => $helper->__('Confirmed At'),
            'index' => 'entity_id',
            'type' => 'datetime',
            'renderer' => 'Ved_Gorders_Block_Adminhtml_Sales_Renderer_ConfirmedAt',
            'filter' => false,
        ]);

        $this->addExportType('*/*/exportGcafeCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportOrderXls', $helper->__('Excel Order'));
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
        return $this->getUrl('*/*/grid', array('_current' => true));
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
                $data[] = '' . $column->getExportHeader() . '';
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

        if ($this->getCountTotals()) {
            $data = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = $column->getRowFieldExport($this->getTotals());
                }
            }
        }
        return $xl_data;
    }

    public function getOrderXls()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);
        $binds = array();
        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $filters = $this->helper('adminhtml')->prepareFilterString($filter);

        }

        $new_db_resource = Mage::getSingleton('core/resource');
        $connection = $new_db_resource->getConnection('default_read');
        $query = "select a.customer_id, c.firstname,
                    c.region, c.city 'quan_huyen' , a.increment_id, a.status, a.created_at, b.sku, b.warehouse_sku, b.name,
                    b.qty_ordered, b.total_returned, b.base_price, b.discount_amount 'khuyen_mai', a.coupon_code,
                    a.coupon_rule_name, a.discount_amount 'coupon_giam', b.row_total, b.price * b.total_returned 'row_total_canceled', b.row_total - b.price * b.total_returned 'real_total' from sales_flat_order a
                    join sales_flat_order_item b on a.entity_id = b.order_id
                    left join sales_flat_order_address c on a.shipping_address_id = c.entity_id
                    where 1=1 ";

        if (isset($filters['entity_id'])) {
            $query .= " and a.entity_id = :entity_id";
            $binds['entity_id'] = $filters['entity_id'];
        }
        if (isset($filters['increment_id'])) {
            $query .= " and a.increment_id like CONCAT(:increment_id, '%')";
            $binds['increment_id'] = $filters['increment_id'];
        }
        if (isset($filters['store_id'])) {
            $query .= " and a.store_id = :store_id";
            $binds['store_id'] = $filters['store_id'];
        }
        if (isset($filters['purchased_on'])) {
            if (isset($filters['purchased_on']['from'])) {
                $date = str_replace('/', '-', $filters['purchased_on']['from']);
                $query .= " and a.created_at > :from";
                $binds['from'] = date('Y-m-d', strtotime($date));
            }
            if (isset($filters['purchased_on']['to'])) {
                $date = str_replace('/', '-', $filters['purchased_on']['to']);
                $query .= " and a.created_at < :to";
                $binds['to'] = date('Y-m-d 23:59:59', strtotime($date));
            }
        }
        if (isset($filters['shipping_name'])) {
            $query .= " and c.firstname like CONCAT(:shipping_name, '%')";
            $binds['shipping_name'] = $filters['shipping_name'];
        }
        if (isset($filters['city'])) {
            $query .= " and c.city like CONCAT(:city, '%')";
            $binds['city'] = $filters['city'];
        }
        if (isset($filters['shipping_region'])) {
            $query .= " and c.region like CONCAT(:shipping_region, '%')";
            $binds['shipping_region'] = $filters['shipping_region'];
        }
        if (isset($filters['order_status'])) {
            $values = explode(",", $filters['order_status']);
            foreach($values as &$value){
                $value = "'" . $value . "'";
            }
            $order_status = implode(", ", $values);
            $query .= " and a.status in ($order_status)";
        } else {
        }
        $result = $connection->fetchAll($query, $binds);
        return $result;

    }

    /**
     * @param array $array
     * @return  array
     */
    private function getOptionsFromCollection($array)
    {
        $data = [];
        foreach ($array as $item) {
            $data[$item['value']] = $item['label'];
        }
        return $data;
    }

    /**
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);
        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $filters = $this->helper('adminhtml')->prepareFilterString($filter);
        }

        if ($column->getId() == 'order_status' && $filters['order_status']) {
            $this->getCollection()->addFieldToFilter('oi.status', array('in' => explode(",", $filters['order_status'])));
            return $this;
        }

        if ($column->getId() == 'store_id' && $filters['store_id']) {
            $storeIds = explode(",", $filters['store_id']);
//            $userId = Mage::getSingleton('admin/session')->getUser()->getUserId();
//            $userRegions = Mage::getResourceModel('userregionmapping/mapping_collection')->addFieldToFilter('user_id', $userId)->getColumnValues('region_name');
//            $isContainStore = count(array_intersect(array('20', '21'), $storeIds)) == count(array('20', '21'));
//            if (
//                (count($storeIds) == 1 && (in_array('20', $storeIds) || in_array('21', $storeIds)))
//                || (count($storeIds) == 2 && $isContainStore)
//            ) {
//                $this->getCollection()
//                    ->addFieldToFilter('oi.store_id', array('in' => $storeIds))
//                    ->addFieldToFilter('shipping.region', array('in' => $userRegions));
//            }
//            else {
//                $this->getCollection()->addFieldToFilter('oi.store_id', array('in' => $storeIds));
//            }

            $this->getCollection()->addFieldToFilter('oi.store_id', array('in' => $storeIds));
            return $this;
        }
        parent::_addColumnFilterToCollection($column);
        return $this;
    }
}