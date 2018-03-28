<?php

class Teko_Amp_Block_Adminhtml_Amp_Config extends Mage_Page_Block_Html
{
    /**
     * @return Teko_Amp_Model_Resource_Config_Collection
     */
    public function getConfig()
    {
        return Mage::getResourceModel('teko_amp/config_collection')->load();
    }

    public function getUpdateUrl()
    {
        return $this->getUrl('*/*/update');
    }

}