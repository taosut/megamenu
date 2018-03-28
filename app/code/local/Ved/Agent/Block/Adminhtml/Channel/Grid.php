<?php

class Ved_Agent_Block_Adminhtml_Channel_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_channel_grid');
        $this->setDefaultSort('channel_id');
        $this->setDefaultDir('DESC');
    }


    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Ved_Agent/agentchannel')
            ->getCollection()
            ->addFieldToFilter('main_table.is_deleted', 0);

        $collection->getSelect()->join(
            array('aduser' => 'admin_user'),
            'aduser.user_id = main_table.created_by',
            array('created_by_email' => 'aduser.email')
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('channel_id', array(
            'index' => 'channel_id',
            'header' => Mage::helper('agent')->__('Channel Id'),
            'sortable' => true,
            'width' => '100px',
        ));

        $this->addColumn('channel_name', array(
            'index' => 'channel_name',
            'header' => Mage::helper('agent')->__('Channel Name'),
            'sortable' => true,
        ));

        $this->addColumn('channel_type', array(
            'index' => 'channel_type',
            'header' => Mage::helper('agent')->__('Channel Type'),
            'type' => 'options',
            'sortable' => true,
            'options' => array(
                Ved_Agent_Model_Agentchannel::SOCIAL => Mage::helper('agent')->__('Social Network'),
                Ved_Agent_Model_Agentchannel::FORUM => Mage::helper('agent')->__('Forum')),
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

        $this->addColumn('created_by_email', array(
            'index' => 'created_by_email',
            'filter_index' => 'aduser.email',
            'header' => Mage::helper('agent')->__('Created By'),
            'sortable' => true,
        ));

        $this->addColumn('action', array(
            'index' => 'channel_id',
            'header' => Mage::helper('agent')->__('Action'),
            'sortable' => false,
            'filter' => false,
            'frame_callback' => array($this, 'prepareActionLayout'),
            'align' => 'center',
            'width' => '150px'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function prepareActionLayout($channelId, $row)
    {
        $isActive = $row->getIsActive();
        $editUrl = $this->getUrl('*/*/edit', array('id' => $channelId));
        $deactiveUrl = $this->getUrl('*/*/deactive', array('id' => $channelId, 'value' => 1 - $isActive));
        $deleteUrl = $this->getUrl('*/*/delete', array('id' => $channelId));

        return '<div>
            <a href="'.$editUrl.'" style="padding-right: 5px">'.Mage::helper('agent')->__('Edit').'</a>'.
            ($isActive ? '<a href="'.$deactiveUrl.'" style="padding-right: 5px" onclick="return confirm(\''.Mage::helper('agent')->__('Do you want to deactive this channel?').'\')">'.Mage::helper('agent')->__('Deactive').'</a>' :
            '<a href="'.$deactiveUrl.'" style="padding-right: 5px" onclick="return confirm(\''.Mage::helper('agent')->__('Do you want to active this channel?').'\')">'.Mage::helper('agent')->__('Active').'</a>').
            '<a href="'.$deleteUrl.'" onclick="return confirm(\''.Mage::helper('agent')->__('Do you want to delete this channel?').'\')">'.Mage::helper('agent')->__('Delete').'</a>
        </div>';
    }

}
