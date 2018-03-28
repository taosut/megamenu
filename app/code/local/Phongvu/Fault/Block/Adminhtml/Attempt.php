<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Block_Adminhtml_Attempt extends Phongvu_Fault_Block_Adminhtml_Abstract
{
    public function __construct()
    {
        $this->_header    = 'Failed Login Attempts';
        $this->_modelName = 'attempt';
        parent::__construct();
    }
}