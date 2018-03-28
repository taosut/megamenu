<?php

class Ved_Agent_Block_Adminhtml_Info_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('agent_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('agent')->__('Agent Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label' => Mage::helper('agent')->__('General Information'),
            'title' => Mage::helper('agent')->__('General Information'),
            'content' => $this->getLayout()->createBlock('agent/adminhtml_info_edit_general_form')->toHtml(),
        ));

        $this->addTab('accounts', array(
            'label' => Mage::helper('agent')->__('Accounts List'),
            'title' => Mage::helper('agent')->__('Accounts List'),
            'url' => $this->getUrl('*/agent_account/grid', array('_current' => true)),
            'content' => $this->getLayout()->createBlock('agent/adminhtml_account_grid')->toHtml(),
            'class' => 'ajax'
        ));

        $this->addTab('achievement_list', array(
            'label' => Mage::helper('agent')->__('Achievements List'),
            'title' => Mage::helper('agent')->__('Achievements List'),
            'url' => $this->getUrl('*/agent_achievement/grid', array('_current' => true)),
            'content' => $this->getLayout()->createBlock('agent/adminhtml_achievement_grid')->toHtml(),
            'class' => 'ajax'
        ));

        $this->addTab('refered_list', array(
            'label' => Mage::helper('agent')->__('Refered List'),
            'title' => Mage::helper('agent')->__('Refered List'),
            'url' => $this->getUrl('*/agent_info/referedGrid', array('_current' => true)),
            'content' => $this->getLayout()->createBlock('agent/adminhtml_info_edit_tabs_refer_grid')->toHtml(),
            'class' => 'ajax'
        ));

        $this->addTab('redemption_history', array(
            'label' => Mage::helper('agent')->__('Redemption History'),
            'title' => Mage::helper('agent')->__('Redemption History'),
            'url' => $this->getUrl('*/agent_redemption/grid', array('_current' => true)),
            'content' => $this->getLayout()->createBlock('agent/adminhtml_redemption_grid')->toHtml(),
            'class' => 'ajax'
        ));

        $this->addTab('point_history', array(
            'label' => Mage::helper('agent')->__('Point History'),
            'title' => Mage::helper('agent')->__('Point History'),
            'url' => $this->getUrl('*/agent_info/pointHistoryGrid', array('_current' => true)),
            'content' => $this->getLayout()->createBlock('agent/adminhtml_info_edit_tabs_point_grid')->toHtml(),
            'class' => 'ajax'
        ));

        return parent::_beforeToHtml();
    }
}
