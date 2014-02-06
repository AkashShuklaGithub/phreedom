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
//  Path: /modules/inventory/classes/inventory_fields.php
//
require_once(DIR_FS_MODULES . 'phreedom/classes/fields.php');

class inventory_fields extends fields{
	public  $help_path   = '07.04.05';
	public  $title       = '';
	public  $module      = 'inventory';
	public  $db_table    = TABLE_INVENTORY;
	public  $type_params = 'inventory_type';
	public  $extra_buttons = '';
  
  public function __construct(){
  	gen_pull_language('inventory');
  	$this->type_array[] = array('id' =>'si', 'text' => INV_TYPES_SI);
    $this->type_array[] = array('id' =>'sr', 'text' => INV_TYPES_SR);
    $this->type_array[] = array('id' =>'ms', 'text' => INV_TYPES_MS);
    $this->type_array[] = array('id' =>'as', 'text' => INV_TYPES_AS);
    $this->type_array[] = array('id' =>'sa', 'text' => INV_TYPES_SA);
    $this->type_array[] = array('id' =>'ns', 'text' => INV_TYPES_NS);
    $this->type_array[] = array('id' =>'lb', 'text' => INV_TYPES_LB);
    $this->type_array[] = array('id' =>'sv', 'text' => INV_TYPES_SV);
    $this->type_array[] = array('id' =>'sf', 'text' => INV_TYPES_SF);
    $this->type_array[] = array('id' =>'ci', 'text' => INV_TYPES_CI);
    $this->type_array[] = array('id' =>'ai', 'text' => INV_TYPES_AI);
    $this->type_array[] = array('id' =>'ds', 'text' => INV_TYPES_DS);
    $this->type_array[] = array('id' =>'ia', 'text' => INV_TYPES_IA);
    $this->type_array[] = array('id' =>'mi', 'text' => INV_TYPES_MI);
    $this->type_desc    = INV_ENTRY_INVENTORY_TYPE;
    parent::__construct();    
  }


}
?>