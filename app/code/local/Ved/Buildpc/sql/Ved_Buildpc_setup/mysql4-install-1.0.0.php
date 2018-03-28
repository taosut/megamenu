<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/21/2017
 * Time: 1:57 PM
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('Ved_Buildpc/buildpc'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Customer PC ID')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'scale'     => 4,
        'precision' => 16,
        'nullable'  => false,
        'default'   => 0,
    ), 'Price of customer PC')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Customer ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Name of customer PC')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Created date')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Last updated date')
    ->addForeignKey(
        $installer->getFkName('Ved_Buildpc/buildpc', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id',
        $installer->getTable('customer/entity'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('Ved_Buildpc/buildpc', 'customer_id'), 'customer_id')
    ->setComment('Customer PCs main Table');
$installer->getConnection()->dropTable($installer->getTable('Ved_Buildpc/buildpc'));
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('Ved_Buildpc/detail'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Customer PC item ID')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Customer PC ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Product ID')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Category ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Store ID')
    ->addColumn('quantity', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
    ), 'Quantity item')
    ->addForeignKey(
        $installer->getFkName('Ved_Buildpc/detail', 'parent_id', 'Ved_Buildpc/buildpc', 'entity_id'),
        'parent_id',
        $installer->getTable('Ved_Buildpc/buildpc'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('Ved_Buildpc/detail', 'category_id', 'catalog/category', 'entity_id'),
        'parent_id',
        $installer->getTable('Ved_Buildpc/buildpc'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('Ved_Buildpc/detail', 'product_id', 'catalog/product', 'entity_id'),
        'product_id',
        $installer->getTable('catalog/product'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('Ved_Buildpc/detail', 'store_id', 'core/store', 'store_id'),
        'store_id',
        $installer->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('Ved_Buildpc/detail', 'parent_id'), 'parent_id')
    ->addIndex($installer->getIdxName('Ved_Buildpc/detail', 'product_id'), 'product_id')
    ->addIndex($installer->getIdxName('Ved_Buildpc/detail', 'category_id'), 'category_id')
    ->addIndex($installer->getIdxName('Ved_Buildpc/detail', 'store_id'), 'store_id')
    ->setComment('Customer PC items table');
$installer->getConnection()->dropTable($installer->getTable('Ved_Buildpc/detail'));
$installer->getConnection()->createTable($table);

$installer->endSetup();