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
//  Path: /modules/phreebooks/pages/popup_journal/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files  *********************/
require(DIR_FS_WORKING . 'functions/phreebooks.php');
/**************   page specific initialization  *************************/
$action = (isset($_GET['action']) ? $_GET['action'] : $_POST['todo']);
// load the sort fields
$_GET['sf'] = $_POST['sort_field'] ? $_POST['sort_field'] : $_GET['sf'];
$_GET['so'] = $_POST['sort_order'] ? $_POST['sort_order'] : $_GET['so'];
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_journal/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($action) {
  case 'go_first':    $_GET['list'] = 1;     break;
  case 'go_previous': $_GET['list']--;       break;
  case 'go_next':     $_GET['list']++;       break;
  case 'go_last':     $_GET['list'] = 99999; break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  default:
}

/*****************   prepare to display templates  *************************/
// generate chart of account types
$coa_types = load_coa_types();

// build the list header
$heading_array = array('post_date' => TEXT_DATE);
if (ENABLE_MULTI_BRANCH) $heading_array['store_id'] = GEN_STORE_ID;
$heading_array['purchase_invoice_id'] = TEXT_REFERENCE;
$heading_array['total_amount']        = TEXT_AMOUNT;
$result      = html_heading_bar($heading_array, $_GET['sf'], $_GET['so'], array(TEXT_DESCRIPTION));
$list_header = $result['html_code'];
$disp_order  = $result['disp_order'];

// build the list for the page selected
$acct_period = ($_GET['search_period']) ? $_GET['search_period'] : $_POST['search_period'];
if (!$acct_period) $acct_period = CURRENT_ACCOUNTING_PERIOD;
$period_filter = ($acct_period == 'all') ? '' : (' and period = ' . $acct_period);

$search_text = ($_GET['search_text'] == TEXT_SEARCH) ? '' : db_input($_GET['search_text']);
if (isset($search_text) && $search_text <> '') {
  $search_fields = array('purchase_invoice_id', 'total_amount');
  // hook for inserting new search fields to the query criteria.
  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
  $search = ' and (' . implode(' like \'%' . $search_text . '%\' or ', $search_fields) . ' like \'%' . $search_text . '%\')';
} else {
  $search = '';
}

$field_list = array('id', 'post_date', 'purchase_invoice_id', 'total_amount', 'store_id');
		
// hook to add new fields to the query return results
if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);

$query_raw = "select " . implode(', ', $field_list) . " from " . TABLE_JOURNAL_MAIN . " 
	where journal_id = 2" . $period_filter . $search . " order by $disp_order, id";

$query_split  = new splitPageResults($_GET['list'], MAX_DISPLAY_SEARCH_RESULTS, $query_raw, $query_numrows);
$query_result = $db->Execute($query_raw);

$include_header   = false;
$include_footer   = true;
$include_tabs     = false;
$include_calendar = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', GL_ENTRY_TITLE);
?>