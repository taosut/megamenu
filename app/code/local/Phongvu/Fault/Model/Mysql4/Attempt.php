<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Model_Mysql4_Attempt extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('pvfault/attempt', 'attempt_id');
    }
    
    public function clear()
    {    
        $this->_getWriteAdapter()->raw_query('TRUNCATE TABLE `' . $this->getMainTable() . '`');
    }

    public function collect($lastRun)
    {    
        return true;
    }
}