<?php

class Ved_Agent_Block_Adminhtml_Account_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_account_history_grid');
    }

    protected function _prepareCollection()
    {
        $accountId = $this->getRequest()->getParam('id');

        $collection = Mage::getModel('Ved_Agent/agentaccounthistory')
            ->getCollection()
            ->addFieldToFilter('main_table.account_id', $accountId);

        $collection->join(
            array('account' => 'agent_account'),
            'main_table.account_id =  account.account_id',
            array('account_name'), 'account.is_deleted = 0', 'inner'
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('account_history_id', array(
            'index' => 'account_history_id',
            'header' => Mage::helper('agent')->__('History Id'),
            'type' => 'number',
            'sortable' => true,
        ));

        $this->addColumn('account_name', array(
            'index' => 'account_name',
            'header' => Mage::helper('agent')->__('Account Name'),
            'sortable' => true,
        ));

        $this->addColumn('created_at', array(
            'index' => 'created_at',
            'header' => Mage::helper('agent')->__('Created At'),
            'sortable' => true,
            'type' => 'datetime',
            'render' => 'Ved_Agent_Block_Adminhtml_Renderer_Datetime',
        ));

        $this->addColumn('status', array(
            'index' => 'status',
            'header' => Mage::helper('agent')->__('Status'),
            'sortable' => true,
            'type' => 'options',
            'options' => array(
                Ved_Agent_Model_Agentaccount::WAITING_TO_VERIFY => Mage::helper('agent')->__('Waiting to Verify'),
                // Ved_Agent_Model_Agentaccount::REQUIRE_TO_UPDATE => Mage::helper('agent')->__('Require to Update'),
                Ved_Agent_Model_Agentaccount::DECLINED => Mage::helper('agent')->__('Declined'),
                Ved_Agent_Model_Agentaccount::VERIFIED => Mage::helper('agent')->__('Verified')),
        ));

        $this->addColumn('comment', array(
            'index' => 'comment',
            'header' => Mage::helper('agent')->__('Comment'),
            'sortable' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return null;
    }
}
