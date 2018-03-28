<?php

class Ved_Agent_Adminhtml_Agent_RedemptionController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Tekshop Agent'))->_title($this->__('Redemption History'));

        $this->loadLayout();

        $this->_setActiveMenu('agent/agent_redemption');
        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_redemption'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('agent/adminhtml_redemption_grid')->toHtml()
        );
    }

}
