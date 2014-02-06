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
//  Path: /modules/inventory/pages/main/template_detail.php
//
// start the form
echo html_form('inventory', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'cID', 'sku', 'add')), 'post', 'enctype="multipart/form-data"');
// include hidden fields
echo html_hidden_field('todo', '') . chr(10);
echo html_hidden_field('rowSeq', $cInfo->id) . chr(10);
echo html_hidden_field('ms_attr_0', $cInfo->ms_attr_0) . chr(10);
echo html_hidden_field('ms_attr_1', $cInfo->ms_attr_1) . chr(10);
// customize the toolbar actions
if ($action == 'properties') {
  echo html_hidden_field('search_text', '') . chr(10);
  $toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
  $toolbar->icon_list['open']['show']     = false;
  $toolbar->icon_list['delete']['show']   = false;
  $toolbar->icon_list['save']['show']     = false;
  $toolbar->icon_list['print']['show']    = false;
} else {
  $toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action','page')), 'SSL') . '\'"';
  $toolbar->icon_list['open']['show']     = false;
  $toolbar->icon_list['delete']['show']   = false;
  if ($security_level > 2 || $first_entry) {
    $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
  } else {
    $toolbar->icon_list['save']['show']   = false;
  }
  $toolbar->icon_list['print']['show']    = false;
  $toolbar->add_help('07.04.01.02');
}
echo $toolbar->build_toolbar(); 
$fields->set_fields_to_display($cInfo->inventory_type);
?>
<h1><?php echo MENU_HEADING_INVENTORY . ' - ' . TEXT_SKU . '# ' . $cInfo->sku . ' (' . $cInfo->description_short . ')'; ?></h1>
<div id="detailtabs">
<ul>
<?php 
  echo add_tab_list('tab_general', TEXT_SYSTEM);
  echo add_tab_list('tab_history', TEXT_HISTORY);
  if ($cInfo->inventory_type == 'as' || $cInfo->inventory_type == 'sa') echo add_tab_list('tab_bom', INV_BOM);
  if ($cInfo->inventory_type == 'ms') echo add_tab_list('tab_master', INV_MS_ATTRIBUTES);
  echo $fields->extra_tab_li;
  // pull in additional custom tabs
  if (isset($extra_inventory_tabs) && is_array($extra_inventory_tabs)) {
    foreach ($extra_inventory_tabs as $tabs) echo add_tab_list($tabs['tab_id'], $tabs['tab_title']);
  }
?>
</ul>
<?php
require (DIR_FS_WORKING . 'pages/main/template_tab_gen.php'); // general tab
require (DIR_FS_WORKING . 'pages/main/template_tab_hist.php'); // history tab
if ($cInfo->inventory_type == 'as' || $cInfo->inventory_type == 'sa') {
  require (DIR_FS_WORKING . 'pages/main/template_tab_bom.php'); // bill of materials tab
}
if ($cInfo->inventory_type == 'ms') {
  require (DIR_FS_WORKING . 'pages/main/template_tab_ms.php'); // master stock tab
}
//********************************* List Custom Fields Here ***********************************
echo $fields->extra_tab_html;
// pull in additional custom tabs
if (isset($extra_inventory_tabs) && is_array($extra_inventory_tabs)) {
  foreach ($extra_inventory_tabs as $tabs) {
	$file_path = DIR_FS_WORKING . 'custom/pages/main/' . $tabs['tab_filename'] . '.php';
	if (file_exists($file_path)) {
	  require($file_path);
	}
  }
}
?>
</div>
</form>
