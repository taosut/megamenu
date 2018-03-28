<?php
class Ved_Agent_Model_Agentachievement extends Mage_Core_Model_Abstract
{
    const WAITING_TO_VERIFY = 0;
    const REQUIRE_TO_UPDATE = 1;
    const DECLINED = 2;
    const VERIFIED = 3;

    public function _construct()
    {
        $this->_init('Ved_Agent/agentachievement');
    }

}
