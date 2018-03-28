<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS teko_api_transaction_order;
CREATE TABLE IF NOT EXISTS teko_api_transaction_order (
    `id`  int(4) NOT NULL AUTO_INCREMENT,
    `transaction_id`  varchar(255) NULL ,
    `order_id`  varchar(255) NULL ,
    `source`  varchar(20) NULL ,
    `status`  int(4) NULL DEFAULT 0 ,
    `created_at`  DATETIME NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Transaction Order' AUTO_INCREMENT=1 ;
");

$installer->endSetup();
