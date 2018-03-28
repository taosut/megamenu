<?php
class Ved_Stockrequest_Model_Resource_Stockrequest extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('ved_stockrequest/stockrequest', 'stock_request_id');
    }
}