<?php

$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn(
    $this->getTable('stock_request'),//table name

    'product_name',      //column name
    'varchar(255) NOT NULL'  //datatype definition
);

$installer->getConnection()->addColumn(
    $this->getTable('stock_request'),//table name

    'status',      //column name
    'int(11) unsigned NOT NULL'  //datatype definition
);

$installer->endSetup();