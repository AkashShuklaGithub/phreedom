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
//  Path: /modules/phreebooks/dashboards/open_inv/open_inv.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
// 2011-12-20 - Updated to show invoice balance, was total invoice amount 
define('DASHBOARD_OPEN_INV_VERSION','3.3');

require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');

class open_inv extends ctl_panel {
  function __construct() {
    $this->max_length = 20;
  }

  function Install($column_id = 1, $row_id = 0) {
	global $db;
	if (!$row_id) $row_id = $this->get_next_row();
	$params['num_rows'] = '';	// defaults to unlimited rows
	$result = $db->Execute("insert into " . TABLE_USERS_PROFILES . " set 
	  user_id = "       . $_SESSION['admin_id'] . ", 
	  menu_id = '"      . $this->menu_id . "', 
	  module_id = '"    . $this->module_id . "', 
	  dashboard_id = '" . $this->dashboard_id . "', 
	  column_id = "     . $column_id . ", 
	  row_id = "        . $row_id . ", 
	  params = '"       . serialize($params) . "'");
  }

  function Remove() {
	global $db;
	$result = $db->Execute("delete from " . TABLE_USERS_PROFILES . " 
	  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' 
	    and dashboard_id = '" . $this->dashboard_id . "'");
  }

  function Output($params) {
	global $db, $currencies;
	$list_length = array();
	for ($i = 0; $i <= $this->max_length; $i++) $list_length[] = array('id' => $i, 'text' => $i);
	// Build control box form data
	$control  = '<div class="row">';
	$control .= '<div style="white-space:nowrap">' . TEXT_SHOW . TEXT_SHOW_NO_LIMIT;
	$control .= html_pull_down_menu('open_inv_field_0', $list_length, $params['num_rows']);
	$control .= html_submit_field('sub_open_inv', TEXT_SAVE);
	$control .= '</div></div>';
	// Build content box
	$total = 0;
	$sql = "select id, purchase_invoice_id, total_amount, bill_primary_name, currencies_code, currencies_value 
	  from " . TABLE_JOURNAL_MAIN . " 
	  where journal_id = 12 and closed = '0' order by post_date DESC, purchase_invoice_id DESC";
	if ($params['num_rows']) $sql .= " limit " . $params['num_rows'];
	$result = $db->Execute($sql);
	if ($result->RecordCount() < 1) {
	  $contents = CP_OPEN_INV_NO_RESULTS;
	} else {
	  while (!$result->EOF) {
	  	$inv_balance = $result->fields['total_amount'] - fetch_partially_paid($result->fields['id']);
	 	$total += $inv_balance;
		$contents .= '<div style="float:right">' . $currencies->format_full($inv_balance, true, $result->fields['currencies_code'], $result->fields['currencies_value']) . '</div>';
		$contents .= '<div>';
		$contents .= '<a href="' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;oID='.$result->fields['id'].'&amp;jID=12&amp;action=edit', 'SSL') . '">';
		$contents .= $result->fields['purchase_invoice_id'] . ' - ';
		$contents .= htmlspecialchars($result->fields['bill_primary_name']);
		$contents .= '</a></div>' . chr(10);
		$result->MoveNext();
	  }
	}
	if (!$params['num_rows'] && $result->RecordCount() > 0) {
	  $contents .= '<div style="float:right">' . $currencies->format_full($total, true, DEFAULT_CURRENCY, 1) . '</div>';
	  $contents .= '<div><b>' . TEXT_TOTAL . '</b></div>' . chr(10);
	}
	return $this->build_div(CP_OPEN_INV_TITLE, $contents, $control);
  }

  function Update() {
	global $db;
	$params['num_rows'] = db_prepare_input($_POST['open_inv_field_0']);
	$db->Execute("update " . TABLE_USERS_PROFILES . " set params = '" . serialize($params) . "' 
	  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' 
	    and dashboard_id = '" . $this->dashboard_id . "'");
  }

}
?>