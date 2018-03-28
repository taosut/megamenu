<?php
/**
 * @var Mage_Core_Model_Resource_Setup $this
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_item'), 'warehouse_sku', array(
        'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'nullable' => true,
        'length' => 255,
        'after' => null,
        'comment' => 'Sku Same Supplier'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_item'), 'standard_product_id', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'default' => 0,
        'length' => 11,
        'after' => null,
        'comment' => 'Standard Product Id Same Supplier'
    ));
$installer->endSetup();