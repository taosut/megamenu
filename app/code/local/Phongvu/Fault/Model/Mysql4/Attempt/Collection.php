<?php
/**
 * @author hoangpt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Model_Mysql4_Attempt_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('pvfault/attempt');
    }
    
    public function addStartDateFilter($date)
    {
        $this->addFieldToFilter('date', array('gt'=>$date));
    }  
    
    public function addIpFilter($ip)
    {
        $this->addFieldToFilter('client_ip', $ip);
    }      
}