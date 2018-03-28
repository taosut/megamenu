<?php
$installer = $this;
$installer->startSetup();
$sql = <<<SQLTEXT
CREATE TABLE IF NOT EXISTS `asia_response_api_update_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `status` tinyint(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sku` (`sku`) USING BTREE
);
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 