<?php

class Ved_Agent_Block_Adminhtml_Info_Edit_Tabs_Refer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_refer_agent_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/agent_info/referedGrid', array('_current' => true));
    }

    protected function _prepareCollection()
    {
        $agentId = $this->getRequest()->getParam('id');

        $collection = Mage::getModel('Ved_Agent/agentpointhistory')
            ->getCollection()
            ->addFieldToFilter('main_table.type', 3)
            ->addFieldToFilter('main_table.user_id', $agentId);

        // Join user firstname
        $firstname = Mage::getResourceModel('customer/customer')->getAttribute('firstname');
        $collection->getSelect()->joinLeft(
            array('cus' => $firstname->getBackend()->getTable()),
            'main_table.object_id = cus.entity_id and cus.attribute_id = '.(int) $firstname->getAttributeId(),
            array(
                'agent_name' => 'cus.value'
            )
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('agent_name', array(
            'index' => 'agent_name',
            'header' => Mage::helper('agent')->__('Name'),
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
