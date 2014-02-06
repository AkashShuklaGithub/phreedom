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
//  Path: /modules/phreebooks/dashboards/so_status/so_status.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('DASHBOARD_SO_STATUS_VERSION','3.2');

class so_status extends ctl_panel {
  function __construct() {
    $this->max_length = MAX_NUM_SO_LIST;
  }

  function Install($column_id = 1, $row_id = 0) {
	global $db;
	if (!$row_id) $row_id = $this->get_next_row();
	$params['num_rows'] = '5';	// defaults to 5 rows to start
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
	$list_order = array(
	  array('id'=>'asc', 'text'=>TEXT_ASC),
	  array('id'=>'desc','text'=>TEXT_DESC),
	);
	$list_limit = array(
	  array('id'=>'0', 'text'=>TEXT_NO),
	  array('id'=>'1', 'text'=>TEXT_YES),
	);
	// Build control box form data
	$control  = '<div class="row">';
	$control .= '  <div style="white-space:nowrap">';
	$control .= TEXT_SHOW.TEXT_SHOW_NO_LIMIT.'&nbsp'.html_pull_down_menu('so_status_field_0', $list_length,$params['num_rows']).'<br />';
	$control .= CP_SO_STATUS_SORT_ORDER     .'&nbsp'.html_pull_down_menu('so_status_field_1', $list_order, $params['order']).'<br />';
	$control .= CP_SO_STATUS_HIDE_FUTURE    .'&nbsp'.html_pull_down_menu('so_status_field_2', $list_limit, $params['limit']);
	$control .= html_submit_field('sub_so_status', TEXT_SAVE);
	$control .= '  </div>';
	$control .= '</div>';
	// Build content box
	$sql = "select id, post_date, purchase_invoice_id, bill_primary_name, total_amount, currencies_code, currencies_value 
	  from " . TABLE_JOURNAL_MAIN . " where journal_id = 10 and closed = '0'";
	if ($params['limit']=='1')    $sql .= " and post_date <= '".date('Y-m-d')."'";
	if ($params['order']=='desc') $sql .= " order by post_date desc";
	if ($params['num_rows'])      $sql .= " limit " . $params['num_rows'];
	$result = $db->Execute($sql);
	if ($result->RecordCount() < 1) {
	  $contents = ACT_NO_RESULTS;
	} else {
	  while (!$result->EOF) {
		$contents .= '<div style="float:right">' . $currencies->format_full($result->fields['total_amount'], true, $result->fields['currencies_code'], $result->fields['currencies_value']) . '</div>';
		$contents .= '<div>';
		$contents .= '<a href="' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;oID=' . $result->fields['id'] . '&amp;jID=10&amp;action=edit', 'SSL') . '">';
		$contents .= $result->fields['purchase_invoice_id'] . ' - ';
		$contents .= gen_locale_date($result->fields['post_date']);
		$name      = gen_trim_string($result->fields['bill_primary_name'], 20, true);
		$contents .= ' ' . htmlspecialchars($name);
		$contents .= '</a></div>' . chr(10);
		$result->MoveNext();
	  }
  }
  return $this->build_div(CP_SO_STATUS_TITLE, $contents, $control);
}

  function Update() {
	global $db;
	$params = array(
	  'num_rows'=> db_prepare_input($_POST['so_status_field_0']),
	  'order'   => db_prepare_input($_POST['so_status_field_1']),
	  'limit'   => db_prepare_input($_POST['so_status_field_2']),
	);
	$db->Execute("update " . TABLE_USERS_PROFILES . " set params = '" . serialize($params) . "' 
	  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' 
		and dashboard_id = '" . $this->dashboard_id . "'");
  }
}
?>