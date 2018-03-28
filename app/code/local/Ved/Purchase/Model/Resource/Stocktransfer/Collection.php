<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/6/2016
 * Time: 2:57 PM
 */
class Ved_Purchase_Model_Resource_Stocktransfer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
        $this->_init("ved_purchase/stocktransfer");
    }

}