<?php

class Ved_UserRegionMapping_Adminhtml_UserregionmappingController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/acl')
            ->_addBreadcrumb($this->__('System'), $this->__('System'))
            ->_addBreadcrumb($this->__('Permissions'), $this->__('Permissions'))
            ->_addBreadcrumb($this->__('Users'), $this->__('Users'));
        return $this;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__("Mapping User with Region"));

        $this->_addContent($this->getLayout()->createBlock('ved_userregionmapping/adminhtml_userregionmapping'))->renderLayout();
    }

    public function mappingAction()
    {
        $this->loadLayout();

        $this->_title($this->__("Mapping User with Region"));

        $this->renderLayout();

    }

    public function saveAction()
    {

        if ($data = $this->getRequest()->getPost()) {
            $user_id = $this->getRequest()->get('user_id');

            if ($user_id) {
                Mage::getResourceModel('userregionmapping/mapping_collection')
                    ->addFieldToFilter('user_id', $user_id)
                    ->load()
                    ->delete();
            }

            foreach ($data['region'] as $index => $region) {
                if (isset($region['region_id']) && $region['region_id']) {
                    Mage::getModel('userregionmapping/mapping')->setData($region)->save();
                }
            }
            $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        }

    }

    public function roleGridAction()
    {
        $this->loadLayout();

        $this->getResponse()->setBody($this->getLayout()->createBlock('ved_userregionmapping/adminhtml_grid_role')->toHtml());
    }
}