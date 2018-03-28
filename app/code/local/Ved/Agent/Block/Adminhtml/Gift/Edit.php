<?php

class Ved_Agent_Block_Adminhtml_Gift_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';

        $this->_blockGroup = 'agent';
        $this->_controller = 'adminhtml_gift';

        $this->_updateButton('delete', 'label', Mage::helper('agent')->__('Delete Redemption Gift'));
    }

    public function getHeaderText()
    {
        if (Mage::registry('redemption_gift') && Mage::registry('redemption_gift')->getId()) {
            return Mage::helper('agent')->__('Edit Redemption Gift #%s', Mage::registry('redemption_gift')->getId());
        } else {
            return Mage::helper('agent')->__('Add New Redemption Gift');
        }
    }
}
