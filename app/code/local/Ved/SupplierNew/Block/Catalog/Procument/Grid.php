<?php
class Ved_SupplierNew_Block_Catalog_Procument_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('procumentGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('procument_filter');

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    protected function _getSupplier_code()
    {
        $supplier_code =  $this->getRequest()->getParam('supplier', "");
        return $supplier_code;
    }
    protected function _getState()
    {
        $state =  $this->getRequest()->getParam('state', "new");
        return $state;
    }
    public function getListOrderedProducts($state){
        //Get List of new orders
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('state', $state); //use state instead of status since 'processing' is a state of the order not a status.
           // ->addAttributeToSelect('*');
        $orderedProductIds = array();
        foreach($orders as $order){
            $orderedItems = $order->getAllVisibleItems();
            foreach ($orderedItems as $item) {
                $orderedProductIds[] = intval($item->getData('product_id'));
            }
        }
        return array_unique($orderedProductIds);
    }
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection =  Mage::getResourceModel('sales/order_item_collection')
            ->addAttributeToSelect('product_id')
            ->addAttributeToSelect('name')
           // ->addAttributeToSelect('supplier_price')
            ->join(array('cp'=>'catalog/product'), 'main_table.product_id = cp.entity_id',
                array(
                    'sold'=>new Zend_Db_Expr('sum(qty_ordered) - sum(qty_canceled)'),
                    'sku'=>'cp.sku',
                    'id'=>'cp.entity_id'
                )
            )
            ->join(array('so'=>'sales/order'), "main_table.order_id= so.entity_id and so.state like 'new'",
                array(
                    'total_orderid'=>new Zend_Db_Expr('count(so.entity_id)')
                ));
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->join(
                array('inv'=>'cataloginventory/stock_item'),'inv.product_id=cp.entity_id',
                array(
                    'qty'=>'inv.qty'
                ),
                '{{table}}.stock_id=1',
                'left');
        }
        if ($store->getId()) {
            $collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }


        $supplier_code = $this->_getSupplier_code();
        $supplier = Mage::getModel("suppliernew/supplier")->load($supplier_code,'supplier_code');

        if($supplier_code){
            $collection->addAttributeToFilter('supplier', $supplier->option_id);
        }
        $collection->getSelect()->group("main_table.product_id");
        // var_dump($collection->getSelect()->assemble());
        //die();
        //Filter by supplier
        $this->setCollection($collection);

        parent::_prepareCollection();

        //$this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
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
        $this->addColumn('sku',
            array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
            ));
        $this->addColumn('supplier_sku',
            array(
                'header' => Mage::helper('catalog')->__('Supplier SKU'),
                'width' => '80px',
                'index' => 'product_id',
             //   'type' =>'text',
                'renderer'=> 'Ved_SupplierNew_Block_Catalog_Renderer_SupplierSKU'
            ));
        $this->addColumn('name',
            array(
                'header' => Mage::helper('catalog')->__('Name'),
                'index' => 'name',
                'width' => '200px',
            ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

//        $this->addColumn('set_name',
//            array(
//                'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
//                'width' => '100px',
//                'index' => 'attribute_set_id',
//                'type' => 'options',
//                'options' => $sets,
//            ));

        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'index' => 'price',
                'type'  => 'currency',
                'currency_code' => $store->getBaseCurrency()->getCode(),

            ));
        $this->addColumn('supplier_price',
            array(
                'header'=> Mage::helper('catalog')->__('Supplier Price'),
                'index' => 'product_id',
                'type'  => 'currency',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'renderer'=> 'Ved_SupplierNew_Block_Catalog_Renderer_SupplierPrice'

            ));
        $this->addColumn('sold',
            array(
                'header'=> Mage::helper('catalog')->__('Sold'),
                'index' => 'sold',
                'type'  => 'number',
                //'renderer' =>'Ved_SupplierNew_Block_Catalog_Renderer_Sold',
                'filter' => false
            ));
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header'=> Mage::helper('catalog')->__('Qty'),
                    'width' => '100px',
                    'type'  => 'number',
                    'index' => 'qty',
            ));
        }
        $helper = Mage::helper('suppliernew');

        $this->addExportType('*/*/exportProductCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportProductExcel', $helper->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')){
            $this->getMassactionBlock()->addItem('attributes', array(
                'label' => Mage::helper('catalog')->__('Update Attributes'),
                'url'   => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current'=>true))
            ));
        }

        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'store'=>$this->getRequest()->getParam('store'),
            'id'=>$row->getData('product_id'))
        );
    }
}
