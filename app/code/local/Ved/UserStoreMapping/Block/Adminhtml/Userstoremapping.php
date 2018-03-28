<?php

class Ved_UserStoreMapping_Block_Adminhtml_Userstoremapping extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ved_userstoremapping/userstoremapping.phtml');
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