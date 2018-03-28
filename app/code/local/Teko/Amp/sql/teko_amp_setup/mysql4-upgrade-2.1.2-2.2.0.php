<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_item'), 'qty_arrive', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'after' => null,
        'comment' => 'Quantity Arrive'
    ));

$installer->endSetup();
