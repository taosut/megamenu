<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('ved_instalment/alepay_logs'))
    ->addColumn('id', Varien_Db_Ddl_Table::
    TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'id')
    ->addColumn('response',Varien_Db_Ddl_Table::TYPE_TEXT,
        null,array(
            'nullable' => false
        )
    ,'Response')
    ->addColumn('return_status',Varien_Db_Ddl_Table::TYPE_TEXT,
        null,array(
            'nullable' => false
        )
    ,'Return Status')
    ->addColumn('ip',Varien_Db_Ddl_Table::TYPE_TEXT,
        null,array(
            'nullable' => false
        )
        ,'IP')
    ->addColumn('message',Varien_Db_Ddl_Table::TYPE_TEXT,
        null,array(
            'nullable' => false
        ),"Message")
    ->addColumn('created_at', Varien_Db_Ddl_Table::
    TYPE_TIMESTAMP, null, array(
        'nullable' => true,
    ), 'Created at');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('ved_instalment/instalment'))
    ->addColumn('instalment_id', Varien_Db_Ddl_Table::
    TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'instalment id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::
    TYPE_TIMESTAMP, null, array(
        'nullable' => true,
    ), 'Created at')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT,
        255, array(
            'nullable' => true,
        ), 'Name')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::
    TYPE_TIMESTAMP, null, array(
        'nullable' => false,
    ), 'Updated at')
    ->setComment('Helloworld subscriptions');

$installer->getConnection()->createTable($table);
$table = $installer->getConnection()
    ->newTable($installer->getTable('ved_instalment/order_instalment'))
    ->addColumn('id', Varien_Db_Ddl_Table::
    TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'id')
    ->addColumn('instalment_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('transaction_id',Varien_Db_Ddl_Table::TYPE_VARCHAR,
        30,array(
            'nullable' => false,
        ))
    ->addColumn('provider', Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255, array(
            'nullable' => false,
        ), 'Provider')
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_DECIMAL,
        '12,4', array(
            'nullable' => false,
        ), 'Amount')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER,
        null, array(
            'nullable' => false,
            'default' => 0
        ), 'Status')
    ->addColumn('fee', Varien_Db_Ddl_Table::TYPE_DECIMAL,
        '12,4', array(
            'nullable' => true,
        ), 'Fee')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT,
        null, array(
            'nullable' => true,
        ), 'Description')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
    ), 'Created at')
    ->addColumn('updated_by', Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255, array(
            'nullable' => true,
        ), 'Updated by');
$installer->getConnection()->createTable($table);
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'is_instalment', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => false,
        'after' => null,
        'default' => 0,
        'comment' => 'Is Instalment ?'
    ));
$installer->endSetup();
