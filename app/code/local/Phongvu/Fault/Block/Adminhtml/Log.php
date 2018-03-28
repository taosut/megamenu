<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Block_Adminhtml_Log extends Phongvu_Fault_Block_Adminhtml_Abstract
{
    public function __construct()
    {
        $this->_header    = 'Not Found Pages';
        $this->_modelName = 'log';
        parent::__construct();
    }
}