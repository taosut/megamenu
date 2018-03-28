<?php
/**
 * @var Mage_Core_Model_Resource_Setup $this
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_item'), 'unit_price', array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'nullable' => true,
        'after' => null,
        'comment' => 'Unit Price'
    ));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_item'), 'total_returned', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'after' => null,
        'comment' => 'Total Returned'
    ));
$installer->endSetup();