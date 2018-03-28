<?php

class Ved_Agent_Block_Adminhtml_Achievement_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('achievement_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        if ($this->getRequest()->getParam('verify_achievement')) {
            $this->setDefaultFilter(array('status' => 0));
        }
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/agent_achievement/grid', array('_current' => true));
    }

    protected function _prepareCollection()
    {
        $status = $this->getRequest()->getParam('status');

        $collection = Mage::getModel('Ved_Agent/agentachievement')->getCollection()
            ->addFieldToFilter('main_table.is_deleted', 0)
            ->addFieldToSelect(array('created_at', 'account_id', 'user_id', 'link', 'status', 'comment'));

        // if ($this->getRequest()->getParam('verify_achievement')) {
        //     $collection->addFieldToFilter('main_table.status', array('neq' => 3));
        // } else if (!isset($status)) {
        //     $collection->addFieldToFilter('main_table.status', array('eq' => 3));
        // }

        if ($agentId = $this->getRequest()->getParam('id')) {
            $collection->addFieldToFilter('main_table.user_id', $agentId);
        }

        if (isset($status)) {
            $collection->addFieldToFilter('main_table.status', array('eq' => $status));
        }

        if ($accountId = $this->getRequest()->getParam('account_id')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        }

        $collection->join(
            array('at' => 'agent_achievement_type'),
            'at.achievement_type_id = main_table.achievement_type_id',
            array(
                'achievement_type_name' => 'at.achievement_type_name',
            ),
            'at.is_deleted = 0', 'left'
        )->join(
            array('account' => 'agent_account'),
            'account.account_id = main_table.account_id',
            null, 'account.is_deleted = 0', 'left'
        )->join(
            array('channel' => 'agent_channel'),
            'account.channel_id = channel.channel_id',
            array(
                'account_link_and_type' => new Zend_Db_Expr('CONCAT(account.account_name, " (", channel.channel_name, ")")')
            ), 'channel.is_deleted = 0', 'left'
        )->getSelect()->joinLeft(
            array('ptshistory' => 'agent_point_history'),
            'main_table.achievement_id = ptshistory.object_id and ptshistory.type = 1',
            array('verified_point' => 'ptshistory.point')
        );

        $collection->getSelect()
            ->columns('(SELECT COUNT(achievement_id)
                FROM agent_achievement AS sub_agent
                WHERE sub_agent.user_id = main_table.user_id
                    AND sub_agent.achievement_type_id = main_table.achievement_type_id
                    AND DATE(main_table.created_at) = DATE(sub_agent.created_at)
                )
            AS num_type_today');

        $collection->addFilterToMap('status', 'main_table.status');

        $firstname = Mage::getResourceModel('customer/customer')->getAttribute('firstname');
        $collection->getSelect()->join(
            array('cus' => $firstname->getBackend()->getTable()),
            'main_table.user_id = cus.entity_id and cus.attribute_id = '.(int) $firstname->getAttributeId(),
            array(
                'agent_name' => 'cus.value'
            )
        );

        $agentCode = Mage::getResourceModel('customer/customer')->getAttribute('agent_code');
        $collection->getSelect()->join(
            array('cus_code' => $agentCode->getBackend()->getTable()),
            'main_table.user_id = cus_code.entity_id and cus_code.attribute_id = '.(int) $agentCode->getAttributeId(),
            array(
                'agent_code' => 'cus_code.value'
            )
        );

        $collection->addFilterToMap('created_at', 'main_table.created_at');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'index' => 'created_at',
            'header' => Mage::helper('agent')->__('Created At'),
            'sortable' => true,
            'type' => 'datetime',
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Datetime',
        ));

        if (!$this->getRequest()->getParam('id')) {
            $this->addColumn('agent_code', array(
                'index' => 'agent_code',
                'filter_index' => 'cus_code.value',
                'header' => Mage::helper('agent')->__('Agent Code Id'),
                'sortable' => true,
            ));
            $this->addColumn('agent_name', array(
                'index' => 'agent_name',
                'filter_index' => 'cus.value',
                'header' => Mage::helper('agent')->__('Agent Name'),
                'sortable' => true,
            ));
        }

        $this->addColumn('achievement_type_name', array(
            'index' => 'achievement_type_name',
            'filter_index' => 'achievement_type_name',
            'header' => Mage::helper('agent')->__('Achievement Type'),
            'sortable' => true,
        ));

        $this->addColumn('num_type_today', array(
            'index' => 'num_type_today',
            'filter_index' => 'num_type_today',
            'header' => Mage::helper('agent')->__('Times used this type today'),
            'sortable' => true,
        ));

        $this->addColumn('account_link_and_type', array(
            'index' => 'account_link_and_type',
            'header' => Mage::helper('agent')->__('Agent Account'),
            'sortable' => true,
            'filter_condition_callback' => array($this, 'filterAccountLinkAndTypeCallback')
        ));

        $this->addColumn('link', array(
            'index' => 'link',
            'header' => Mage::helper('agent')->__('Link'),
            'sortable' => false,
            'frame_callback' => array($this, 'prepareLinkLayout'),
            'width' => '300px'
        ));

        $this->addColumn('status', array(
            'index' => 'status',
            'header' => Mage::helper('agent')->__('Status'),
            'sortable' => true,
            'type' => 'options',
            'options' => array(
                Ved_Agent_Model_Agentachievement::WAITING_TO_VERIFY => Mage::helper('agent')->__('Waiting to Verify'),
                Ved_Agent_Model_Agentachievement::REQUIRE_TO_UPDATE => Mage::helper('agent')->__('Require to Update'),
                Ved_Agent_Model_Agentachievement::DECLINED => Mage::helper('agent')->__('Declined'),
                Ved_Agent_Model_Agentachievement::VERIFIED => Mage::helper('agent')->__('Verified'),
            )
        ));

        $this->addColumn('comment', array(
            'index' => 'comment',
            'header' => Mage::helper('agent')->__('Admin Comment'),
            'sortable' => true,
        ));

        $this->addColumn('verified_point', array(
            'index' => 'verified_point',
            'header' => Mage::helper('agent')->__('Point'),
            'sortable' => true,
            'align' => 'center',
            'width' => '20px'
        ));

        if (!$this->getRequest()->getParam('id')) {
            $this->addColumn('action', array(
                'index' => 'achievement_id',
                'header' => Mage::helper('agent')->__('Action'),
                'frame_callback' => array($this, 'prepareActionLayout'),
                'align' => 'center',
                'sortable' => false,
                'filter' => false,
                'width' => '150px'
            ));
        }


        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function prepareLinkLayout($link)
    {
        return '<a target="_blank" href="'.$link.'">'.$link.'</a>';
    }

    public function prepareActionLayout($value, $row)
    {
        $achievementId = $row->getId();
        $achievementStatus = $row->getStatus();

        $editUrl = $this->getUrl('*/*/edit', array('id' => $achievementId));
        $saveUrl = $this->getUrl('*/*/save', array('id' => $achievementId));
        $deleteUrl = $this->getUrl('*/*/delete', array('id' => $achievementId));

        $_style = "text-decoration: none; font-size: 16px; padding: 0 3px; cursor: pointer;";
        $_deleteConfirm = Mage::helper('agent')->__('Do you want to delete this achievement?');

        if ($achievementStatus == Ved_Agent_Model_Agentachievement::VERIFIED)
            return false;

        $_editText = Mage::helper('agent')->__('Edit');
        $_verifyText = Mage::helper('agent')->__('Verify');
        $_declineText = Mage::helper('agent')->__('Decline');
        $_requireUpdateText = Mage::helper('agent')->__('Require Update');
        $_deleteText = Mage::helper('agent')->__('Delete');

        $_isDisableEdit = ($achievementStatus == Ved_Agent_Model_Agentachievement::DECLINED) ? "class='disabled'" : "href=\"${editUrl}\"";

        $_isDisableDecline = ($achievementStatus == Ved_Agent_Model_Agentachievement::DECLINED) ? "class='disabled'" : "onclick=\"updateAchievement(true, 2, '{$saveUrl}')\"";

        $_isDisableUpdate = ($achievementStatus == Ved_Agent_Model_Agentachievement::REQUIRE_TO_UPDATE || $achievementStatus == Ved_Agent_Model_Agentachievement::DECLINED) ? "class='disabled'" : "onclick=\"updateAchievement(true, 1, '{$saveUrl}')\"";

        $_isDisableDelete = ($achievementStatus == Ved_Agent_Model_Agentachievement::DECLINED) ? "class='disabled'" : "onclick=\"return confirm('{$_deleteConfirm}')\" href=\"${deleteUrl}\"";

        return "<div>
            <a {$_isDisableEdit} style=\"{$_style}\" title=\"{$_editText}\">
                <i class=\"fa fa-pencil text-success\" aria-hidden=\"true\"></i>
            </a>
            <a onclick=\"updateAchievement(false, 3, '{$saveUrl}')\" style=\"{$_style}\" title=\"{$_verifyText}\">
                <i class=\"fa fa-check-circle\" aria-hidden=\"true\"></i>
            </a>
            <a {$_isDisableDecline} style=\"{$_style}\" title=\"{$_declineText}\">
                <i class=\"fa fa-times-circle\" aria-hidden=\"true\"></i>
            </a>
            <a {$_isDisableUpdate} style=\"{$_style}\" title=\"{$_requireUpdateText}\">
                <i class=\"fa fa-exclamation-circle\" aria-hidden=\"true\"></i>
            </a>
            <a {$_isDisableDelete} style=\"{$_style}\" title=\"{$_deleteText}\">
                <i class=\"fa fa-trash text-danger\" aria-hidden=\"true\"></i>
            </a>
        </div>";
    }

    public function filterAccountLinkAndTypeCallback($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $this->getCollection()->getSelect()->where(
            "CONCAT(account.account_name, ' (', channel.channel_name, ')') LIKE '%{$value}%'"
        );
        return $this;
    }

    private function _getAchievementNameOptions()
    {
        $collection = Mage::getModel('Ved_Agent/agentachievementtype')->getCollection();

        $achievementName = array();

        foreach ($collection as $type) {
            $achievementName[$type->getId()] = $type->getAchievementTypeName();
        }

        return $achievementName;
    }

}
