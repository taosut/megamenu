<?php

$installer = $this;

$installer->startSetup();

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('stock_request')}`;
CREATE TABLE `{$this->getTable('stock_request')}` (
  `stock_request_id` int(11) unsigned NOT NULL auto_increment,
  `user_name` varchar(255) NOT NULL default '',
  `phone_number` varchar(255) NOT NULL default '',
  `request_content` text NOT NULL default '',
  `product_id` int(11) unsigned NOT NULL default '0',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stock_request_id`),
  INDEX (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$installer->endSetup();