<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS log_api;
CREATE TABLE IF NOT EXISTS log_api (
    `id`  int(4) NOT NULL AUTO_INCREMENT,
    `controller`  varchar(255) NULL ,
    `action`  varchar(255) NULL ,
    `method`  varchar(255) NULL ,
    `response`  text NULL ,
    `request_param`  text NULL ,
    `request_body`  text NULL ,
    `status`  int(4) NULL DEFAULT 0 ,
    `ip`  varchar(255) NULL ,
    `created_at`  DATETIME NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Log API' AUTO_INCREMENT=1 ;
");

$installer->endSetup();
