<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/6/2016
 * Time: 2:57 PM
 */
class Ved_Purchase_Model_Requestitem extends Mage_Core_Model_Abstract
{
    protected function _construct(){

        $this->_init("ved_purchase/requestitem");

    }

    protected function _beforeSave()
    {
        if ($this->getReceiveDate() == null) {
            return $this;
        }
        if (is_a($this->getReceiveDate(), DateTime::class)) {
            $this->setReceiveDate($this->getReceiveDate()->setTimeZone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
        }

        return parent::_beforeSave();
    }
}
