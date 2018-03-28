<?php
class Ved_Agent_Model_Agentachievementhistory extends Mage_Core_Model_Abstract
{
    const VERIFY_ACHIEVEMENT = 1;
    const REDEMPTION = 2;
    const AFFILIATE = 3;
    const OTHER_ADD = 4;
    const OTHER_SUB = 5;

    public function _construct()
    {
        $this->_init('Ved_Agent/agentachievementhistory');
    }

}
