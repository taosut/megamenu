<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/6/2016
 * Time: 2:59 PM
 */
class Ved_Purchase_Block_Adminhtml_Stocktransfer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ved_stocktransfer_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('stocktransfer_filter');

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();

        $helper = Mage::helper("purchase");
        $websiteIds = $helper->getWebsiteByUserId($adminuserId);

        $collection = Mage::getModel('ved_purchase/stocktransfer')->getCollection()
            ->addFieldToFilter('website_id',
                array(
                    array('in'=> array(array_merge(array('0'),$websiteIds))),
                )
            );

        $collection->getSelect()->joinLeft(
            array('user' => $collection->getTable('admin/user')),
            'user.user_id = main_table.created_by');



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
        $helper = Mage::helper("purchase");
        $this->addColumn('id',
            array(
                'header'=> $helper->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'id',
            ));

        $this->addColumn('store_name',
            array(
                'header'        => $helper->__('Kho nhập'),
                'width' => '150px',
                'index'         => 'store_name',
                'type'          => 'text',
                'filter_index'  => 'store_name',
            ));


        $this->addColumn('code',
            array(
                'header'        => $helper->__('Code'),
                'width' => '150px',
                'index'         => 'code',
                'type'          => 'text',
                'filter_index'  => 'code',
            ));

        $this->addColumn('request_store_name', array(
            'header'            => $helper->__('Requested Store'),
            'width' => '120px',
            'width' => '150px',
            'index'         => 'request_store_name',
            'type'          => 'text',
            'filter_index'  => 'request_store_name',
        ));

        $this->addColumn('description',
            array(
                'header'        => $helper->__('Description'),
                'index'         => 'description',
                'type'          => 'text',
                'filter'  => false,
            ));

        $this->addColumn('created_at', array(
            'header' => $helper->__('Created At'),
            'width' => '150px',
            'type'   => 'datetime',
            'index'  => 'created_at',
            'filter_index'=>'created_at',
        ));

        $this->addColumn('status', array(
            'header' => $helper->__('Trạng thái'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '100px',
            //'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'options'=>array(
                '1'=> $helper->__('Created'),
                '2'=> $helper->__('Imported'),
                '0'=> $helper->__('Deleted')
            ),
            'filter_index'=>'status',
        ));



        $this->addColumn('username',
            array(
                'header'=> $helper->__('Created By'),
                'width' => '100px',
                'index' => 'username',
                'type'  => 'text',
                'filter_index'      => 'username',
            ));


        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
//        $this->setMassactionIdField('entity_id');
//        $this->getMassactionBlock()->setFormFieldName('product');
//
//        $this->getMassactionBlock()->addItem('product_approve', array(
//            'label'=> Mage::helper('sales')->__('Approve '),
//            'url'  => $this->getUrl('*/productqc/massApprove'),
//        ));
//
//        $this->getMassactionBlock()->addItem('product_reject', array(
//            'label'=> Mage::helper('sales')->__('Reject '),
//            'url'  => $this->getUrl('*/productqc/massReject'),
//        ));
//
//        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
//        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        //Mage::helper('purchase')->getUrl('adminhtml/stocktransfer/edit/') .'id/$entity_id';
        return $this->getUrl('adminhtml/stocktransfer/edit/', array(
                'id'=>$row->getId())
        );
    }
}
