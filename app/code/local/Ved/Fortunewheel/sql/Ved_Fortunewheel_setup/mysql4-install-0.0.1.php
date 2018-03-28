<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('Ved_Fortunewheel/wheelDetail'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Customer ID')
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Sales rule ID')
    ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
    ), 'Coupon ID')
    ->addColumn('count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Count')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
    ), '0: Send, 1: Checked')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Created date')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Last updated date')
    ->setComment('Table detail fortune wheel');
$installer->getConnection()->dropTable($installer->getTable('Ved_Fortunewheel/wheelDetail'));
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('Ved_Fortunewheel/wheelInfoSalesrule'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        'primary'   => true,
    ), 'Sales rule ID')
    ->addColumn('percent', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        'scale'     => 4,
        'precision' => 8,
        'nullable'  => false,
        'default'   => 0,
    ), 'Percent of rule')
    ->addColumn('count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Count')
    ->setComment('Table information about rule');
$installer->getConnection()->dropTable($installer->getTable('Ved_Fortunewheel/wheelInfoSalesrule'));
$installer->getConnection()->createTable($table);

$installer->getConnection()
    ->addColumn($installer->getTable('salesrule/coupon'), 'is_sent', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'after' => null,
        'comment' => 'status if code is sent'
    ));

$installer->endSetup();