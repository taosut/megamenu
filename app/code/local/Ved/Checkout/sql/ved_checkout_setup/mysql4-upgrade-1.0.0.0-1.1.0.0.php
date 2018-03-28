<?php

/**
 * @var Mage_Core_Model_Resource_Setup $this
 */

$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('ved_checkout/quote'), 'old_grand_total', array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'nullable' => false,
        'after' => null,
        'comment' => 'Old Grand Total for Notification'
    ));
$installer->endSetup();