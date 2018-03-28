<?php

class Ved_Crosscheck_Block_Adminhtml_PaymentCrosscheck_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ved_crosscheck_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        //var_dump($this);die();
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("ved_crosscheck/paymentcrosscheck")->getCollection();

        $collection->getSelect()->joinLeft(
            array('user' => $collection->getTable('admin/user')),
            'user.user_id = main_table.created_by');

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('crosscheck');
        $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->addColumn('id', array(
            'header' => $helper->__('Payment #'),
            'index'  => 'id',
            'width' => '100px',
            'filter_index'=>'id',
        ));

        $this->addColumn('pay_date', array(
            'header' => $helper->__('Pay Date'),
            'type'   => 'date',
            'index'  => 'pay_date',
            'filter_index'=>'pay_date',
        ));

        $this->addColumn('store_id', array(
            'header'    => $helper->__('From (Store)'),
            'index'     => 'store_id',
            'type'      => 'store',
            'filter_index'=>'store_id',
        ));

        $this->addColumn('total_amount', array(
            'header' => $helper->__('Total amount'),
            'index' => 'total_amount',
            'filter_index'=>'total_amount',
        ));

        $this->addColumn('total_imported_amount', array(
            'header' => $helper->__('Total amount imported'),
            'index' => 'total_imported_amount',
            'filter_index'=>'total_imported_amount',
        ));

        $this->addColumn('note', array(
            'header' => $helper->__('Note'),
            'index' => 'note',
            'width' => '300px',
            'filter'=> false,
        ));

        $this->addColumn('created_at', array(
            'header' => $helper->__('Created at'),
            'type'   => 'datetime',
            'index'  => 'created_at',
            'filter_index'=>'created_at',
        ));

        $this->addColumn('username',
            array(
                'header'=> $helper->__('User'),
                'width' => '150px',
                'index' => 'username',
                'type'  => 'text',
                'filter_index'      => 'username',
            ));

        $this->addColumn('total_imported_amount', array(
            'header' => $helper->__('Total amount imported'),
            'index' => 'total_imported_amount',
            'filter_index'=>'total_imported_amount',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Trạng thái'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '200px',
            'options'=>array(
                '1'=>'Chờ xử lý',
                '2'=>'Đã cập nhật đơn hàng',
                '3'=>'Đã hoàn tất',
                '0'=>'Đã hủy',
            ),
            'filter_index'=>'status',
        ));

        return parent::_prepareColumns();
    }



    public function getRowUrl($row)
    {
        if($row->getStatus() == 1){
            return $this->getUrl('*/*/editPaymentCrosscheck', array('crosscheck_id' => $row->getId()));
        }else{
            return $this->getUrl('*/*/viewPaymentCrosscheck', array('crosscheck_id' => $row->getId()));
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridPayment', array('_current'=>true));
    }


}