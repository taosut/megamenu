<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('ved_gorders/order_source'))
    ->addColumn('id', Varien_Db_Ddl_Table::
    TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::
    TYPE_TIMESTAMP, null, array(
        'nullable' => true,
    ), 'Created at')
    ->addColumn('original_increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR,
        50, array(
            'nullable' => true,
        ), 'Original Increment Id Order')
    ->addColumn('channel', Varien_Db_Ddl_Table::
    TYPE_VARCHAR, 25, array(
        'nullable' => true,
    ), 'Updated at')
    ->addColumn('terminal_id', Varien_Db_Ddl_Table::
    TYPE_VARCHAR, 255, array(
        'nullable' => true,
    ), null)
    ->addColumn('agent_id', Varien_Db_Ddl_Table::
    TYPE_INTEGER, 11, array(
        'nullable' => true,
    ), null);

$installer->getConnection()->createTable($table);

$installer->endSetup();
