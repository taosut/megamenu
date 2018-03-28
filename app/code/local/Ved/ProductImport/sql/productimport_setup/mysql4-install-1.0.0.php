<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE IF NOT EXISTS `import_log` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`filename` VARCHAR(500) NOT NULL,
	`template` TEXT NOT NULL,
	`size` INT(11) NOT NULL DEFAULT '0',
	`imported` INT(11) NOT NULL DEFAULT '0',
	`image_error` INT(11) NOT NULL DEFAULT '0',
	`failed` INT(11) NOT NULL DEFAULT '0',
	`imported_user` INT(11) NOT NULL,
	`imported_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 