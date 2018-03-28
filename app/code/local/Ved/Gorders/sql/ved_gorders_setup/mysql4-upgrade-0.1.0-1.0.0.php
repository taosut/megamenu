<?php
/**
 * FAQ accordion for Magento

 */

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `sales_flat_order`
ADD COLUMN `is_send_queue`  tinyint NULL DEFAULT 0;

");

$installer->endSetup();
