<?php
/**
 * @author hoangpt
 * @package Phongvu_Fault
 */
class Phongvu_Fault_Block_Adminhtml_Log_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'pvfault';
        $this->_controller = 'adminhtml_log';
        
        $this->_removeButton('reset');
        $this->_removeButton('delete');

        $this->_addButton('saveAndDeleteThis', array(
            'label'     => Mage::helper('adminhtml')->__('Save and Delete this Entry'),
            'onclick'   => 'saveAndDelete(\'' . $this->getUrl('*/*/save',
                array('type' => Phongvu_Fault_Helper_Data::TYPE_THIS, 'id' => $this->getRequest()->getParam('id'))) . '\')'
        ), -100);

        $this->_formScripts[] = "
            function saveAndDelete(url) {
                editForm.submit(url);
            }
        ";
    }

    public function getHeaderText()
    {
        return Mage::helper('pvfault')->__('Create Redirect');
    }
}