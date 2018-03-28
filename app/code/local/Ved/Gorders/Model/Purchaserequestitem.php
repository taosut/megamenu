<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 12/7/2016
 * Time: 5:04 PM
 */
class Ved_Gorders_Model_Purchaserequestitem extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ved_gorders/purchaserequestitem');
    }

    public function getDateGMT()
    {
        if ($this->getReceiveDate() == null) {
            return "";
        }
        return DateTime::createFromFormat(
            'Y-m-d H:i:s', $this->getReceiveDate(), new DateTimeZone('UTC'))
            ->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))->format('Y-m-d H:i:s');
    }
}