<?php
/**
 * SQL Create Table teko_amp_message_queue
 * Drop table if exists
 */
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS tekshop_payment_online_cart (
    `id`  int(10) NOT NULL AUTO_INCREMENT,
    `quote_id`  int(10) NOT NULL ,
    `amount`  DECIMAL(20,4) NOT NULL ,
    `pay_date`  VARCHAR(200) NOT NULL ,
    `transaction_no`  int(10) NOT NULL,
    `status`  int(10) NOT NULL,
    `created_at`  DATETIME NULL DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Save Order When VNPay Call IPN' AUTO_INCREMENT=1 ;
");

$installer->endSetup();
