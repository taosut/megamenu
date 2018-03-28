<?php
/**
 * @author hoang.pt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Block_Adminhtml_Abstract extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_header    = 'Not Found Pages';
    protected $_modelName = 'log';
    
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_' . $this->_modelName;
        $this->_blockGroup = 'pvfault';
        $this->_headerText = Mage::helper('pvfault')->__($this->_header);
        $this->_removeButton('add'); 
        
        $confirm = "'". Mage::helper('pvfault')->__('Are you sure?') . "'";
        $this->_addButton('truncate_log', array(
            'label'     => Mage::helper('core')->__('Clear Log'),
            'onclick'   => 'if (confirm('.$confirm.')) {setLocation(\'' . $this->getUrl('*/*/clear')  .'\')}',
            'class'     => 'delete',
        )); 
    }
}