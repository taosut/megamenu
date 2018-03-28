<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('ved_instalment/order_instalment'), 'prepaid_method', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length' => 255,
        'after' => null,
        'comment' => 'prepaid_method'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('ved_instalment/order_instalment'), 'customer_name', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length' => 255,
        'after' => null,
        'comment' => 'customer_name'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('ved_instalment/order_instalment'), 'customer_id', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length' => 255,
        'after' => null,
        'comment' => 'customer_name'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('ved_instalment/order_instalment'), 'customer_telephone', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length' => 255,
        'after' => null,
        'comment' => 'customer_telephone'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('ved_instalment/order_instalment'), 'term', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => false,
        'default' => 0,
        'length' => 2,
        'after' => null,
        'comment' => 'term'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('ved_instalment/order_instalment'), 'delay_fee', array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 4,
        'scale' => 2,
        'nullable' => false,
        'after' => null,
        'comment' => 'delay_fee'
    ));
$installer->endSetup();