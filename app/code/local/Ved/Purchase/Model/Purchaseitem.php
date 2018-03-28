<?php

/**
 * Class Ved_Purchase_Model_Purchaseitem
 * @method $this setImportQty(int $qty)
 * @method int getRequestQty()
 * @method int getImportQty()
 */
class Ved_Purchase_Model_Purchaseitem extends Mage_Core_Model_Abstract
{
    public function getTypeQueue()
    {
        switch ($this->getType()) {
            case 0:
                return 'HANG_HOA';
            case 2:
                return 'KHUYEN_MAI';
            case 1:
                return 'KY_GUI';
            default :
                return 'HANG_HOA';
        }
    }

    protected function _construct()
    {
        $this->_init("ved_purchase/purchaseitem");
    }

    function _beforeSave()
    {
        $this->setUpdatedAt(now());
        return parent::_beforeSave();
    }

}
