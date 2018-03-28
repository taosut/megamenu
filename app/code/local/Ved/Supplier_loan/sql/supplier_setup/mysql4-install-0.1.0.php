<?php
$installer = $this;
$installer->startSetup();
$prefix = Mage::getConfig()->getTablePrefix();
$sql = <<<SQLTEXT
CREATE TABLE `supplier` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `supplier_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `supplier_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `supplier_address` text COLLATE utf8_unicode_ci NOT NULL,
  `supplier_contact` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `supplier_province` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Tỉnh/TP',
  `supplier_district` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Quận/Huyện',
  `note` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Created At',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Updated At',
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQLTEXT;

$installer->run(printf($sql, Mage::getConfig()->getTablePrefix()));

$installer->endSetup();
	 