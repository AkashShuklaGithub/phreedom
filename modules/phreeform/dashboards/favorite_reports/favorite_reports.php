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
//  Path: /modules/phreeform/dashboards/favorite_reports.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('DASHBOARD_FAVORITE_REPORTS_VERSION','3.2');

require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');

class favorite_reports extends ctl_panel {
  function __construct() {
  }

  function Install($column_id = 1, $row_id = 0) {
	global $db;
	if (!$row_id) $row_id = $this->get_next_row();
	$result = $db->Execute("insert into " . TABLE_USERS_PROFILES . " set 
	  user_id = "       . $_SESSION['admin_id'] . ", 
	  menu_id = '"      . $this->menu_id . "', 
	  module_id = '"    . $this->module_id . "', 
	  dashboard_id = '" . $this->dashboard_id . "', 
	  column_id = "     . $column_id . ", 
	  row_id = "        . $row_id . ", 
	  params = ''");
  }

  function Remove() {
	global $db;
	$result = $db->Execute("delete from " . TABLE_USERS_PROFILES . " 
	  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' 
		and dashboard_id = '" . $this->dashboard_id . "'");
  }

  function Output($params) {
	global $db;
	// load the report list
	$result = $db->Execute("select id, security, doc_title from " . TABLE_PHREEFORM . " 
	  where doc_ext in ('rpt','frm') order by doc_title");
	$data_array = array(array('id' => '', 'text' => GEN_HEADING_PLEASE_SELECT));
	$type_array = array();
	while(!$result->EOF) {
	  if (security_check($result->fields['security'])) {
		$data_array[] = array('id' => $result->fields['id'], 'text' => $result->fields['doc_title']);
	  }
	  $result->MoveNext();
	}
	// Build control box form data
	$control  = '<div class="row">';
	$control .= '<div style="white-space:nowrap">';
	$control .= TEXT_REPORT . '&nbsp;' . html_pull_down_menu('report_id', $data_array);
	$control .= '&nbsp;&nbsp;&nbsp;&nbsp;';
	$control .= html_submit_field('sub_favorite_reports', TEXT_ADD);
	$control .= html_hidden_field('favorite_reports_rId', '');
	$control .= '</div></div>';

	// Build content box
	$contents = '';
	if (is_array($params)) {
	  $index = 1;
	  foreach ($params as $id => $description) {
		$contents .= '<div style="float:right; height:16px;">';
		$contents .= html_icon('phreebooks/dashboard-remove.png', TEXT_REMOVE, 'small', 'onclick="return del_index(\'' . $this->dashboard_id . '\', ' . $index . ')"');
		$contents .= '</div>';
		$contents .= '<div style="height:16px;">';
		$contents .= '  <a href="index.php?module=phreeform&amp;page=popup_gen&amp;rID=' . $id . '" target="_blank">' . $description . '</a>' . chr(10);
		$contents .= '</div>';
		$index++;
	  }
	} else {
	  $contents = CP_FAVORITE_REPORTS_NO_RESULTS;
	}
	return $this->build_div(CP_FAVORITE_REPORTS_TITLE, $contents, $control);
  }

  function Update() {
	global $db;
	$admin_id    = $_SESSION['admin_id'];
	$report_id   = db_prepare_input($_POST['report_id']);
	$result      = $db->Execute("select doc_title from " . TABLE_PHREEFORM . " where id = '" . $report_id . "'");
	$description = $result->fields['doc_title'];
	$remove_id   = db_prepare_input($_POST['favorite_reports_rId']);
	// do nothing if no title or url entered
	if (!$remove_id && $report_id == '') return; 
	// fetch the current params
	$result = $db->Execute("select params from " . TABLE_USERS_PROFILES . "
	  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' 
		and dashboard_id = '" . $this->dashboard_id . "'");
	if ($remove_id) { // remove element
	  $params = unserialize($result->fields['params']);
	  $temp   = array();
	  $index  = 1;
	  foreach ($params as $key => $value) {
		if ($index <> $remove_id) $temp[$key] = $value;
		$index++;
	  }
	  $params = $temp;
	} elseif ($result->fields['params']) { // append new url and sort
	  $params = unserialize($result->fields['params']);
	  $params[$report_id] = $description;
	  asort($params);
	} else { // first entry
	  $params = array($report_id => $description);
	}
	$db->Execute("update " . TABLE_USERS_PROFILES . " set params = '" . serialize($params) . "' 
	  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' 
		and dashboard_id = '" . $this->dashboard_id . "'");
  }
}
?>