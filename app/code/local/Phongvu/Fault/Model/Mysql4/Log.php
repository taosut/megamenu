<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('pvfault/log', 'log_id');
    }
    
    public function clear()
    {    
        $this->_getWriteAdapter()->raw_query('truncate table ' . $this->getMainTable());
    }
    
    public function collect($lastRun)
    {    
        return true;
    }

    public function hasRedirect($path, $storeId)
    {
        $read = $this->_getReadAdapter();
        $tbl  = $this->getTable('core/url_rewrite');

        $sql = $read->select()->from($tbl, 'request_path')
            ->where('request_path = ?', $path)
			->where('store_id = ?', (int) $storeId)
            ->limit(1);

        return $read->fetchCol($sql);
    }

	public function errorWasLogged($path, $storeId)
	{
		$read = $this->_getReadAdapter();

		$sql = $read->select()->from($this->getMainTable(), 'COUNT(' . $this->getIdFieldName() . ')')
			->where('url = ?', $path)
			->where('store_id = ?', (int) $storeId);

		return (int) $read->fetchOne($sql) > 0;
	}

}