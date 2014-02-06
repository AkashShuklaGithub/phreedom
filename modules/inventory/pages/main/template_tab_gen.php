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
//  Path: /modules/inventory/pages/main/template_tab_gen.php
//
?>
<div id="tab_general">

  <div id="inv_image" title="<?php echo $cInfo->sku; ?>">
    <?php if ($cInfo->image_with_path) {
    	echo html_image(DIR_WS_MY_FILES . $_SESSION['company'] . '/inventory/images/' . $cInfo->image_with_path, '', 600) . chr(10);
    } else {
    	echo TEXT_NO_IMAGE;
    }
    ?>
    <div>
	  <h2><?php echo TEXT_SKU . ': ' . $cInfo->sku; ?></h2>
	  <p><?php echo '<br />' . $cInfo->description_sales; ?></p>
    </div>
  </div>

  <table class="ui-widget" style="border-style:none;width:100%">
    <tr><td>
    <table class="ui-widget" style="border-style:none;width:100%">
     <tbody class="ui-widget-content">
     <tr>
	  <td align="right"><?php echo TEXT_SKU; ?></td>
	  <td>
		<?php echo html_input_field('sku', $cInfo->sku, 'readonly="readonly" size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" maxlength="' . MAX_INVENTORY_SKU_LENGTH . '"', false); ?>
		<?php echo TEXT_INACTIVE; ?>
		<?php echo html_checkbox_field('inactive', '1', $cInfo->inactive); ?>
	  </td>
	  <td align="right"><?php echo INV_QTY_ON_HAND; ?></td>
	  <td><?php echo html_input_field('quantity_on_hand', $cInfo->quantity_on_hand, 'disabled="disabled" size="6" maxlength="5" style="text-align:right"', false); ?></td>
	  <td rowspan="5" align="center">
		<?php if ($cInfo->image_with_path) { // show image if it is defined
			echo $cInfo->image_with_path . html_image(DIR_WS_MY_FILES . $_SESSION['company'] . '/inventory/images/' . $cInfo->image_with_path, $cInfo->image_with_path, '', '100', 'onclick="showImage()"');
		} else echo '&nbsp;'; ?>
	  </td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_INVENTORY_DESC_SHORT; ?></td>
	  <td>
	  	<?php echo html_input_field('description_short', $cInfo->description_short, 'size="33" maxlength="32"', false); ?>
		<?php if ($cInfo->id) echo '&nbsp;' . html_icon('categories/preferences-system.png', TEXT_WHERE_USED, 'small', 'onclick="ajaxWhereUsed()"') . chr(10); ?>
	  </td>
	  <td align="right"><?php echo INV_QTY_ON_ORDER; ?></td>
	  <td><?php echo html_input_field('quantity_on_order', $cInfo->quantity_on_order, 'disabled="disabled" size="6" maxlength="5" style="text-align:right"', false); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_ITEM_MINIMUM_STOCK; ?></td>
	  <td><?php echo html_input_field('minimum_stock_level', $cInfo->minimum_stock_level, 'size="6" maxlength="5" style="text-align:right"', false); ?></td>
	  <td align="right"><?php echo INV_QTY_ON_ALLOCATION; ?></td>
	  <td><?php echo html_input_field('quantity_on_allocation', $cInfo->quantity_on_allocation, 'disabled="disabled" size="6" maxlength="5" style="text-align:right"', false); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_ITEM_REORDER_QUANTITY; ?></td>
	  <td><?php echo html_input_field('reorder_quantity', $cInfo->reorder_quantity, 'size="6" maxlength="5" style="text-align:right"', false); ?></td>
	  <td align="right"><?php echo INV_QTY_ON_SALES_ORDER; ?></td>
	  <td><?php echo html_input_field('quantity_on_sales_order', $cInfo->quantity_on_sales_order, 'disabled="disabled" size="6" maxlength="5" style="text-align:right"', false); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_HEADING_LEAD_TIME; ?></td>
	  <td><?php echo html_input_field('lead_time', $cInfo->lead_time, 'size="6" maxlength="5" style="text-align:right"', false); ?></td>
	  <td align="right"><?php echo INV_ENTRY_ITEM_WEIGHT; ?></td>
	  <td><?php echo html_input_field('item_weight', $cInfo->item_weight, 'size="11" maxlength="9" style="text-align:right"', false); ?></td>
	</tr>
	</tbody>
	</table>
    <table class="ui-widget" style="border-style:none;width:100%">
 	 <thead class="ui-widget-header">
	  <tr><th colspan="5"><?php echo TEXT_CUSTOMER_DETAILS; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	<tr>
	  <td valign="top" align="right"><?php echo INV_ENTRY_INVENTORY_DESC_SALES; ?></td>
	  <td colspan="5"><?php echo html_textarea_field('description_sales', 75, 2, $cInfo->description_sales, '', $reinsert_value = true); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_FULL_PRICE; ?></td>
	  <td>
	  	<?php echo html_input_field('full_price', $currencies->precise($cInfo->full_price), 'size="15" maxlength="20" style="text-align:right"', false); 
			if (ENABLE_MULTI_CURRENCY) echo ' (' . DEFAULT_CURRENCY . ')'; 
		    echo '&nbsp;' . html_icon('mimetypes/x-office-spreadsheet.png', BOX_SALES_PRICE_SHEETS, 'small', $params = 'onclick="priceMgr(' . $cInfo->id . ', 0, 0, \'c\')"') . chr(10); ?>
	  </td>
	  <td align="right"><?php echo INV_ENTRY_ITEM_TAXABLE; ?></td>
	  <td colspan="2"><?php echo html_pull_down_menu('item_taxable', $tax_rates, $cInfo->item_taxable); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_DEFAULT_PRICE_SHEET; ?></td>
	  <td><?php echo html_pull_down_menu('price_sheet', get_price_sheet_data('c'), $cInfo->price_sheet); ?></td>
	  <td>&nbsp;</td>
	  <td>&nbsp;</td>
	</tr>
<?php if ($_SESSION['admin_security'][SECURITY_ID_PURCHASE_INVENTORY] > 0) { ?>
	</tbody>
	</table>
    <table class="ui-widget" style="border-style:none;width:100%">
 	 <thead class="ui-widget-header">
	   <tr><th colspan="5"><?php echo TEXT_VENDOR_DETAILS; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	<tr>
	  <td valign="top" align="right"><?php echo INV_ENTRY_INVENTORY_DESC_PURCHASE; ?></td>
	  <td colspan="5"><?php echo html_textarea_field('description_purchase', 75, 2, $cInfo->description_purchase, '', $reinsert_value = true); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_INV_ITEM_COST; ?></td>
	  <td><?php 
	    echo html_input_field('item_cost', $currencies->precise($cInfo->item_cost), 'size="15" maxlength="20" style="text-align:right"', false);
		if (ENABLE_MULTI_CURRENCY) echo ' (' . DEFAULT_CURRENCY . ')';
		echo '&nbsp;' . html_icon('mimetypes/x-office-spreadsheet.png', BOX_PURCHASE_PRICE_SHEETS, 'small', $params = 'onclick="priceMgr(' . $cInfo->id . ', 0, 0, \'v\')"') . chr(10);
		if (($cInfo->inventory_type == 'as' || $cInfo->inventory_type == 'sa') && $cInfo->id) echo '&nbsp;' . html_icon('apps/accessories-calculator.png', TEXT_CURRENT_COST, 'small', 'onclick="ajaxAssyCost()"') . chr(10); ?>
	  </td>
	  <td align="right"><?php echo INV_ENTRY_PURCH_TAX; ?></td>
	  <td colspan="2"><?php echo html_pull_down_menu('purch_taxable', $purch_tax_rates, $cInfo->purch_taxable); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_DEFAULT_PRICE_SHEET; ?></td>
	  <td><?php echo html_pull_down_menu('price_sheet_v', get_price_sheet_data('v'), $cInfo->price_sheet_v); ?></td>
	  <td align="right"><?php echo INV_HEADING_PREFERRED_VENDOR; ?></td>
	  <td colspan="2"><?php echo html_pull_down_menu('vendor_id', gen_get_contact_array_by_type('v'), $cInfo->vendor_id); ?></td>
	</tr>
<?php } ?>
	</tbody>
	</table>
    <table class="ui-widget" style="border-style:none;width:100%">
 	 <thead class="ui-widget-header">
	  <tr><th colspan="5"><?php echo TEXT_ITEM_DETAILS; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	<tr>
	  <td align="right"><?php echo INV_ENTRY_INVENTORY_TYPE; ?></td>
	  <td><?php echo html_hidden_field('inventory_type', $cInfo->inventory_type);
		echo html_input_field('inv_type_desc', $inventory_types_plus[$cInfo->inventory_type], 'readonly="readonly"', false); ?> </td>
	  <td colspan="2"><?php echo html_checkbox_field('remove_image', '1', $cInfo->remove_image) . ' ' . TEXT_REMOVE . ': ' . $cInfo->image_with_path; ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_HEADING_UPC_CODE; ?></td>
	  <td><?php echo html_input_field('upc_code', $cInfo->upc_code, 'size="16" maxlength="13" style="text-align:right"', false); ?></td>
	  <td align="right"><?php echo INV_ENTRY_SELECT_IMAGE; ?></td>
	  <td colspan="2"><?php echo html_file_field('inventory_image'); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_INVENTORY_COST_METHOD; ?></td>
	  <td>
		<?php foreach ($cost_methods as $key=>$value) $cost_pulldown_array[] = array('id' => $key, 'text' => $value); ?>
		<?php echo html_pull_down_menu('cost_method', $cost_pulldown_array, $cInfo->cost_method, (is_null($cInfo->last_journal_date) ? '' : 'disabled="disabled"')); ?>
	    <?php echo ' ' . INV_ENTRY_INVENTORY_SERIALIZE . ' ' . html_checkbox_field('serialize', '1', $cInfo->serialize, '', 'disabled="disabled"'); ?>
	  </td>
	  <td align="right"><?php echo INV_ENTRY_IMAGE_PATH; ?></td>
	  <td colspan="2"><?php echo html_hidden_field('image_with_path', $cInfo->image_with_path); 
		echo html_input_field('inventory_path', substr($cInfo->image_with_path, 0, strrpos($cInfo->image_with_path, '/'))); ?>
	  </td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_ACCT_SALES; ?></td>
	  <td><?php echo html_pull_down_menu('account_sales_income', $gl_array_list, $cInfo->account_sales_income); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_ACCT_INV; ?></td>
	  <td><?php echo html_pull_down_menu('account_inventory_wage', $gl_array_list, $cInfo->account_inventory_wage); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_ACCT_COS; ?></td>
	  <td><?php echo html_pull_down_menu('account_cost_of_sales', $gl_array_list, $cInfo->account_cost_of_sales); ?></td>
	</tr>
   </tbody>
   </table>
<?php if (ENABLE_MULTI_BRANCH || $cInfo->inventory_type == 'sr') { ?>
  </td>
  <td valign="top">
  <?php if (ENABLE_MULTI_BRANCH) {?>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr>
	    <th><?php echo GEN_STORE_ID; ?></th>
	    <th><?php echo INV_HEADING_QTY_IN_STOCK; ?></th>
	  </tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <?php foreach ($store_stock as $stock) {
	  	  echo '<tr>' . chr(10);
		  echo '  <td>' . $stock['store'] . '</td>' . chr(10);
		  echo '  <td align="center">' . $stock['qty'] . '</td>' . chr(10);
	      echo '</tr>' . chr(10);
		} ?>
     </tbody>
    </table>
    <?php } 
    if ($cInfo->inventory_type == 'sr') { ?>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr>
	    <th><?php echo GEN_STORE_ID; ?></th>
	    <th><?php echo TEXT_QUANTITY; ?></th>
	    <th><?php echo INV_HEADING_SERIAL_NUMBER; ?></th>
	  </tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <?php foreach ($serial_list as $value) {
	  	  echo '<tr>' . chr(10);
		  echo '  <td>' . $value['store'] . '</td>' . chr(10);
		  echo '  <td>' . $value['qty'] . '</td>' . chr(10);
		  echo '  <td align="center">' . $value['serial'] . '</td>' . chr(10);
	      echo '</tr>' . chr(10);
	  } ?>
     </tbody>
    </table>
    <?php } ?>
<?php } ?>
  </td></tr>
 </tbody>
 </table>
</div>
