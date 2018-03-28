<?php

$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('agent_achievement_type')}`
MODIFY `created_at` TIMESTAMP NULL;

ALTER TABLE `{$this->getTable('agent_achievement_type')}`
MODIFY `updated_at` TIMESTAMP NULL;

ALTER TABLE `{$this->getTable('agent_achievement_type')}`
ADD `is_active` int(11) NOT NULL default 1;

ALTER TABLE `{$this->getTable('agent_channel')}`
MODIFY `created_at` TIMESTAMP NULL;

ALTER TABLE `{$this->getTable('agent_channel')}`
MODIFY `updated_at` TIMESTAMP NULL;

ALTER TABLE `{$this->getTable('agent_channel')}`
ADD `is_active` int(11) NOT NULL default 1;

");

$installer->endSetup();
