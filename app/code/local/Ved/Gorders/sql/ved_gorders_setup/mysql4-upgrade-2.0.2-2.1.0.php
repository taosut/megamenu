<?php

$installer = $this;

$installer->startSetup();

$installer->run("

  ALTER TABLE `sales_flat_order` 
  ADD COLUMN `estimate_delivery` DATETIME NULL DEFAULT NULL AFTER `deposit_amount`;
  
");

$installer->endSetup();
