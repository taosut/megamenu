<?php

class Ved_Agent_Block_Adminhtml_Redemption_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_redemption_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/agent_redemption/grid', array('_current' => true));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Ved_Agent/agentredemption')->getCollection();

        if ($agentId = $this->getRequest()->getParam('id')) {
            $collection->addFieldToFilter('main_table.user_id', $agentId);
        }

        $collection->join(
            array('coupon' => 'salesrule/coupon'),
            'coupon.coupon_id = main_table.coupon_id',
            array(
                'coupon_code' => 'coupon.code',
                'coupon_is_used' => new Zend_Db_Expr('CASE WHEN coupon.times_used = 0 THEN 0 ELSE 1 END')
            ), null, 'left'
        )->join(
            array('gift' => 'agent_redemption_gift'),
            'gift.redemption_gift_id = main_table.redemption_gift_id',
            array(
                'redemption_gift_name' => 'gift.redemption_gift_name'
            ), 'gift.is_deleted = 0', 'left'
        )->join(
            array('rule' => 'salesrule/rule'),
            'rule.rule_id = gift.rule_id',
            array(
                'sale_rule_name' => 'rule.name'
            ), null, 'left'
        )->join(
            array('point' => 'agent_point_history'),
            'point.object_id = main_table.redemption_id AND point.type = 2',
            array(
                'redemption_spent_point' => 'point.point'
            ), null, 'left'
        );

        // Join user firstname
        $firstname = Mage::getResourceModel('customer/customer')->getAttribute('firstname');
        $firstnameTable = $firstname->getBackend()->getTable();
        $collection->getSelect()->joinLeft(
            array('cus' => $firstnameTable),
            'main_table.user_id = cus.entity_id and cus.attribute_id = '.(int) $firstname->getAttributeId(),
            array(
                'agent_name' => 'cus.value'
            )
        );

        $agentInfo = Mage::getResourceModel('customer/customer')->getAttribute('agent_info');
        $collection->getSelect()->join(
            array('cus_info' => $agentInfo->getBackend()->getTable()),
            'main_table.user_id = cus_info.entity_id and cus_info.attribute_id = '.(int) $agentInfo->getAttributeId(),
            array(
                'agent_info' => 'cus_info.value'
            )
        );

        $isAgentDeleted = Mage::getResourceModel('customer/customer')->getAttribute('is_agent_deleted');
        $collection->getSelect()->join(
            array('cus_att' => $isAgentDeleted->getBackend()->getTable()),
            'main_table.user_id = cus_att.entity_id and cus_att.attribute_id = '.(int) $firstname->getAttributeId().' and cus_att.value = 0',
            null
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
        $this->addColumn('coupon_code', array(
            'index' => 'coupon_code',
            'filter_index' => 'coupon.code',
            'header' => Mage::helper('agent')->__('Coupon Code'),
            'sortable' => true,
            'width' => '100px',
        ));

        if (!$this->getRequest()->getParam('id')) {
            $this->addColumn('agent_code', array(
                'index' => 'agent_code',
                'filter_index' => 'cus_code.value',
                'header' => Mage::helper('agent')->__('Agent Code Id'),
                'sortable' => true,
                'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Agent_Code'
            ));
            $this->addColumn('agent_name', array(
                'index' => 'agent_name',
                'filter_index' => 'cus.value',
                'header' => Mage::helper('agent')->__('Agent Name'),
                'sortable' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'index' => 'created_at',
            'header' => Mage::helper('agent')->__('Created At'),
            'type' => 'datetime',
            'sortable' => true,
            'renderer' => 'Ved_Agent_Block_Adminhtml_Renderer_Datetime',
        ));

        $this->addColumn('redemption_gift_name', array(
            'index' => 'redemption_gift_name',
            'header' => Mage::helper('agent')->__('Gift Name'),
            'sortable' => true,
        ));

        $this->addColumn('redemption_spent_point', array(
            'index' => 'redemption_spent_point',
            'filter_index' => 'point.point',
            'header' => Mage::helper('agent')->__('Value (Spent Point)'),
            'type' => 'number',
            'sortable' => true,
        ));

        $this->addColumn('coupon_is_used', array(
            'index' => 'coupon_is_used',
            'header' => Mage::helper('agent')->__('Is Coupon Used?'),
            'sortable' => true,
            'width' => '100px',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('agent')->__('No'),
                1 => Mage::helper('agent')->__('Yes'),
            ),
            'filter_condition_callback' => array($this, 'filterCouponIsUsedCallback')
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function filterCouponIsUsedCallback($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        if ($value == 0) {
            $this->getCollection()->getSelect()->where(
                 "coupon.times_used = 0");
        } else {
            $this->getCollection()->getSelect()->where(
                 "coupon.times_used = 1");
        }

        return $this;
    }
}
