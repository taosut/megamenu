<?php

class Ved_Crosscheck_Block_Adminhtml_ViewPaymentCrosscheck_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ved_crosscheckItem_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        //var_dump($this);die();
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $crosscheckId = $this->getRequest()->get('crosscheck_id');
        $collection = Mage::getModel("ved_crosscheck/paymentCrosscheckItem")->getCollection();
        $collection->addFieldToFilter('payment_crosscheck_id', $crosscheckId);
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
        $this->addColumn('order_increment_id', array(
            'header' => $helper->__('Đơn hàng #'),
            'index'  => 'order_increment_id',
            'width' => '200px',
            'filter_index'=>'order_increment_id',
        ));

        $this->addColumn('order_amount', array(
            'header' => $helper->__('Số tiền'),
            'index' => 'order_amount',
            'width' => '200px',
            'filter_index'=>'order_amount',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Trạng thái'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '200px',
            'options' => array('1' => "Import thành công"),
            'filter_index'=>'status',
        ));

        $this->addColumn('created_at', array(
            'header' => $helper->__('Ngày cập nhật'),
            'type'   => 'datetime',
            'index'  => 'created_at',
            'width' => '200px',
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

        $this->addExportType('*/*/exportCrosscheckItemCsv', $helper->__('CSV'));
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
        return $this->getUrl('*/*/gridPaymentItem', array('_current'=>true));
    }


}