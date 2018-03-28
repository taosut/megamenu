<?php

/**
 * Created by PhpStorm.
 * User: Van Dung Bui
 * Date: 12/7/2016
 * Time: 5:04 PM
 */

/**
 * Class Ved_Gorders_Model_Resource_Orderitemstock
 * @method $this setImportQuantity(int $int)
 * @method int getRequestQuantity()
 */
class Ved_Gorders_Model_Resource_Orderitemstock extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('ved_gorders/orderitemstock', 'id');
    }
}