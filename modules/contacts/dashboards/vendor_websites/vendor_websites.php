<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright (c) 2008, 2009, 2010, 2011 PhreeSoft, LLC             |
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
//  Path: /modules/contacts/dashboards/vendor_websites/vendor_websites.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('DASHBOARD_VENDOR_WEBSITES_VERSION','3.2');

class vendor_websites extends ctl_panel {
  function __construct() {
  }

  function Install($column_id = 1, $row_id = 0) {
	global $db;
	if (!$row_id) $row_id = $this->get_next_row();
	// fetch the pages params to copy to new install
	$result = $db->Execute("select params from " . TABLE_USERS_PROFILES . "
	  where menu_id = '" . $this->menu_id . "' and dashboard_id = '" . $this->dashboard_id . "'"); // just need one
	$db->Execute("insert into " . TABLE_USERS_PROFILES . " set 
	  user_id = "       . $_SESSION['admin_id'] . ", 
	  menu_id = '"      . $this->menu_id . "', 
	  module_id = '"    . $this->module_id . "', 
	  dashboard_id = '" . $this->dashboard_id . "', 
	  column_id = "     . $column_id . ", 
	  row_id = "        . $row_id . ", 
	  params = '"       . $result->fields['params'] . "'");
  }

  function Remove() {
	global $db;
	$result = $db->Execute("delete from " . TABLE_USERS_PROFILES . " 
	  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' 
	    and dashboard_id = '" . $this->dashboard_id . "'");
  }

  function Output($params) {
	global $db;
	$sql = "select a.primary_name, a.website 
	  from " . TABLE_CONTACTS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.id = a.ref_id 
	  where  c.type = 'v' and c.inactive = '0' and a.website !='' order by a.primary_name";
	$result = $db->Execute($sql);
	// Build control box form data
	
	// Build content box
	$contents = '';
	if ($result->RecordCount() < 1) {
	  $contents = CP_VENDOR_WEBSITES_NO_RESULTS;
	} else {
	  while (!$result->EOF) {
		$contents .= '<div style="height:16px;">';
		$contents .= '  <a href=" http://'. $result->fields['website'] . '" target="_blank">' . $result->fields['primary_name'] . '</a>' . chr(10);
		$contents .= '</div>';
		$index++;
		$result->MoveNext();
	  }
	} 
	return $this->build_div(CP_VENDOR_WEBSITES_TITLE, $contents, $control);
  }

  function Update() {
	global $db;
	$my_title  = db_prepare_input($_POST['vendor_websites_field_0']);
	$my_url    = db_prepare_input($_POST['vendor_websites_field_1']);
	$remove_id = db_prepare_input($_POST[$this->dashboard_id . '_rId']);
	// do nothing if no title or url entered
	if (!$remove_id && ($my_title == '' || $my_url == '')) return; 
	// fetch the current params
	$result = $db->Execute("select params from " . TABLE_USERS_PROFILES . "
	  where menu_id = '" . $this->menu_id . "' and dashboard_id = '" . $this->dashboard_id . "'"); // just need one
	if ($remove_id) { // remove element
	  $params     = unserialize($result->fields['params']);
	  $first_part = array_slice($params, 0, $remove_id - 1);
	  $last_part  = array_slice($params, $remove_id);
	  $params     = array_merge($first_part, $last_part);
	} elseif ($result->fields['params']) { // append new url and sort
	  $params     = unserialize($result->fields['params']);
	  $params[$my_title] = $my_url;
	  ksort($params);
	} else { // first entry
	  $params     = array($my_title => $my_url);
	}
	$db->Execute("update " . TABLE_USERS_PROFILES . " set params = '" . serialize($params) . "' 
	  where menu_id = '" . $this->menu_id . "' and dashboard_id = '" . $this->dashboard_id . "'");
  }
}
?>