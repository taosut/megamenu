<?php

class Ved_Agent_Block_Adminhtml_Gift_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_redemption_gift');
        $this->setDefaultSort('redemption_gift_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Ved_Agent/agentredemptiongift')
            ->getCollection()
            ->addFieldToFilter('main_table.is_deleted', 0);

        $collection->join(
            array('rule' => 'salesrule/rule'),
            'main_table.rule_id = rule.rule_id',
            array(
                'rule_name' => 'rule.name'
            ), null, 'left'
        );

        $collection->getSelect()->joinLeft(
            array('coupon' => 'salesrule_coupon'),
            'main_table.rule_id = coupon.rule_id',
            null
        )
        ->columns('COUNT(case coupon.is_redeem when "1" then 1 else null end) AS times_used')
        ->columns('COUNT(coupon.is_redeem) AS total_times_use')
        ->group('main_table.redemption_gift_id');

        $collection->addFilterToMap('created_at', 'main_table.created_at');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('redemption_gift_id', array(
            'index' => 'redemption_gift_id',
            'header' => Mage::helper('agent')->__('Redemption Gift Id'),
            'sortable' => true,
            'width' => '100px',
        ));

        $this->addColumn('redemption_gift_name', array(
            'index' => 'redemption_gift_name',
            'header' => Mage::helper('agent')->__('Redemption Gift Name'),
            'sortable' => true,
        ));

        $this->addColumn('rule_name', array(
            'index' => 'rule_name',
            'header' => Mage::helper('agent')->__('Rule Name'),
            'sortable' => true,
        ));

        $this->addColumn('point', array(
            'index' => 'point',
            'header' => Mage::helper('agent')->__('Point to Trade'),
            'sortable' => true,
            'type' => 'number',
        ));

        $this->addColumn('times_used', array(
            'index' => 'times_used',
            'header' => Mage::helper('agent')->__('Times used'),
            'sortable' => true,
            'type' => 'number',
        ));

        $this->addColumn('total_times_use', array(
            'index' => 'total_times_use',
            'header' => Mage::helper('agent')->__('Total Times use'),
            'sortable' => true,
            'type' => 'number',
        ));

        $this->addColumn('created_at', array(
            'index' => 'created_at',
            'header' => Mage::helper('agent')->__('Created At'),
            'sortable' => true,
            'type' => 'datetime',
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Datetime',
        ));

        $this->addColumn('action', array(
            'index' => 'redemption_gift_id',
            'header' => Mage::helper('agent')->__('Action'),
            'sortable' => false,
            'filter' => false,
            'frame_callback' => array($this, 'prepareActionLayout'),
            'align' => 'center',
            'width' => '100px'
        ));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function prepareActionLayout($giftId)
    {
        $editUrl = $this->getUrl('*/*/edit', array('id' => $giftId));
        $deleteUrl = $this->getUrl('*/*/delete', array('id' => $giftId));

        return '<div>
            <a href="'.$editUrl.'" style="padding-right: 5px">'.Mage::helper('agent')->__('Edit').'</a>
            <a href="'.$deleteUrl.'" onclick="return confirm(\''.Mage::helper('agent')->__('Do you want to delete this redemption gift?').'\')">'.Mage::helper('agent')->__('Delete').'</a>
        </div>';
    }

}
