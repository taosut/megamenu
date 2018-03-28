<?php
class Ved_Agent_Model_Resource_Agentpointhistory extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ved_Agent/agent_point_history', 'point_history_id');
    }
}