<?php
/**
 * SQL Create Table teko_amp_message_queue
 * Drop table if exists
 */
$installer = $this;

$installer->startSetup();

$installer->run("


CREATE TABLE IF NOT EXISTS sales_order_utm_source (
    `id`  int(11) NOT NULL AUTO_INCREMENT,
    `order_id`  int(11) NOT NULL,
    `referer`  VARCHAR(255) NULL DEFAULT NULL,
    `source`  VARCHAR(255) NULL DEFAULT NULL,
    `medium`  VARCHAR(255) NULL DEFAULT NULL,
    `campaign`  VARCHAR(255) NULL DEFAULT NULL,
    `created_at`  DATETIME NULL DEFAULT NULL,
PRIMARY KEY (`id`), 
INDEX order_utm_source_idx (id) USING BTREE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$installer->endSetup();
