<?php
/**
 * Created by PhpStorm.
 * User: Phuc Loi
 * Date: 7/12/2017
 * Time: 2:51 PM
 */

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE sales_flat_order_status_history
ADD COLUMN user_id INT(11) DEFAULT NULL;
");

$installer->endSetup();
