<?php

class Ved_Agent_Block_Adminhtml_Achievementtype_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_achievementtype_grid');
        $this->setDefaultSort('achievement_type_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Ved_Agent/agentachievementtype')
            ->getCollection()
            ->addFieldToFilter('main_table.is_deleted', 0);

        $collection->join(
            array('channel' => 'agent_channel'),
            'main_table.channel_id = channel.channel_id',
            array('channel_name' => 'channel.channel_name'),
            'channel.is_deleted = 0', 'left'
        );

        $collection->addFilterToMap('created_at', 'main_table.created_at');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('achievement_type_id', array(
            'index' => 'achievement_type_id',
            'header' => Mage::helper('agent')->__('Achievement Type Id'),
            'sortable' => true,
            'width' => '100px',
        ));

        $this->addColumn('channel_name', array(
            'index' => 'channel_name',
            'header' => Mage::helper('agent')->__('Channel Name'),
            'sortable' => true,
        ));

        $this->addColumn('achievement_type_name', array(
            'index' => 'achievement_type_name',
            'header' => Mage::helper('agent')->__('Achievement Type Name'),
            'sortable' => true,
        ));

        $this->addColumn('point', array(
            'index' => 'point',
            'header' => Mage::helper('agent')->__('Point'),
            'sortable' => true,
            'type' => 'number'
        ));

        $this->addColumn('is_active', array(
            'index' => 'is_active',
            'header' => Mage::helper('agent')->__('Is Active?'),
            'sortable' => true,
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('agent')->__('Inactive'),
                1 => Mage::helper('agent')->__('Active')
            ),
            'width' => '100px'
        ));

        $this->addColumn('main_table_created_at', array(
            'index' => 'created_at',
            'header' => Mage::helper('agent')->__('Created At'),
            'sortable' => true,
            'type' => 'datetime',
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Datetime',
        ));

        $this->addColumn('action', array(
            'index' => 'achievement_type_id',
            'header' => Mage::helper('agent')->__('Action'),
            'sortable' => false,
            'filter' => false,
            'frame_callback' => array($this, 'prepareActionLayout'),
            'align' => 'center',
            'width' => '200px'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function prepareActionLayout($typeId, $row)
    {
        $isActive = $row->getIsActive();
        $editUrl = $this->getUrl('*/*/edit', array('id' => $typeId));
        $deactiveUrl = $this->getUrl('*/*/deactive', array('id' => $typeId, 'value' => 1 - $isActive));
        $deleteUrl = $this->getUrl('*/*/delete', array('id' => $typeId));

        return '<div>
            <a href="'.$editUrl.'" style="padding-right: 5px">'.Mage::helper('agent')->__('Edit').'</a>'.
            ($isActive ? '<a href="'.$deactiveUrl.'" style="padding-right: 5px" onclick="return confirm(\''.Mage::helper('agent')->__('Do you want to deactive this achievement type?').'\')">'.Mage::helper('agent')->__('Deactive').'</a>' :
            '<a href="'.$deactiveUrl.'" style="padding-right: 5px" onclick="return confirm(\''.Mage::helper('agent')->__('Do you want to active this achievement type?').'\')">'.Mage::helper('agent')->__('Active').'</a>').
            '<a href="'.$deleteUrl.'" onclick="return confirm(\''.Mage::helper('agent')->__('Do you want to delete this achievement type?').'\')">'.Mage::helper('agent')->__('Delete').'</a>
        </div>';
    }

}
