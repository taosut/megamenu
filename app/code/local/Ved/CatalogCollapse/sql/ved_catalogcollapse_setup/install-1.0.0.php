<?php

$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('catalog/eav_attribute'),
    'is_auto_collapse',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        'comment' => 'Is Auto Collapse'
    )
);

$installer->endSetup();
