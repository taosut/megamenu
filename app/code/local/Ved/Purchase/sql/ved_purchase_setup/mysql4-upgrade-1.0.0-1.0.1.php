<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 10/13/2017
 * Time: 10:56 AM
 */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `sales_flat_purchase_request_item`
ADD COLUMN `assignee` int(11) NULL DEFAULT NULL;
");

$installer->endSetup();
