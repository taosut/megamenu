<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 12/1/16
 * Time: 15:58
 */
class Ved_Agent_Model_Resource_Agentaccounthistory_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
        $this->_init("Ved_Agent/agentaccounthistory");
    }
}