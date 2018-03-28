<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/21/2017
 * Time: 3:36 PM
 */
class Ved_Buildpc_Model_Resource_Buildpc_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
        $this->_init("Ved_Buildpc/buildpc");
    }
}