<?php

$installer = $this;

$installer->startSetup();

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('agent_channel')}`;
CREATE TABLE `{$this->getTable('agent_channel')}` (
  `channel_id` int(11) unsigned NOT NULL auto_increment,
  `channel_name` varchar(255) NOT NULL default '',
  `channel_type` int(11) NOT NULL,
  `is_deleted` int(11) NOT NULL default 0,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('agent_account')}`;
CREATE TABLE `{$this->getTable('agent_account')}` (
  `account_id` int(11) unsigned NOT NULL auto_increment,
  `account_name` varchar(255) NOT NULL default '',
  `channel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `is_deleted` int(11) NOT NULL default 0,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('agent_account_history')}`;
CREATE TABLE `{$this->getTable('agent_account_history')}` (
  `account_history_id` int(11) unsigned NOT NULL auto_increment,
  `account_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `comment` varchar(255) NOT NULL default '',
  `created_by` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`account_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('agent_achievement_type')}`;
CREATE TABLE `{$this->getTable('agent_achievement_type')}` (
  `achievement_type_id` int(11) unsigned NOT NULL auto_increment,
  `channel_id` int(11) NOT NULL,
  `achievement_type_name` varchar(255) NOT NULL default '',
  `point` int(11) NOT NULL,
  `is_deleted` int(11) NOT NULL default 0,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`achievement_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('agent_achievement')}`;
CREATE TABLE `{$this->getTable('agent_achievement')}` (
  `achievement_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `achievement_type_id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL default '',
  `note` text NOT NULL default '',
  `status` int(11) NOT NULL,
  `comment` varchar (255) NOT NULL default '',
  `is_deleted` int(11) NOT NULL default 0,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('agent_achievement_history')}`;
CREATE TABLE `{$this->getTable('agent_achievement_history')}` (
  `achievement_history_id` int(11) unsigned NOT NULL auto_increment,
  `achievement_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `comment` varchar (255) NOT NULL default '',
  `is_deleted` int(11) NOT NULL default 0,
  `created_by` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`achievement_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('agent_redemption')}`;
CREATE TABLE `{$this->getTable('agent_redemption')}` (
  `redemption_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `redemption_gift_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`redemption_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('agent_redemption_gift')}`;
CREATE TABLE `{$this->getTable('agent_redemption_gift')}` (
  `redemption_gift_id` int(11) unsigned NOT NULL auto_increment,
  `redemption_gift_name` varchar(255) NOT NULL default '',
  `rule_id` int(11) NOT NULL,
  `point` int(11) NOT NULL,
  `is_deleted` int(11) NOT NULL default 0,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`redemption_gift_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$installer->run("
 
DROP TABLE IF EXISTS `{$this->getTable('agent_point_history')}`;
CREATE TABLE `{$this->getTable('agent_point_history')}` (
  `point_history_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `point` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `detail` text NOT NULL default '',
  `accumulate_point` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`point_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

// Add new column is_redeem for table salesrule_coupon
$installer->getConnection()->addColumn(
    $this->getTable('salesrule_coupon'),//table name

    'is_redeem',      //column name
    'int(11) NOT NULL DEFAULT 0'  //datatype definition
);

$installer->endSetup();