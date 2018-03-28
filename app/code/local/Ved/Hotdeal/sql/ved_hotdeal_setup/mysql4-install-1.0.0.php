<?php
$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `hot_deal`;
CREATE TABLE `hot_deal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `date_from` datetime DEFAULT NULL,
  `date_to` datetime DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `status` smallint(6) DEFAULT NULL,
  `full_size` varchar(255) DEFAULT NULL,
  `small_size` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for hot_deal_categories
-- ----------------------------
DROP TABLE IF EXISTS `hot_deal_categories`;
CREATE TABLE `hot_deal_categories` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(255) DEFAULT NULL,
`position` tinyint(4) DEFAULT '0',
`create_at` datetime DEFAULT NULL,
`create_by` int(11) DEFAULT NULL,
`is_active` tinyint(4) DEFAULT '1',
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for hot_deal_category_store
-- ----------------------------
DROP TABLE IF EXISTS `hot_deal_category_store`;
CREATE TABLE `hot_deal_category_store` (
  `category_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`),
  KEY `FK_FAQ_CATEGORY_STORE_STORE` (`store_id`),
  CONSTRAINT `hot_deal_category_store_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `faq_category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hot_deal_category_store_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='FAQ Categories to Stores';

");

$installer->endSetup();
