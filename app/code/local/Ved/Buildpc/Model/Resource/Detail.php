<?php
class Ved_Buildpc_Model_Resource_Detail extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ved_Buildpc/detail', 'entity_id');
    }
}