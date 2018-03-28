<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/7/2016
 * Time: 4:47 PM
 */
class Ved_Purchase_Model_Resource_Stocktransferitem_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
        $this->_init("ved_purchase/stocktransferitem");
    }

}