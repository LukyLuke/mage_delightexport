<?php
/**
 * DelightSerial Customisation by delight software gmbh for Magento
 *
 * DISCLAIMER
 *
 * Do not edit or add code to this file if you wish to upgrade this Module to newer
 * versions in the future.
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @copyright  Copyright (c) 2001-2011 delight software gmbh (http://www.delightsoftware.com/)
 */
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS `{$installer->getTable('delightexport/files')}`;
    DROP TABLE IF EXISTS `{$installer->getTable('delightexport/config')}`;
");

$installer->run("
CREATE TABLE `{$installer->getTable('delightexport/files')}`(
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `file_name` varchar(250) NOT NULL default '',
  `file_description` TEXT NOT NULL default '',
  `store_ids` varchar(200) NOT NULL default '0',
  `file_type` int(10) unsigned NOT NULL default 0,
  PRIMARY KEY `DELIGHTEXPORT_FILE_PK` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
CREATE TABLE `{$installer->getTable('delightexport/config')}`(
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `file_id` int(10) unsigned NOT NULL default 0,
  `attribute` varchar(100) NOT NULL default '',
  `export_field` varchar(100) NOT NULL default '',
  `format` int(11) NOT NULL default 0,
  `pre_text` varchar(250) NOT NULL default '',
  `post_text` varchar(250) NOT NULL default '',
  `config` TEXT NOT NULL default '',
  KEY `DELIGHTSERIAL_CONF_FILE` (`file_id`),
  PRIMARY KEY `DELIGHTEXPORT_CONF_PK` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$conn->addConstraint(
    'FK_DELIGHTSERIAL_CONF_FILE', $installer->getTable('delightexport/config'), 'file_id', $installer->getTable('delightexport/files'), 'entity_id'
);

$installer->endSetup();
