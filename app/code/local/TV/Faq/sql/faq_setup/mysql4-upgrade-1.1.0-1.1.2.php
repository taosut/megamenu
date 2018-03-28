<?php
/**
 * Created by PhpStorm.
 * User: Dung.BV
 * Date: 3/21/2017
 * Time: 1:52 PM
 */

$installer = $this;

$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('tv_faq/category')}
  ADD COLUMN category_image varchar(255) NULL AFTER `is_active`;
  
  
");

$installer->endSetup();
