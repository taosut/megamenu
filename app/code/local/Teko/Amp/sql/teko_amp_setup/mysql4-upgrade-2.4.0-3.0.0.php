<?php
/**
 * @var Mage_Core_Model_Resource_Setup $this
 */
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_product', 'instock', [
    'label' => 'Trạng thái hàng',
    'type' => 'int',
    'input' => 'select',
    'backend' => '',
    'frontend' => '',
    'source' => 'teko_amp/instock_option',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => true,
    'user_defined' => true,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'visible_in_advanced_search' => false,
    'unique' => false,
]);
$sql = <<<SQLTEXT
INSERT INTO eav_entity_attribute (
	entity_type_id,
	attribute_set_id,
	attribute_group_id,
	attribute_id,
	sort_order
) SELECT
	eav_entity_attribute.entity_type_id,
	eav_entity_attribute.attribute_set_id,
	eav_entity_attribute.attribute_group_id,
	(
		SELECT
			attribute_id
		FROM
			eav_attribute
		WHERE
			attribute_code = 'instock'
	),
	11
FROM
	eav_entity_attribute
WHERE
	attribute_id = (
		SELECT
			attribute_id
		FROM
			eav_attribute
		WHERE
			attribute_code = 'warehouse_sku'
	);
SQLTEXT;

$installer->run($sql);


$installer->endSetup();