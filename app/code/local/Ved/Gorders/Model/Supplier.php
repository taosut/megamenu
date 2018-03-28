<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 12/7/2016
 * Time: 5:04 PM
 */
class Ved_Gorders_Model_Supplier extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ved_gorders/supplier');
    }

    public function getJsonInfo()
    {
        $data = [
            'supplier_code' => $this->getSupplierCode(),
            'supplier_mobile' => $this->getSupplierMobile(),
            'supplier_address' => $this->getSupplierAddress(),
            'supplier_contact' => $this->getSupplierContact(),
            'supplier_province' => $this->getSupplierProvince(),
            'supplier_district' => $this->getSupplierDistrict(),
            'note' => $this->getNote(),
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}