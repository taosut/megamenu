<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('ved_instalment/instalment'), 'allow_confirm', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => false,
        'length' => 1,
        'after' => null,
        'comment' => 'Allow_Confirm'
    ));

$installer->getConnection()
    ->addColumn($installer->getTable('ved_instalment/order_instalment'), 'prepaid_amount', array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'nullable' => false,
        'after' => null,
        'comment' => 'prepaid amount'
    ));

$installer->endSetup();