<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS teko_api_order_inactive;
CREATE TABLE IF NOT EXISTS teko_api_order_inactive (
    `id`  int(4) NOT NULL AUTO_INCREMENT,
    `entity_id`  varchar(255) NULL ,
    `created_at`  DATETIME NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Inactive Order' AUTO_INCREMENT=1 ;
");

$installer->endSetup();
