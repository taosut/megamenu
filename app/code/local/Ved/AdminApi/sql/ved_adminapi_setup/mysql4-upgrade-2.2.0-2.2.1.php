<?php
$installer = $this;

$installer->startSetup();

$installer->run("
  ALTER TABLE `sales_flat_order` 
  ADD COLUMN `deposit_method` varchar(255) NULL DEFAULT NULL;
");

$installer->endSetup();
