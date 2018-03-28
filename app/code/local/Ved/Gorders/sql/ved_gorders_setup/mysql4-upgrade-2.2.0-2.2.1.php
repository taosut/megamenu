<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 11/6/2017
 * Time: 11:22 AM
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('ved_gorders/purchaserequestitem'), 'note_purchase', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Note when sale purchased for purchase request to supplier'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('ved_gorders/purchaserequestitem'), 'note_check', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Note when supplier confirmed for purchase request to supplier'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('ved_gorders/purchaserequestitem'), 'pre_status', array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => true,
        'after' => null,
        'default' => 0,
        'comment' => 'Pre status of purchase request: 0-new, 1-request done, 2-request fail'
    ));
$installer->run("
ALTER TABLE `sales_flat_purchase_request_item` 
ADD COLUMN `receive_date` DATETIME NULL DEFAULT NULL;
");
$installer->endSetup();