<?php
/**
 * SQL Create Table teko_amp_message_queue
 * Drop table if exists
 */
$installer = $this;

$installer->startSetup();

$sql = <<<SQLTEXT
ALTER TABLE `tekshop_payment_order_transaction`
MODIFY COLUMN `pay_date`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `amount`,
MODIFY COLUMN `bank_code`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `transaction_no`,
MODIFY COLUMN `bank_transaction_no`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `bank_code`;
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
