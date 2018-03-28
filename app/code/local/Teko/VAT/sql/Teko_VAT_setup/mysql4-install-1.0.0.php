<?php
/**
 * @var Mage_Core_Model_Resource_Setup $this
 */
$installer = $this;

$installer->startSetup();
try {
    $installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'is_vat', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'after' => null,
        'length' => 2,
        'default' => 0,
        'comment' => 'Description'
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'vat_name', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Description'
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'vat_address', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Description'
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'vat_id', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Description'
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'vat_address_to', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Description'
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'is_vat', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'after' => null,
        'length' => 2,
        'default' => 0,
        'comment' => 'Description'
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'vat_name', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Description'
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'vat_address', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Description'
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'vat_id', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Description'
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'vat_address_to', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'after' => null,
        'comment' => 'Description'
    ));

} catch (Exception $e) {
    echo "<pre>";
    print_r($e);
    die;
}

$installer->endSetup();
