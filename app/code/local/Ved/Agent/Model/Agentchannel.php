<?php
class Ved_Agent_Model_Agentchannel extends Mage_Core_Model_Abstract
{
    const SOCIAL = 1;
    const FORUM = 2;

    public function _construct()
    {
        $this->_init('Ved_Agent/agentchannel');
    }

}
