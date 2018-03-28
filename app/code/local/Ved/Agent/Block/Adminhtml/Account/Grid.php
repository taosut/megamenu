<?php

class Ved_Agent_Block_Adminhtml_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_account_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/agent_account/grid', array('_current' => true));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Ved_Agent/agentaccount')
            ->getCollection()
            ->addFieldToFilter('main_table.is_deleted', 0);

        if ($agentId = $this->getRequest()->getParam('id')) {
            $collection->addFieldToFilter('main_table.user_id', $agentId);
        }

        $collection->join(
            array('channel' => 'agent_channel'),
            'channel.channel_id = main_table.channel_id',
            array(
                'channel_name' => 'channel.channel_name',
                'channel_type' => 'channel.channel_type'
            ), 'channel.is_deleted = 0', 'left'
        );

        $collection->getSelect()->joinLeft(
            array('achievement' => 'agent_achievement'),
            'main_table.account_id = achievement.account_id AND achievement.is_deleted = 0',
            null
        );

        // Join user firstname
        $firstname = Mage::getResourceModel('customer/customer')->getAttribute('firstname');
        $collection->getSelect()->joinLeft(
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

        $collection->getSelect()
            ->columns('COUNT(case achievement.status when "0" then 1 else null end) AS waiting_verify_post')
            ->columns('COUNT(case achievement.status when "1" then 1 else null end) AS require_update_post')
            ->columns('COUNT(case achievement.status when "2" then 1 else null end) AS declined_post')
            ->columns('COUNT(case achievement.status when "3" then 1 else null end) AS verified_post')
            ->group('main_table.account_id');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('channel_type', array(
            'index' => 'channel_type',
            'header' => Mage::helper('agent')->__('Channel Type'),
            'sortable' => true,
            'type' => 'options',
            'options' => array(
                1 => Mage::helper('agent')->__('Social Network'),
                2 => Mage::helper('agent')->__('Forum')),
        ));

        $this->addColumn('channel_name', array(
            'index' => 'channel_name',
            'header' => Mage::helper('agent')->__('Channel Name'),
            'sortable' => true,
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

        $this->addColumn('account_name', array(
            'index' => 'account_name',
            'header' => Mage::helper('agent')->__('Account Name'),
            'sortable' => true,
        ));

        $this->addColumn('waiting_verify_post', array(
            'index' => 'waiting_verify_post',
            'header' => Mage::helper('agent')->__('# of Waiting to Verify Post'),
            'sortable' => true,
            'filter' => false,
            'type' => 'number',
            'width' => '175px',
            'frame_callback' => array($this, 'prepareWaitingVerify'),
        ));

        $this->addColumn('require_update_post', array(
            'index' => 'require_update_post',
            'header' => Mage::helper('agent')->__('# of Require to Update Post'),
            'sortable' => true,
            'filter' => false,
            'type' => 'number',
            'width' => '175px',
            'frame_callback' => array($this, 'prepareRequireUpdate'),
        ));

        $this->addColumn('declined_post', array(
            'index' => 'declined_post',
            'header' => Mage::helper('agent')->__('# of Declined Post'),
            'sortable' => true,
            'filter' => false,
            'type' => 'number',
            'width' => '175px',
            'frame_callback' => array($this, 'prepareDeclined'),
        ));

        $this->addColumn('verified_post', array(
            'index' => 'verified_post',
            'header' => Mage::helper('agent')->__('# of Verified Post'),
            'sortable' => true,
            'filter' => false,
            'type' => 'number',
            'width' => '175px',
            'frame_callback' => array($this, 'prepareVerified'),
        ));

        $this->addColumn('status', array(
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'header' => Mage::helper('agent')->__('Status'),
            'sortable' => true,
            'type' => 'options',
            'options' => array(
                Ved_Agent_Model_Agentaccount::WAITING_TO_VERIFY => Mage::helper('agent')->__('Waiting to Verify'),
                // Ved_Agent_Model_Agentaccount::REQUIRE_TO_UPDATE => Mage::helper('agent')->__('Require to Update'),
                Ved_Agent_Model_Agentaccount::DECLINED => Mage::helper('agent')->__('Declined'),
                Ved_Agent_Model_Agentaccount::VERIFIED => Mage::helper('agent')->__('Verified')),
        ));

        if (!$this->getRequest()->getParam('id')) {
            $this->addColumn('action', array(
                'index' => 'account_id',
                'header' => Mage::helper('agent')->__('Action'),
                'frame_callback' => array($this, 'prepareActionLayout'),
                'align' => 'center',
                'sortable' => false,
                'filter' => false,
                'width' => '175px'
            ));
        }

        $this->addColumn('history', array(
            'index' => 'account_id',
            'header' => Mage::helper('agent')->__('History'),
            'frame_callback' => array($this, 'prepareHistoryLayout'),
            'sortable' => false,
            'filter' => false,
            'align' => 'center'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function prepareStatusLayout($postNum, $row, $status)
    {
        if ($postNum == 0) {
            return $postNum;
        } else {
            $url = $this->getUrl('*/agent_achievement', array(
                'account_id' => $row->getId(),
                'status' => $status
            ));
            return "<a href=\"{$url}\">{$postNum}</a>";
        }
    }

    public function prepareWaitingVerify($postNum, $row)
    {
        return $this->prepareStatusLayout($postNum, $row, 0);
    }

    public function prepareRequireUpdate($postNum, $row)
    {
        return $this->prepareStatusLayout($postNum, $row, 1);
    }

    public function prepareDeclined($postNum, $row)
    {
        return $this->prepareStatusLayout($postNum, $row, 2);
    }

    public function prepareVerified($postNum, $row)
    {
        return $this->prepareStatusLayout($postNum, $row, 3);
    }

    public function prepareActionLayout($value, $row)
    {
        $accountId = $row->getAccountId();
        $accountStatus = $row->getStatus();
        $achieveNumber = $row->getWaitingVerifyPost() + $row->getRequireUpdatePost() + $row->getDeclinedPost() + $row->getVerifiedPost();

        $editUrl = $this->getUrl('*/*/edit', array('id' => $accountId));
        $saveUrl = $this->getUrl('*/*/save', array('id' => $accountId));
        $deleteUrl = $this->getUrl('*/*/delete', array('id' => $accountId));

        $_style = "text-decoration: none; font-size: 16px; padding: 0 3px; cursor: pointer;";
        $_deleteConfirm = Mage::helper('agent')->__('Do you want to delete this account?');

        $_verifyText = Mage::helper('agent')->__('Verify');
        $_declineText = Mage::helper('agent')->__('Decline');
        $_deleteText = Mage::helper('agent')->__('Delete');

        $_isDisableVerify = ($accountStatus == Ved_Agent_Model_Agentaccount::VERIFIED) ? "class='disabled'" : "onclick=\"updateAccount(false, 3, '{$saveUrl}')\"";
        $_isDisableDecline = ($accountStatus == Ved_Agent_Model_Agentaccount::DECLINED) ? "class='disabled'" : "onclick=\"updateAccount(true, 2, '{$saveUrl}')\"";
        $_isDisableDelete = ($achieveNumber > 0) ? "class='disabled'" : "onclick=\"return confirm('{$_deleteConfirm}')\" href=\"${deleteUrl}\"";

        return "<div>
            <a {$_isDisableVerify} style=\"{$_style}\" title=\"{$_verifyText}\">
                <i class=\"fa fa-check-circle\" aria-hidden=\"true\"></i>
            </a>
            <a {$_isDisableDecline} style=\"{$_style}\" title=\"{$_declineText}\">
                <i class=\"fa fa-times-circle\" aria-hidden=\"true\"></i>
            </a>
            <a {$_isDisableDelete} style=\"{$_style}\" title=\"{$_deleteText}\">
                <i class=\"fa fa-trash text-danger\" aria-hidden=\"true\"></i>
            </a>
        </div>";
    }

    public function prepareHistoryLayout($accountId)
    {
        $historyUrl = $this->getUrl('*/agent_account/viewHistory', array('id' => $accountId));

        return "<a href=\"{$historyUrl}\" style='cursor: pointer'>Lịch sử</a>";
    }
}
