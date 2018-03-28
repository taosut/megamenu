<?php
/**
 * SQL Create Table teko_amp_message_queue
 * Drop table if exists
 */
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS teko_amp_message_queue;
CREATE TABLE IF NOT EXISTS teko_amp_message_queue (
    `id`  int(4) NOT NULL AUTO_INCREMENT,
    `queue`  varchar(255) NULL ,
    `routing_key`  varchar(255) NULL ,
    `message`  text NULL ,
    `count`  tinyint(2) NULL DEFAULT 0 ,
    `created_at`  int(11) NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Message Queue' AUTO_INCREMENT=1 ;
");

$installer->endSetup();
