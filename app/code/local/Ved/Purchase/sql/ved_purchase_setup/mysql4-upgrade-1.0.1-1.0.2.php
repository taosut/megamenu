<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 10/23/2017
 * Time: 4:20 PM
 */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `sales_flat_purchase`
ADD COLUMN `updated_by` int(11) NULL DEFAULT NULL;
");

$installer->run("
ALTER TABLE `sales_flat_purchase`
ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL;
");

$installer->endSetup();