<?php

class Ved_UserStoreMapping_Adminhtml_UsermappingController extends Mage_Adminhtml_Controller_Action
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
        $this->_title($this->__("Mapping User with Store"));

        $this->_addContent($this->getLayout()->createBlock('ved_userstoremapping/adminhtml_userstoremapping'))->renderLayout();
    }

    public function mappingAction()
    {
        $this->loadLayout();

        $this->_title($this->__("Mapping User with Store"));

        $this->renderLayout();

    }

    public function saveAction()
    {

        if ($data = $this->getRequest()->getPost()) {
            $user_id = $this->getRequest()->get('user_id');

            if ($user_id) {
                Mage::getResourceModel('userstoremapping/mapping_collection')
                    ->addFieldToFilter('user_id', $user_id)
                    ->load()
                    ->delete();
            }

            foreach ($data['store'] as $index => $store) {
                if (isset($store['store_id']) && $store['store_id']) {
                    Mage::getModel('userstoremapping/mapping')->setData($store)->save();
                }
            }
            $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        }

    }

    public function roleGridAction()
    {
        $this->loadLayout();

        $this->getResponse()->setBody($this->getLayout()->createBlock('ved_userstoremapping/adminhtml_grid_role')->toHtml());
    }
}