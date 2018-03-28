<?php
class Ved_Fortunewheel_Model_Resource_WheelDetail extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ved_Fortunewheel/wheelDetail', 'entity_id');
    }
}