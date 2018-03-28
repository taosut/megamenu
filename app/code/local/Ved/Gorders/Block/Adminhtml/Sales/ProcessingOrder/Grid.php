<?php

class Ved_Gorders_Block_Adminhtml_Sales_ProcessingOrder_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection = Mage::getResourceModel('sales/order_grid_collection');
        $collection
            ->join(array('oi' => 'sales/order'), 'oi.entity_id=main_table.entity_id AND oi.state="processing"'
                , array('qty_ordered' => 'oi.total_qty_ordered', 'dvcp' => 'oi.pl', 'ttcp' => 'oi.shipping_status'), null, 'inner')
            ->join(array('shipping' => 'sales/order_address'), 'main_table.entity_id = shipping.parent_id AND shipping.address_type != "billing"', array(
                'shipping_telephone' => 'shipping.telephone',
                'shipping_address' => new Zend_Db_Expr('concat(shipping.street,",",shipping.city,",",shipping.region) '),
                'shipping_city' => 'shipping.city',
                'shipping_region' => 'shipping.region'
            ), null, 'left')
            ->join(array('billing' => 'sales/order_address'), 'main_table.entity_id = billing.parent_id AND billing.address_type = "billing"', array(
                'billing_telephone' => 'billing.telephone'
            ), null, 'left');
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

        $this->addColumn('qty_ordered', array(
            'header' => Mage::helper('sales')->__('Items Total'),
            'index' => 'qty_ordered',
            'type' => 'number',
            'total' => 'sum',
            'filter' => false
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
            'filter_index' => 'oi.grand_total',
        ));

        $this->addColumn('dvcp', array(
            'header' => Mage::helper('sales')->__('Đơn vị CP'),
            'index' => 'dvcp',
            'filter_index' => 'oi.dvcp',
        ));

        $this->addColumn('ttcp', array(
            'header' => Mage::helper('sales')->__('Trạng thái CP'),
            'index' => 'ttcp',
            'filter_index' => 'oi.ttcp',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Trạng thái'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            //'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'options' => array(
                'processing' => 'Chờ vận chuyển',
                'third_party_ship' => 'Chờ vận chuyển',
                'gcafe_ship' => 'Kỹ thuật vận chuyển',
                'redelivery' => 'Chuyển phát lại',
                'delivery_failed' => 'Giao không thành công',
                'delivered' => 'Đã giao hàng',
                'sent_to_province' => 'Đã chuyển đi tỉnh',
                'province_received' => 'VP Tỉnh đã nhận được hàng'
            ),
            'filter_index' => 'oi.status',
        ));

        $this->addColumn('comments', array(
            'header' => $helper->__('Comments'),
            'index' => 'entity_id',
            'type' => 'text',
            'renderer' => 'Ved_Gorders_Block_Adminhtml_Sales_Renderer_Comments',
            'filter' => false
        ));

        $this->addColumn('confirmed_at', [
            'header' => $helper->__('Confirmed At'),
            'index' => 'entity_id',
            'type' => 'datetime',
            'renderer' => 'Ved_Gorders_Block_Adminhtml_Sales_Renderer_ConfirmedAt',
            'filter' => false,
        ]);
        $this->addExportType('*/*/exportGcafeCsv', $helper->__('CSV'));

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
            $userId = Mage::getSingleton('admin/session')->getUser()->getUserId();
            $userRegions = Mage::getResourceModel('userregionmapping/mapping_collection')->addFieldToFilter('user_id', $userId)->getColumnValues('region_name');
            $isContainStore = count(array_intersect(array('20', '21'), $storeIds)) == count(array('20', '21'));
            if (
                (count($storeIds) == 1 && (in_array('20', $storeIds) || in_array('21', $storeIds)))
                || (count($storeIds) == 2 && $isContainStore)
            ) {
                $this->getCollection()
                    ->addFieldToFilter('oi.store_id', array('in' => $storeIds))
                    ->addFieldToFilter('shipping.region', array('in' => $userRegions));
            } else {
                $this->getCollection()->addFieldToFilter('oi.store_id', array('in' => $storeIds));
            }

            return $this;
        }
        parent::_addColumnFilterToCollection($column);
        return $this;
    }
}