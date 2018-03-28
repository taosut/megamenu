<?php
/**
 * @var Mage_Core_Model_Resource_Setup $this
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('teko_amp/queue'), 'properties', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Properties Of Queue'
    ));
$installer->endSetup();