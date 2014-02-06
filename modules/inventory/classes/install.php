<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright (c) 2008, 2009, 2010, 2011, 2012 PhreeSoft, LLC       |
// | http://www.PhreeSoft.com                                        |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/inventory/classes/install.php
//
class inventory_admin {
  function __construct() {
    $this->notes = array();
	$this->prerequisites = array( // modules required and rev level for this module to work properly
	  'contacts'   => '3.6',
	  'phreedom'   => '3.4',
	  'phreebooks' => '3.4',
	);
	// Load configuration constants for this module, must match entries in admin tabs
    $this->keys = array(
	  'INV_STOCK_DEFAULT_SALES'            => '4000',
	  'INV_STOCK_DEFAULT_INVENTORY'        => '1200',
	  'INV_STOCK_DEFAULT_COS'              => '5000',
	  'INV_STOCK_DEFAULT_COSTING'          => 'f',
	  'INV_MASTER_STOCK_DEFAULT_SALES'     => '4000',
	  'INV_MASTER_STOCK_DEFAULT_INVENTORY' => '1200',
	  'INV_MASTER_STOCK_DEFAULT_COS'       => '5000',
	  'INV_MASTER_STOCK_DEFAULT_COSTING'   => 'f',
	  'INV_ASSY_DEFAULT_SALES'             => '4000',
	  'INV_ASSY_DEFAULT_INVENTORY'         => '1200',
	  'INV_ASSY_DEFAULT_COS'               => '5000',
	  'INV_ASSY_DEFAULT_COSTING'           => 'f',
	  'INV_SERIALIZE_DEFAULT_SALES'        => '4000',
	  'INV_SERIALIZE_DEFAULT_INVENTORY'    => '1200',
	  'INV_SERIALIZE_DEFAULT_COS'          => '5000',
	  'INV_SERIALIZE_DEFAULT_COSTING'      => 'f',
	  'INV_NON_STOCK_DEFAULT_SALES'        => '4000',
	  'INV_NON_STOCK_DEFAULT_INVENTORY'    => '1200',
	  'INV_NON_STOCK_DEFAULT_COS'          => '5000',
	  'INV_SERVICE_DEFAULT_SALES'          => '4000',
	  'INV_SERVICE_DEFAULT_INVENTORY'      => '1200',
	  'INV_SERVICE_DEFAULT_COS'            => '5000',
	  'INV_LABOR_DEFAULT_SALES'            => '4000',
	  'INV_LABOR_DEFAULT_INVENTORY'        => '1200',
	  'INV_LABOR_DEFAULT_COS'              => '5000',
	  'INV_ACTIVITY_DEFAULT_SALES'         => '4000',
	  'INV_CHARGE_DEFAULT_SALES'           => '4000',
	  'INVENTORY_DEFAULT_TAX'              => '0',
	  'INVENTORY_DEFAULT_PURCH_TAX'        => '0',
	  'INVENTORY_AUTO_ADD'                 => '0',
	  'INVENTORY_AUTO_FILL'                => '0',
	  'ORD_ENABLE_LINE_ITEM_BAR_CODE'      => '0',
	  'ORD_BAR_CODE_LENGTH'                => '12',
	  'ENABLE_AUTO_ITEM_COST'              => '0',
	);
	// add new directories to store images and data
	$this->dirlist = array(
	  'inventory',
	  'inventory/images',
	);
	// Load tables
	$this->tables = array(
	  TABLE_INVENTORY => "CREATE TABLE " . TABLE_INVENTORY . " (
		  id int(11) NOT NULL auto_increment,
		  sku varchar(24) NOT NULL default '',
		  inactive enum('0','1') NOT NULL default '0',
		  inventory_type char(2) NOT NULL default 'si',
		  description_short varchar(32) NOT NULL default '',
		  description_purchase varchar(255) default NULL,
		  description_sales varchar(255) default NULL,
		  image_with_path varchar(255) default NULL,
		  account_sales_income varchar(15) default NULL,
		  account_inventory_wage varchar(15) default '',
		  account_cost_of_sales varchar(15) default NULL,
		  item_taxable int(11) NOT NULL default '0',
		  purch_taxable int(11) NOT NULL default '0',
		  item_cost float NOT NULL default '0',
		  cost_method enum('a','f','l') NOT NULL default 'f',
		  price_sheet varchar(32) default NULL,
		  price_sheet_v varchar(32) default NULL,
		  full_price float NOT NULL default '0',
		  item_weight float NOT NULL default '0',
		  quantity_on_hand float NOT NULL default '0',
		  quantity_on_order float NOT NULL default '0',
		  quantity_on_sales_order float NOT NULL default '0',
		  quantity_on_allocation float NOT NULL default '0',
		  minimum_stock_level float NOT NULL default '0',
		  reorder_quantity float NOT NULL default '0',
		  vendor_id int(11) NOT NULL default '0',
		  lead_time int(3) NOT NULL default '1',
		  upc_code varchar(13) NOT NULL DEFAULT '',
		  serialize enum('0','1') NOT NULL default '0',
		  creation_date datetime NOT NULL default '0000-00-00 00:00:00',
		  last_update datetime NOT NULL default '0000-00-00 00:00:00',
		  last_journal_date datetime NOT NULL default '0000-00-00 00:00:00',
		  PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_ASSY_LIST => "CREATE TABLE " . TABLE_INVENTORY_ASSY_LIST . " (
		  id int(11) NOT NULL auto_increment,
		  ref_id int(11) NOT NULL default '0',
		  sku varchar(24) NOT NULL default '',
		  description varchar(32) NOT NULL default '',
		  qty float NOT NULL default '0',
		  PRIMARY KEY (id),
		  KEY ref_id (ref_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_COGS_OWED => "CREATE TABLE " . TABLE_INVENTORY_COGS_OWED . " (
		  id int(11) NOT NULL auto_increment,
		  journal_main_id int(11) NOT NULL default '0',
		  store_id int(11) NOT NULL default '0',
		  sku varchar(24) NOT NULL default '',
		  qty float NOT NULL default '0',
		  post_date date NOT NULL default '0000-00-00',
		  PRIMARY KEY (id),
		  KEY sku (sku),
		  INDEX (store_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_COGS_USAGE => "CREATE TABLE " . TABLE_INVENTORY_COGS_USAGE . " (
		  id int(11) NOT NULL auto_increment,
		  journal_main_id int(11) NOT NULL default '0',
		  qty float NOT NULL default '0',
		  inventory_history_id int(11) NOT NULL default '0',
		  PRIMARY KEY (id),
		  INDEX (journal_main_id, inventory_history_id) 
		) ENGINE=innodb DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_HISTORY => "CREATE TABLE " . TABLE_INVENTORY_HISTORY . " (
		  id int(11) NOT NULL auto_increment,
		  ref_id int(11) NOT NULL default '0',
		  store_id int(11) NOT NULL default '0',
		  journal_id int(2) NOT NULL default '6',
		  sku varchar(24) NOT NULL default '',
		  qty float NOT NULL default '0',
		  serialize_number varchar(24) NOT NULL default '',
		  remaining float NOT NULL default '0',
		  unit_cost float NOT NULL default '0',
		  post_date datetime default NULL,
		  PRIMARY KEY (id),
		  KEY sku (sku),
		  KEY ref_id (ref_id),
		  KEY remaining (remaining),
		  INDEX (store_id),
		  INDEX (journal_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_MS_LIST => "CREATE TABLE " . TABLE_INVENTORY_MS_LIST . " (
		  id int(11) NOT NULL auto_increment,
		  sku varchar(24) NOT NULL default '',
		  attr_name_0 varchar(16) NULL,
		  attr_name_1 varchar(16) NULL,
		  attr_0 varchar(255) NULL,
		  attr_1 varchar(255) NULL, 
		  PRIMARY KEY (id)
		) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_INVENTORY_SPECIAL_PRICES => "CREATE TABLE " . TABLE_INVENTORY_SPECIAL_PRICES . " (
		  id int(11) NOT NULL auto_increment,
		  inventory_id int(11) NOT NULL default '0',
		  price_sheet_id int(11) NOT NULL default '0',
		  price_levels varchar(255) NOT NULL default '',
		  PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_PRICE_SHEETS => "CREATE TABLE " . TABLE_PRICE_SHEETS . " (
		  id int(11) NOT NULL auto_increment,
		  sheet_name varchar(32) NOT NULL default '',
		  type char(1) NOT NULL default 'c',
		  inactive enum('0','1') NOT NULL default '0',
		  revision float NOT NULL default '0',
		  effective_date date NOT NULL default '0000-00-00',
		  expiration_date date default NULL,
		  default_sheet enum('0','1') NOT NULL default '0',
		  default_levels varchar(255) NOT NULL default '',
		  PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
    );
  }

  function install($module) {
	$error = false;
	$this->notes[] = MODULE_INVENTORY_NOTES_1;
	require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
	xtra_field_sync_list('inventory', TABLE_INVENTORY);
    return $error;
  }

  function initialize($module) {
  }

  function update($module) {
    global $db, $messageStack;
	$error = false;
    if (MODULE_INVENTORY_STATUS < '3.1') {
	  $tab_map = array('0' => '0');
	  $result = $db->Execute("select * from " . DB_PREFIX . 'inventory_categories');
	  while (!$result->EOF) {
	    $updateDB = $db->Execute("insert into " . TABLE_EXTRA_TABS . " set 
		  module_id = 'inventory',
		  tab_name = '"    . $result->fields['category_name']        . "',
		  description = '" . $result->fields['category_description'] . "',
		  sort_order = '"  . $result->fields['sort_order']           . "'");
	    $tab_map[$result->fields['category_id']] = db_insert_id();
	    $result->MoveNext();
	  }
	  $result = $db->Execute("select * from " . DB_PREFIX . 'inventory_fields');
	  while (!$result->EOF) {
	    $updateDB = $db->Execute("insert into " . TABLE_EXTRA_FIELDS . " set 
		  module_id = 'inventory',
		  tab_id = '"      . $tab_map[$result->fields['category_id']] . "',
		  entry_type = '"  . $result->fields['entry_type']  . "',
		  field_name = '"  . $result->fields['field_name']  . "',
		  description = '" . $result->fields['description'] . "',
		  params = '"      . $result->fields['params']      . "'");
	    $result->MoveNext();
	  }
	  $db->Execute("DROP TABLE " . DB_PREFIX . "inventory_categories");
	  $db->Execute("DROP TABLE " . DB_PREFIX . "inventory_fields");
	  xtra_field_sync_list('inventory', TABLE_INVENTORY);
	}
    if (MODULE_INVENTORY_STATUS < '3.2') {
	  if (!db_field_exists(TABLE_PRICE_SHEETS, 'type')) $db->Execute("ALTER TABLE " . TABLE_PRICE_SHEETS . " ADD type char(1) NOT NULL default 'c' AFTER sheet_name");
	  if (!db_field_exists(TABLE_INVENTORY, 'price_sheet_v')) $db->Execute("ALTER TABLE " . TABLE_INVENTORY . " ADD price_sheet_v varchar(32) default NULL AFTER price_sheet");
	  xtra_field_sync_list('inventory', TABLE_INVENTORY);
	}
	if (!$error) {
	  write_configure('MODULE_' . strtoupper($module) . '_STATUS', constant('MODULE_' . strtoupper($module) . '_VERSION'));
   	  $messageStack->add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $module, constant('MODULE_' . strtoupper($module) . '_VERSION')), 'success');
	}
	return $error;
  }

  function remove($module) {
    global $db, $messageStack;
	$error = false;
	$db->Execute("delete from " . TABLE_EXTRA_FIELDS . " where module_id = 'inventory'");
	$db->Execute("delete from " . TABLE_EXTRA_TABS   . " where module_id = 'inventory'");
	return $error;
  }

  function load_reports($module) {
	$error = false;
	$id    = admin_add_report_heading(MENU_HEADING_INVENTORY, 'inv');
	if (admin_add_report_folder($id, TEXT_REPORTS, 'inv', 'fr')) $error = true;
	return $error;
  }

  function load_demo() {
    global $db;
	$error = false;
	// Data for table `inventory`
	$db->Execute("TRUNCATE TABLE " . TABLE_INVENTORY);
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (1, 'AMD-3600-CPU', '0', 'si', 'AMD 3600+ Athlon CPU', 'AMD 3600+ Athlon CPU', 'AMD 3600+ Athlon CPU', 'demo/athlon.jpg', '4000', '1200', '5000', '1', '0', 100, 'f', '', '', 150, 1, 0, 0, 0, 0, 0, 0, 3, 1, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (2, 'ASSY-BB', '0', 'lb', 'Labor - BB Computer Assy', 'Labor Cost - Assemble Bare Bones Computer', 'Labor - BB Computer Assy', '', '4000', '6000', '5000', '1', '0', 25, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (3, 'BOX-TW-322', '0', 'ns', 'TW-322 Shipping Box', 'TW-322 Shipping Box - 12 x 12 x 12', 'TW-322 Shipping Box', '', '4000', '6800', '5000', '1', '0', 1.35, 'f', '', '', 0, 0, 0, 0, 0, 0, 15, 25, 0, 1, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (4, 'BOX-TW-553', '0', 'ns', 'TW-533 Shipping Box', 'TW-533 Shipping Box - 24 x 12 x 12', 'TW-533 Shipping Box', '', '4000', '6800', '5000', '1', '0', 1.75, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (5, 'CASE-ALIEN', '0', 'si', 'Alien Case - Red', 'Closed Cases - Red Full Tower ATX case w/o power supply', 'Alien Case - Red', 'demo/red_alien.jpg', '4000', '1200', '5000', '1', '0', 47, 'f', '', '', 98.26, 11, 0, 0, 0, 0, 2, 1, 13, 5, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (6, 'DESC-WARR', '0', 'ai', 'Warranty Template', 'Warranty Template', 'Warranty Template', '', '1000', '1000', '1000', '1', '0', 0, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (7, 'DVD-RW', '0', 'si', 'DVD RW with Lightscribe', 'DVD RW with Lightscribe - 8x', 'DVD RW with Lightscribe', 'demo/lightscribe.jpg', '4000', '1200', '5000', '1', '0', 23.6, 'f', '', '', 45, 2, 0, 0, 0, 0, 3, 1, 15, 14, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (8, 'HD-150GB', '0', 'si', '150GB SATA Hard Drive', '150GB SATA Hard Drive - 7200 RPM', '150GB SATA Hard Drive', 'demo/150gb_sata.jpg', '4000', '1200', '5000', '1', '0', 27, 'f', '', '', 56, 2, 0, 0, 0, 0, 10, 15, 15, 30, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (9, 'KB-128-ERGO', '0', 'si', 'KeysRus ergonomic keyboard', 'KeysRus ergonomic keyboard - Lighted for Gaming', 'KeysRus ergonomic keyboard', 'demo/ergo_key.jpg', '4000', '1200', '5000', '0', '1', 23.51, 'f', '', '', 56.88, 0, 0, 0, 0, 0, 5, 10, 11, 1, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (10, 'LCD-21-WS', '0', 'si', 'LCDisplays 21\" LCD Monitor', 'LCDisplays 21\" LCD Monitor - wide screen w/anti-glare finish, Black', 'LCDisplays 21\" LCD Monitor', 'demo/monitor.jpg', '4000', '1200', '5000', '1', '0', 145.01, 'f', '', '', 189.99, 0, 0, 0, 0, 0, 2, 1, 5, 3, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (11, 'MB-ATI-K8', '0', 'si', 'ATI K8 Motherboard', 'ATI-K8-TW AMD socket 939 Motherboard for Athlon Processors', 'ATI K8 Motherboard', 'demo/mobo.jpg', '4000', '1200', '5000', '1', '0', 125, 'f', '', '', 155.25, 1, 0, 0, 0, 0, 5, 10, 3, 3, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (12, 'MB-ATI-K8N', '0', 'si', 'ATI K8 Motherboard w/network', 'ATI-K8-TW AMD socket 939 Motherboard for Athlon Processors with network ports', 'ATI K8 Motherboard w/network', 'demo/mobo.jpg', '4000', '1200', '5000', '1', '0', 135, 'f', '', '', 176.94, 1.2, 0, 0, 0, 0, 3, 10, 3, 3, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (13, 'Mouse-S', '0', 'si', 'Serial Mouse - 300 DPI', 'Serial Mouse - 300 DPI', 'Serial Mouse - 300 DPI', 'demo/serial_mouse.jpg', '4000', '1200', '5000', '1', '0', 4.85, 'f', '', '', 13.99, 0.6, 0, 0, 0, 0, 15, 25, 11, 1, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (14, 'PC-2GB-120GB-21', '0', 'as', 'Computer 2GB-120GB-21', 'Fully assembled computer AMD/ATI 2048GB Ram/1282 GB HD/Red Case/ Monitor/ Keyboard/ Mouse', 'Computer 2GB-120GB-21', 'demo/complete_computer.jpg', '4000', '1200', '5000', '1', '0', 0, 'f', '', '', 750, 21.3, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (15, 'PS-450W', '0', 'si', '450 Watt Silent Power Supply', '850 Watt Silent Power Supply - for use with Intel or AMD processors', '450 Watt Silent Power Supply', 'demo/power_supply.jpg', '4000', '1200', '5000', '1', '0', 86.26, 'f', '', '', 124.5, 4.7, 0, 0, 0, 0, 10, 6, 14, 5, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (16, 'RAM-2GB-0.2', '0', 'si', '2GB SDRAM', '2 GB PC3200 Memory Modules - for Athlon processors', '2GB SDRAM', 'demo/2gbram.jpg', '4000', '1200', '5000', '1', '0', 56.25, 'f', '', '', 89.65, 0, 0, 0, 0, 0, 8, 10, 3, 2, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (17, 'VID-NV-512MB', '0', 'si', 'nVidia 512 MB Video Card', 'nVidea 512 MB Video Card - with SLI support', 'nVidia 512 MB Video Card', 'demo/nvidia_512.jpg', '4000', '1200', '5000', '1', '0', 0, 'f', '', '', 300, 0.7, 0, 0, 0, 0, 4, 5, 1, 4, '', '0', now(), '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (18, 'PC-BB-512', '0', 'as', 'Bare Bones Computer 2600+/2GB', 'Fully assembled bare bones computer AMD/ATI 512MB/2GB/Red Case', 'Bare Bones Computer 2600+/2GB', 'demo/barebones.jpg', '4000', '1200', '5000', '1', '0', 0, 'f', '', '', 750, 21.3, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '');");
	// Data for table `inventory_assy_list`
	$db->Execute("TRUNCATE TABLE " . TABLE_INVENTORY_ASSY_LIST);
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (1, 14, 'LCD-21-WS', 'LCDisplays 21', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (2, 14, 'HD-150GB', '150GB SATA Hard Drive', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (3, 14, 'DVD-RW', 'DVD RW with Lightscribe', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (4, 14, 'VID-NV-512MB', 'nVidea 512 MB Video Card', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (5, 14, 'RAM-2GB-0.2', '2GB SDRAM', 2);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (6, 14, 'AMD-3600-CPU', 'AMD 3600+ Athlon CPU', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (7, 14, 'MB-ATI-K8N', 'ATI K8 Motherboard w/network', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (8, 14, 'CASE-ALIEN', 'Alien Case - Red', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (9, 14, 'Mouse-S', 'Serial Mouse - 300 DPI', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (10, 14, 'KB-128-ERGO', 'KeysRus ergonomic keyboard', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (11, 18, 'RAM-2GB-0.2', '2GB SDRAM', 2);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (12, 18, 'AMD-3600-CPU', 'AMD 3600+ Athlon CPU', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (13, 18, 'MB-ATI-K8N', 'ATI K8 Motherboard w/network', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (14, 18, 'CASE-ALIEN', 'Alien Case - Red', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (15, 18, 'VID-NV-512MB', 'nVidea 512 MB Video Card', 1);");
	// copy the demo images
	require(DIR_FS_MODULES . 'phreedom/classes/backup.php');
	$backups = new backup;
	if (!@mkdir(DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/demo')) $error = true;
	$dir_source = DIR_FS_MODULES  . 'inventory/images/demo/';
	$dir_dest   = DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/demo/';
	$backups->copy_dir($dir_source, $dir_dest);
	return $error;
  }

}
?>