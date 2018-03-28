<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE sales_flat_order
ADD COLUMN deposit_amount  DECIMAL (16,4) NOT NULL DEFAULT 0;
");

$installer->endSetup();
