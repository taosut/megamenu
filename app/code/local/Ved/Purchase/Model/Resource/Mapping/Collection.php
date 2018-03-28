<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 12/1/16
 * Time: 17:16
 */
class Ved_Purchase_Model_Resource_Mapping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct(){
        $this->_init("ved_purchase/mapping");
    }

}