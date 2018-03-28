<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('logGrid');
        $this->setDefaultSort('date');
    }
    
    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getModel('pvfault/log')->getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('pvfault');

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => $hlp->__('Store'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
            ));
        }

        $this->addColumn('date', array(
            'header'    => $hlp->__('Date'),
            'index'     => 'date',
            'type'      => 'datetime',
            'width'     => '170px',
            'gmtoffset' => true,
            'default'	=> ' ---- ',
        ));

        $this->addColumn('url', array(
            'header'    => $hlp->__('Page'),
            'index'     => 'url',
        ));

        $this->addColumn('referer', array(
            'header'    => $hlp->__('Referrer'),
            'index'     => 'referer',
        ));

        $this->addColumn('client_ip', array(
            'header'    => $hlp->__('Client IP'),
            'index'     => 'client_ip',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId())); 
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('log_id');
        $this->getMassactionBlock()->setFormFieldName('log');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->escapeHtml(Mage::helper('pvfault')->__('Delete')),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->escapeHtml(Mage::helper('pvfault')->__('Are you sure?'))
        ));

        return $this;
    }

}