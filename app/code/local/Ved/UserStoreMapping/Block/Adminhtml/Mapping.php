<?php

class Ved_UserStoreMapping_Block_Adminhtml_Mapping extends Mage_Adminhtml_Block_Template
{
    private $storeCollection;

    private $listSelected;

    private $userId;

    public function __construct()
    {
        parent::__construct();

        $this->storeCollection = Mage::getModel("core/store")->getCollection();

        $this->listSelected = Mage::getResourceModel('userstoremapping/mapping_collection');

        $this->userId = $this->getRequest()->get('user_id');
    }

    public function getListStore()
    {
        return $this->storeCollection->getItems();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('user_id' => $this->userId));
    }

    public function getListSelected()
    {
        return $this->listSelected->addFieldToFilter('user_id', $this->userId)->getColumnValues('store_id');
    }

    public function getUserId()
    {
        return $this->userId;
    }
}