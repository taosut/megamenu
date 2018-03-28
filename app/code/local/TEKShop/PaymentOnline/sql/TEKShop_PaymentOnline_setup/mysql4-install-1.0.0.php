<?php
/**
 * SQL Create Table teko_amp_message_queue
 * Drop table if exists
 */
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS tekshop_payment_online_log (
    `id`  int(10) NOT NULL AUTO_INCREMENT,
    `content`  text NULL ,
    `note`  text NULL ,
    `ip`  VARCHAR (20) DEFAULT 1 ,
    `type`  int(10) DEFAULT 1 ,
    `created_at`  DATETIME NULL DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Log payment online' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS tekshop_payment_order_transaction (
    `id`  int(10) NOT NULL AUTO_INCREMENT,
    `order_id`  int(10) NOT NULL ,
    `quote_id`  int(10) NOT NULL ,
    `amount`  DECIMAL(20,4) NOT NULL ,
    `pay_date`  VARCHAR(200) NOT NULL ,
    `transaction_no`  int(10) NOT NULL,
    `bank_code`  VARCHAR (200) NOT NULL,
    `bank_transaction_no`  VARCHAR(10) NOT NULL,
    `created_at`  DATETIME NULL DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Order Payment transaction online' AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS tekshop_payment_online_cart (
    `id`  int(10) NOT NULL AUTO_INCREMENT,
    `quote_id`  int(10) NOT NULL ,
    `amount`  DECIMAL(20,4) NOT NULL ,
    `pay_date`  VARCHAR(200) NOT NULL ,
    `transaction_no`  int(10) NOT NULL,
    `bank_code`  VARCHAR (200) NOT NULL,
    `bank_transaction_no`  VARCHAR(10) NOT NULL,
    `created_at`  DATETIME NULL DEFAULT NULL,
    `ip` VARCHAR (100) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Order Payment transaction online' AUTO_INCREMENT=1 ;
");

$installer->endSetup();
