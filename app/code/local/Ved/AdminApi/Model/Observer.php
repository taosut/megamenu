<?php

class Ved_AdminApi_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function addRelationshipData($observer = null)
    {
        $data_object = $observer->getEvent()->getData('data_object');
        $region = Mage::getModel('teko_amp/region')->load($data_object->getRegionId());
        $data_object->setRegion($region);
    }
}