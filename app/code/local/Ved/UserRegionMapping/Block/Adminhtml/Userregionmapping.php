<?php

class Ved_UserRegionMapping_Block_Adminhtml_Userregionmapping extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ved_userregionmapping/userregionmapping.phtml');
    }

    public function getAddNewUrl()
    {
        return $this->getUrl('*/permissions_user/new/');
    }

    /**
     * Get grid HTML
     *
     * @return unknown
     */
    public function getGridHtml()
    {
        return $this->getChild('grid')->toHtml();
    }
}