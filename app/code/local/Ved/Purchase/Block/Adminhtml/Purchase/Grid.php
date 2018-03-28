<?php

/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/6/2016
 * Time: 2:59 PM
 */
class Ved_Purchase_Block_Adminhtml_Purchase_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ved_purchase_grid');
        $this->setDefaultSort('new_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('purchase_filter');

    }

    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();

        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);

        $collection = Mage::getModel('ved_purchase/purchase')->getCollection()
            ->addFieldToFilter('website_id',
                array(
                    array('in' => array(array_merge(array('0'), $websiteIds))),
                )
            )
            ->addExpressionFieldToSelect(
                'new_date',
                'IF (receive_date IS NULL, DATE_ADD(`main_table`.`created_at`,INTERVAL 10 YEAR), receive_date)',
                ['receive_date' => 'receive_date', 'created_at' => 'created_at']
            );

        $collection->getSelect()->joinLeft(
            array('user' => $collection->getTable('admin/user')),
            '`user`.user_id = main_table.created_by');
        if(!$this->getRequest()->get('sort'))
            $collection->getSelect()->order(new Zend_Db_Expr('CASE WHEN `main_table`.`status` = 1 THEN 0 ELSE 1 END'))
                ->order('new_date', 'ASC')->order('main_table.created_at', 'ASC');
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);
        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }
        if (is_string($filter)) {
            $filters = $this->helper('adminhtml')->prepareFilterString($filter);

        }
        if ($column->getId() == 'total_product' && $filters['total_product']) {
            /**
             * @var Ved_Purchase_Model_Resource_Purchase_Collection $purchaseCollection
             */
            $purchaseCollection = $this->getCollection();
            $productSku = $purchaseCollection->getSelect()->getAdapter()->quote($filters['total_product']);
            $purchaseCollection->getSelect()->columns(array('filter_key' => new Zend_Db_Expr($productSku)));
            $purchaseCollection->getSelect()->joinLeft(array('item' => 'sales_flat_purchase_item'),
                "main_table.id = item.purchase_id and (item.product_sku like" . '"%"' . $productSku . '"%"'. "or item.product_name like" . '"%"' . $productSku . '"%")',
                ['item.product_sku', 'item.product_name']
            )->where('item.product_sku is not null');
            return $this;
        }
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper("purchase");
        $this->addColumn('id',
            array(
                'header' => $helper->__('ID'),
                'width' => '50px',
                'type' => 'number',
                'filter_index' => 'main_table.id',
                'index' => 'id',
            ));

        $this->addColumn('website_id',
            array(
                'header' => Mage::helper('catalog')->__('Websites'),
                'width' => '120px',
                'sortable' => false,
                'index' => 'website_id',
                'type' => 'options',
                'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            ));


        $this->addColumn('code',
            array(
                'header' => $helper->__('Code'),
                'width' => '120px',
                'index' => 'code',
                'type' => 'text',
                'filter_index' => 'main_table.code',
                'renderer' => 'Ved_Purchase_Block_Adminhtml_Renderer_Purchase_Code'
            ));

        $this->addColumn('supplier_name', array(
            'header' => $helper->__('Supplier Name'),
            'index' => 'supplier_name',
            'type' => 'text',
            'filter_index' => 'main_table.supplier_name',
            'renderer' => 'Ved_Purchase_Block_Adminhtml_Renderer_Purchase_Code'
        ));

        $this->addColumn('total_product', array(
            'header' => $helper->__('Tổng số sản phẩm/Mã sản phẩm'),
            'type' => 'text',
            'renderer' => 'Ved_Purchase_Block_Adminhtml_Renderer_Product_Total',
        ));

        $this->addColumn('created_at', array(
            'header' => $helper->__('Created At'),
            'width' => '150px',
            'type' => 'datetime',
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'renderer' => 'Ved_Purchase_Block_Adminhtml_Renderer_Purchase_Code'
        ));

        $this->addColumn('receive_date', array(
            'header' => $helper->__('Receive Date'),
            'width' => '150px',
            'type' => 'datetime',
            'index' => 'receive_date',
            'filter_index' => 'main_table.receive_date',
            'renderer' => 'Ved_Purchase_Block_Adminhtml_Renderer_Purchase_Code'
        ));

        $this->addColumn('status', array(
            'header' => $helper->__('Trạng thái'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            'options' => array(
                '1' => $helper->__('Created'),
                '2' => $helper->__('Imported'),
                '0' => $helper->__('Deleted')
            ),
            'filter_index' => 'main_table.status',
        ));

        $this->addColumn('username',
            array(
                'header' => $helper->__('User'),
                'width' => '80px',
                'index' => 'username',
                'type' => 'text',
                'filter_index' => 'username',
            ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        Mage::helper('purchase')->getUrl('adminhtml/purchase/edit/') . 'id/$entity_id';
        return $this->getUrl('adminhtml/purchase/edit/', array(
                'id' => $row->getId())
        );
    }
}
