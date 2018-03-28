<?php

class Ved_Agent_Block_Adminhtml_Info_Edit_Tabs_Point_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_point_history_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/agent_info/pointHistoryGrid', array('_current' => true));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Ved_Agent/agentpointhistory')->getCollection();

        if ($agentId = $this->getRequest()->getParam('id')) {
            $collection->addFieldToFilter('main_table.user_id', $agentId);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('point', array(
            'index' => 'point',
            'header' => Mage::helper('agent')->__('Point'),
            'width' => '100px',
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Agent_Point',
            'align' => 'center',
            'sortable' => false,
            'filter' => false
        ));

        $this->addColumn('type', array(
            'index' => 'type',
            'type' => 'options',
            'options' => array(
                1 => Mage::helper('agent')->__('Verify achievement'),
                2 => Mage::helper('agent')->__('Redemption'),
                3 => Mage::helper('agent')->__('Refer agent'),
                4 => Mage::helper('agent')->__('Add Point'),
                5 => Mage::helper('agent')->__('Substract Point')
            ),
            'header' => Mage::helper('agent')->__('Type'),
            'sortable' => true,
        ));

        $this->addColumn('detail', array(
            'index' => 'detail',
            'header' => Mage::helper('agent')->__('Detail'),
            'sortable' => true,
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Agent_Detail',
            'sortable' => false,
            'filter' => false
        ));

        $this->addColumn('accumulate_point', array(
            'index' => 'accumulate_point',
            'header' => Mage::helper('agent')->__('Accumulate Point'),
            'align' => 'center',
            'sortable' => false,
            'filter' => false
        ));

        $this->addColumn('created_at', array(
            'index' => 'created_at',
            'header' => Mage::helper('agent')->__('Created At'),
            'type' => 'datetime',
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Datetime',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return null;
    }
}
