<?php

class Ved_UserRegionMapping_Block_Adminhtml_Mapping extends Mage_Adminhtml_Block_Template
{
    private $storeCollection;

    private $listSelected;

    private $userId;

    public function __construct()
    {
        parent::__construct();

        $this->storeCollection = Mage::getModel("core/store")->getCollection();

        $this->listSelected = Mage::getResourceModel('userregionmapping/mapping_collection');

        $this->userId = $this->getRequest()->get('user_id');
    }

    public function getListStore()
    {
        return $this->storeCollection->getItems();
    }

    public function getListRegions()
    {
        $cities = array();
        $collection = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter('VN')->load();
        if(count($collection)>0){
            foreach($collection as $item){
                $data = $item->getData();
                $cities = $cities + array($data['region_id']=>$data['default_name']);
            }
        }
        return $cities;
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('user_id' => $this->userId));
    }

    public function getListSelected()
    {
        return $this->listSelected->addFieldToFilter('user_id', $this->userId)->getColumnValues('region_id');
    }

    public function getUserId()
    {
        return $this->userId;
    }
}