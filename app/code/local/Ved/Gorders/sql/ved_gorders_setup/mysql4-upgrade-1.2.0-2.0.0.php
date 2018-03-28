<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `sales_flat_order`
ADD COLUMN `created_by_id`  int(11) NOT NULL DEFAULT 0 AFTER `is_send_queue`,
ADD COLUMN `created_by_name`  varchar(255) NOT NULL AFTER `created_by_id`;
");

$installer->endSetup();
