<?php
class Ved_Agent_Model_Resource_Agentachievementhistory extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ved_Agent/agent_achievement_history', 'achievement_history_id');
    }
}