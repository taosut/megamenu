<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/12/2017
 * Time: 1:53 PM
 */

class Ved_Agent_Block_AgentList extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Tekshop Agent List');
        $this->setTemplate('agent/agent_list.phtml');
    }
}
