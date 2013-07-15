<?php
$installer = $this;
$installer->startSetup();
$conn = $installer->getConnection();
$conn->addColumn($this->getTable('cms_page'), 'include_in_nav_label', 'varchar(50) not null');
$conn->addColumn($this->getTable('cms_page'), 'include_in_nav_parent', 'tinyint(4) not null');
$conn->addColumn($this->getTable('cms_page'), 'include_in_nav', 'tinyint(1) unsigned not null');
$conn->addColumn($this->getTable('cms_page'), 'include_in_nav_position', 'tinyint(3) unsigned not null');

$this->addAttribute('catalog_category', 'nav_position', array(
    'group'         => 'General Information',
    'input'         => 'text',
    'type'          => 'int',
    'label'         => 'Nav Menu Position',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();