<?php
$installer = $this;

$installer->startSetup();

$this->addAttribute('customer_address', 'customer_email', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Customer Email',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'visible_on_front' => 1
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'customer_email')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();

$installer->endSetup();