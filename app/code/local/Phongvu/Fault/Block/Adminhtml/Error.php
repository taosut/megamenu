<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Block_Adminhtml_Error extends Phongvu_Fault_Block_Adminhtml_Abstract
{
    public function __construct()
    {
        $this->_header    = 'System Errors';
        $this->_modelName = 'error';
        parent::__construct();
    }
}