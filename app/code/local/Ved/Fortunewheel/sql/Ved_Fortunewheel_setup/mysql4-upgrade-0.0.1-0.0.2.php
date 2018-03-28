<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable("customer_entity"), 'process_lock', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'lock process fortune wheel customer'
    ));

$installer->endSetup();