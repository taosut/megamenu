<?php
/**
 * @author hoangpt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Controller_Abstract extends Mage_Adminhtml_Controller_Action
{
    protected $_title     = 'Not found pages';
    protected $_modelName = 'log';
    
	public function indexAction() 
	{
	    try {
            $result = Mage::getModel('pvfault/' . $this->_modelName)->collect();
            if (is_array($result)){ // add errors to the session
                foreach ($result as $err){
	               Mage::getSingleton('adminhtml/session')->addError($err);                    
                }
            }
	    }
        catch (Exception $e) {
            print_r($e->getMessage()); // todo
        }

        $this->loadLayout(); 
        $this->_setActiveMenu('report/pvfault');
        $this->_addBreadcrumb($this->__('Reports'), $this->__($this->_title)); 
        $this->_title($this->__($this->_title))
             ->_title($this->__('Reports'))
             ->_title($this->__('Errors and Missing Pages'))
        ;         
        $this->_addContent($this->getLayout()->createBlock('pvfault/adminhtml_' . $this->_modelName));
            $this->renderLayout();
	}
	
    public function clearAction() 
	{
	    Mage::getModel('pvfault/' . $this->_modelName)->clear();
	    Mage::getSingleton('adminhtml/session')->addSuccess(
	       $this->__('%s have been cleared', $this->__($this->_title)
	    )); 
	    $this->_redirect('*/*/'); 
	}
        
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/pvfault');
    }

}