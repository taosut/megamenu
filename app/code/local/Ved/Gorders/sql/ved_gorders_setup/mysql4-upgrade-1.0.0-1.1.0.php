<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `sales_order_item_stock`
ADD COLUMN `standard_product_id`  varchar(255) NULL DEFAULT NULL;

ALTER TABLE `sales_flat_purchase_request_item`
ADD COLUMN `standard_product_id`  varchar(255) NULL DEFAULT NULL;
");

$installer->endSetup();
