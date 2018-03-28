<?php

class Ved_Agent_Block_Adminhtml_Info_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_info_grid');
        $this->setDefaultSort('waiting_verify_post');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('agent_point_id');

        $this->getMassactionBlock()->addItem('change_point_add', array(
            'label'=> Mage::helper('agent')->__('Add Point'),
            'url'  => $this->getUrl('*/*/massChangePoint', array('_current' => true, 'type' => Ved_Agent_Model_Agentachievementhistory::OTHER_ADD)),
            'confirm' => Mage::helper('agent')->__('Are you sure?'),
            'additional' => array(
                'point' => array(
                    'name' => 'point',
                    'type' => 'text',
                    'class' => 'required-entry validate-number validate-not-negative-number',
                    'label' => Mage::helper('agent')->__('Point'),
                ),
                'reason' => array(
                    'name' => 'reason',
                    'type' => 'text',
                    'class' => 'required-entry',
                    'label' => Mage::helper('agent')->__('Reason'),
                )
            )
        ));

        $this->getMassactionBlock()->addItem('change_point_sub', array(
            'label'=> Mage::helper('agent')->__('Substract Point'),
            'url'  => $this->getUrl('*/*/massChangePoint', array('_current' => true, 'type' => Ved_Agent_Model_Agentachievementhistory::OTHER_SUB)),
            'confirm' => Mage::helper('agent')->__('Are you sure?'),
            'additional' => array(
                'point' => array(
                    'name' => 'point',
                    'type' => 'text',
                    'class' => 'required-entry validate-number validate-not-negative-number',
                    'label' => Mage::helper('agent')->__('Point'),
                ),
                'reason' => array(
                    'name' => 'reason',
                    'type' => 'text',
                    'class' => 'required-entry',
                    'label' => Mage::helper('agent')->__('Reason'),
                )
            )
        ));

        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToFilter('is_agent', 1)
            ->addAttributeToFilter('website_id', 20)
            ->addAttributeToFilter('agent_info', array('like' => '%')) // To get agent_info in query
            ->addAttributeToSelect(array('firstname', 'phone_number', 'agent_info', 'agent_code', 'is_agent_deleted'));

        $collection->getSelect()
            ->joinLeft(
                array('achievement' => 'agent_achievement'),
                'achievement.user_id = e.entity_id AND achievement.is_deleted = 0',
                array('achievement_id', 'status'))
            ->joinLeft(
                array('point' => 'agent_point_history'),
                'point.user_id = e.entity_id AND point.type = 3',
                array('object_id'))
            ->columns('COUNT(distinct case status when "0" then achievement_id else null end) AS waiting_verify_post')
            ->columns('COUNT(distinct case status when "1" then achievement_id else null end) AS require_update_post')
            ->columns('COUNT(distinct case status when "2" then achievement_id else null end) AS declined_post')
            ->columns('COUNT(distinct case status when "3" then achievement_id else null end) AS verified_post')
            ->columns('COUNT(distinct object_id) AS refered_num')
            // ->columns("JSON_EXTRACT(`at_agent_info`.`value`, '$.available_point') as available_point")
            // ->columns("JSON_EXTRACT(`at_agent_info`.`value`, '$.used_point') as used_point")
            ->group('e.entity_id');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();

        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            switch ($columnIndex) {
                case 'waiting_verify_post':
                case 'require_update_post':
                case 'declined_post':
                case 'verified_post':
                case 'refered_num':
                // case 'available_point':
                // case 'used_point':
                    $collection->getSelect()->order($columnIndex.' '.strtoupper($column->getDir()));
                    break;
                default:
                    parent::_setCollectionOrder($column);
                    break;
            }
        }

        return $collection;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('agent_code', array(
            'index' => 'agent_code',
            'header' => Mage::helper('agent')->__('Agent Code Id'),
            'sortable' => true,
            'width' => '100px',
        ));

        $this->addColumn('firstname', array(
            'index' => 'firstname',
            'header' => Mage::helper('agent')->__('Name'),
            'sortable' => true,
        ));

        $this->addColumn('phone_number', array(
            'index' => 'phone_number',
            'header' => Mage::helper('agent')->__('Phone Number'),
            'sortable' => true,
        ));

        $this->addColumn('email', array(
            'index' => 'agent_info',
            'header' => Mage::helper('agent')->__('Email'),
            'sortable' => true,
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Agent_Email',
            'filter_condition_callback' => array($this, 'filterEmailCallback')
        ));

        $this->addColumn('is_agent_deleted', array(
            'index' => 'is_agent_deleted',
            'header' => Mage::helper('agent')->__('Is Deleted?'),
            'sortable' => true,
            'type' => 'options',
            'align' => 'center',
            'options' => array(
                0 => Mage::helper('agent')->__('No'),
                1 => Mage::helper('agent')->__('Yes'),
            ),
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

        $this->addColumn('refered_num', array(
            'index' => 'refered_num',
            'header' => Mage::helper('agent')->__('# Refered'),
            'sortable' => true,
            'filter' => false,
            'type' => 'number',
            'width' => '100px',
        ));

        $this->addColumn('available_point', array(
            'index' => 'agent_info',
            'header' => Mage::helper('agent')->__('Available Point'),
            'sortable' => false,
            'filter' => false,
            // 'type' => 'number',
            'width' => '100px',
            'align' => 'right',
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Agent_AvailablePoint'
        ));

        $this->addColumn('used_point', array(
            'index' => 'agent_info',
            'header' => Mage::helper('agent')->__('Used Point'),
            'sortable' => false,
            'filter' => false,
            // 'type' => 'number',
            'width' => '100px',
            'align' => 'right',
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Agent_UsedPoint'
        ));

        $this->addColumn('action', array(
            'index' => 'entity_id',
            'header' => Mage::helper('agent')->__('Action'),
            'frame_callback' => array($this, 'prepareActionLayout'),
            'align' => 'center',
            'sortable' => false,
            'filter' => false,
            'width' => '150px',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function prepareStatusLayout($postNum, $row, $status)
    {
        if ($postNum == 0) {
            return $postNum;
        } else {
            $url = $this->getUrl('*/agent_achievement', array(
                'id' => $row->getId(),
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

    public function prepareActionLayout($agentId, $row)
    {
        $isDeleted = $row->getIsAgentDeleted();
        $editUrl = $this->getUrl('*/*/edit', array('id' => $agentId));
        $deleteUrl = $this->getUrl('*/*/delete', array('id' => $agentId));
        $recoverUrl = $this->getUrl('*/*/recover', array('id' => $agentId));

        return '<div>
            <a href="'.$editUrl.'" style="padding-right: 5px">'.Mage::helper('agent')->__('Edit').'</a>'.
            (!$isDeleted ? '<a href="'.$deleteUrl.'" onclick="return confirm(\''.Mage::helper('agent')->__('Do you want to delete this agent?').' '.Mage::helper('agent')->__('Accounts, achievements are also deleted.').'\')">'.Mage::helper('agent')->__('Delete').'</a>' :
            '<a href="'.$recoverUrl.'" onclick="return confirm(\''.Mage::helper('agent')->__('Do you want to recover this agent?').' '.Mage::helper('agent')->__('Accounts, achievements are also recovered.').'\')">'.Mage::helper('agent')->__('Recover').'</a>').
        '</div>';
    }

    public function filterEmailCallback($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $this->getCollection()->getSelect()->where(
            "at_agent_info.value LIKE '%\"email\":\"%{$value}%\",%,%,%,%,%'"
        );
        return $this;
    }

}
