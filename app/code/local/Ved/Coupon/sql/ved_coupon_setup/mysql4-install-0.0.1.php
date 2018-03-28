<?php

$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
    ->newTable($installer->getTable('ved_coupon/coupon_request'))
    ->addColumn('id', Varien_Db_Ddl_Table::
    TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'id')
    ->addColumn('sale_name',Varien_Db_Ddl_Table::TYPE_TEXT,
        null,array(
            'nullable' => false
        )
        ,'Saleman Name')
    ->addColumn('request_order',Varien_Db_Ddl_Table::TYPE_TEXT,
        null,array(
            'nullable' => false
        )
        ,'Saleman Name')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER,
        null, array(
            'nullable' => false,
            'default' => 0
        ), 'Status')
    ->addColumn('discount_amount',Varien_Db_Ddl_Table::TYPE_DECIMAL,
        '12,4',array(
            'nullable' => false
        )
        ,'Discount Amount')
    ->addColumn('coupon_code',Varien_Db_Ddl_Table::TYPE_TEXT,
        null,array(
            'nullable' => false,
            'unique'   => true
        )
        ,'Saleman Name')
    ->addColumn('date_request', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
    ), 'date_request')
    ->addColumn('date_approve', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
    ), 'date_approve');

$installer->getConnection()->createTable($table);
$installer->endSetup();