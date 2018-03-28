<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Model_Log extends Phongvu_Fault_Model_Abstract
{
    protected $modelName = 'log';

    public function hasRedirect()
    {
        return $this->getResource()->hasRedirect($this->getRequestPath(), $this->getStoreId());
    }

}