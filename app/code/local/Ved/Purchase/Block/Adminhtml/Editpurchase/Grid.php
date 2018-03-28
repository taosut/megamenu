<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/6/2016
 * Time: 2:59 PM
 */
class Ved_Purchase_Block_Adminhtml_Editpurchase_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ved_purchase_item_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('purchase_filter');

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {

        $purchaseId = $this->getParentBlock()->getPurchaseId();


        $collection = Mage::getModel("ved_purchase/purchaseitem")->getCollection()
            ->addFieldToFilter('purchase_id', $purchaseId)
            ->addExpressionFieldToSelect('total_price', '( price * request_qty )', array('price'=>'price', 'import_qty'=>'import_qty'));
        //var_dump($collection);die();

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

        $this->addColumn('product_name',
            array(
                'header'        => $helper->__('Product Name'),
                'width'         => '300px',
                'index'         => 'product_name',
                'type'          => 'text',
                'filter'        => false
            ));

        $this->addColumn('product_sku',
            array(
                'header'        => $helper->__('Product Code'),
                'width'         => '120px',
                'index'         => 'product_sku',
                'type'          => 'text',
                'filter'        => false
            ));

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '120px',
                'sortable'  => false,
                'index'     => 'type',
                'type'      => 'options',
                'options'   => array('1' => 'Hàng hóa', '2' => 'Ký gửi'),
                'filter'        => false
            ));

        $this->addColumn('request_qty',
            array(
                'header'        => $helper->__('Request Quantity'),
                'width'         => '120px',
                'index'         => 'request_qty',
                'type'          => 'text',
                'filter'        => false
            ));

        $this->addColumn('import_qty',
            array(
                'header'        => $helper->__('Import Quantity'),
                'width'         => '120px',
                'index'         => 'import_qty',
                'type'          => 'text',
                'filter'        => false
            ));

        $store = $this->_getStore();

        $this->addColumn('price',
            array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'width'         => '120px',
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'filter'        => false
            ));

        $this->addColumn('vat',
            array(
                'header'        => $helper->__('VAT'),
                'width'         => '80px',
                'index'         => 'vat',
                'align'         => 'right',
                'type'          => 'text',
                'filter'        => false
            ));


        $this->addColumn('total_price',
            array(
                'header'=> Mage::helper('catalog')->__('Total Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'total_price',
                'filter'        => false
            ));





        $link= Mage::helper('purchase')->getUrl('adminhtml/purchase/edit/') .'id/$entity_id';
        $this->addColumn('action',
            array(
                'header'    => $helper->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $helper->__('Edit'),
                        'url'     => $link
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
            ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_Rss')) {
            $this->addRssList('rss/catalog/notifystock', $helper->__('Notify Low Stock RSS'));
        }

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
        return $this->getUrl('*/*/1', array(
                'store'=>$this->getRequest()->getParam('store'),
                'id'=>$row->getId())
        );
    }
}
