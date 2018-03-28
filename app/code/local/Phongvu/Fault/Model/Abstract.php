<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Model_Abstract extends Mage_Core_Model_Abstract
{
    protected $modelName = 'log';
    
    public function _construct()
    {    
        $this->_init('pvfault/' . $this->modelName);
    }
    
    public function clear()
    {
        return $this->getResource()->clear();
    }
    
    public function getCountFrom($lastRun=0)
    {
        $this->collect($lastRun);
        $collection = $this->getCollection()
            ->addFieldToFilter('date', array('gt' => date('Y-m-d H:i:s', $lastRun)));
        return $collection->count();        
    }
    
    public function collect($lastRun=0)
    {
        return $this->getResource()->collect($lastRun);
    }    
}