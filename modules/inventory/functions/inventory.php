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
//  Path: /modules/inventory/functions/inventory.php
//

function build_bom_list($id, $error = false) {
  global $db;
  $bom_list = array();
  if ($error) { // re-generate the erroneous information
	$x = 1;
	while (isset($_POST['sku_' . $x])) { // while there are item rows to read in
	  $bom_list[] = array(
		'id'          => db_prepare_input($_POST['id_'   . $x]),
		'sku'         => db_prepare_input($_POST['sku_'  . $x]),
		'description' => db_prepare_input($_POST['desc_' . $x]),
		'qty'         => db_prepare_input($_POST['qty_'  . $x]),
	  );
	  $x++;
    }
  } else { // pull the information from the database
	$result = $db->Execute("select id, sku, description, qty 
	  from " . TABLE_INVENTORY_ASSY_LIST . " where ref_id = " . $id . " order by id");
	while (!$result->EOF) {
	  $bom_list[] = $result->fields;
	  $result->MoveNext();
	}
  }
  return $bom_list;
}

  function load_store_stock($sku, $store_id) {
	global $db;
	$sql = "select sum(remaining) as remaining from " . TABLE_INVENTORY_HISTORY . " 
		where store_id = '" . $store_id . "' and sku = '" . $sku . "'";
	$result = $db->Execute($sql);
	$store_bal = $result->fields['remaining'];
	$sql = "select sum(qty) as qty from " . TABLE_INVENTORY_COGS_OWED . " 
		where store_id = '" . $store_id . "' and sku = '" . $sku . "'";
	$result = $db->Execute($sql);
	$qty_owed = $result->fields['qty'];
	return ($store_bal - $qty_owed);
  }

  function inv_calculate_prices($item_cost, $full_price, $encoded_price_levels) {
    global $currencies, $messageStack;
	if (!defined('MAX_NUM_PRICE_LEVELS')) {
	  $messageStack->add('Constant MAX_NUM_PRICE_LEVELS is not defined! returning from inv_calculate_prices','error');
	  return false;
	}
	$price_levels = explode(';', $encoded_price_levels);
	$prices = array();
	for ($i=0, $j=1; $i < MAX_NUM_PRICE_LEVELS; $i++, $j++) {
		$level_info = explode(':', $price_levels[$i]);
		$price      = $level_info[0] ? $level_info[0] : ($i==0 ? $full_price : 0);
		$qty        = $level_info[1] ? $level_info[1] : $j;
		$src        = $level_info[2] ? $level_info[2] : 0;
		$adj        = $level_info[3] ? $level_info[3] : 0;
		$adj_val    = $level_info[4] ? $level_info[4] : 0;
		$rnd        = $level_info[5] ? $level_info[5] : 0;
		$rnd_val    = $level_info[6] ? $level_info[6] : 0;
		if ($j == 1) $src++; // for the first element, the Not Used selection is missing

		switch ($src) {
			case 0: $price = 0;                  break; // Not Used
			case 1: 			                 break; // Direct Entry
			case 2: $price = $item_cost;         break; // Last Cost
			case 3: $price = $full_price;        break; // Retail Price
			case 4: $price = $first_level_price; break; // Price Level 1
		}

		switch ($adj) {
			case 0:                                      break; // None
			case 1: $price -= $adj_val;                  break; // Decrease by Amount
			case 2: $price -= $price * ($adj_val / 100); break; // Decrease by Percent
			case 3: $price += $adj_val;                  break; // Increase by Amount
			case 4: $price += $price * ($adj_val / 100); break; // Increase by Percent
		}

		switch ($rnd) {
			case 0: // None
				break;
			case 1: // Next Integer (whole dollar)
				$price = ceil($price);
				break;
			case 2: // Constant remainder (cents)
				$remainder = $rnd_val;
				if ($remainder < 0) $remainder = 0; // don't allow less than zero adjustments
				// conver to fraction if greater than 1 (user left out decimal point)
				if ($remainder >= 1) $remainder = '.' . $rnd_val;
				$price = floor($price) + $remainder;
				break;
			case 3: // Next Increment (round to next value)
				$remainder = $rnd_val;
				if ($remainder <= 0) { // don't allow less than zero adjustments, assume zero
				  $price = ceil($price);
				} else {
				  $price = ceil($price / $remainder) * $remainder;
				}
		}

		if ($j == 1) $first_level_price = $price; // save level 1 pricing
		$price = $currencies->precise($price);
		if ($src) $prices[$i] = array('qty' => $qty, 'price' => $price);
	}
	return $prices;
  }

  function save_ms_items($sql_data_array, $attributes) {
  	global $db;
	$base_sku = $sql_data_array['sku'];
	// split attributes
	$attr0 = explode(',', $attributes['ms_attr_0']);
	$attr1 = explode(',', $attributes['ms_attr_1']);
	if (!count($attr0)) return true; // no attributes, nothing to do
	// build skus
	$sku_list = array();
	for ($i = 0; $i < count($attr0); $i++) {
		$temp = explode(':', $attr0[$i]);
		$idx0 = $temp[0];
		if (count($attr1)) {
			for ($j = 0; $j < count($attr1); $j++) {
				$temp = explode(':', $attr1[$j]);
				$idx1 = $temp[0];
				$sku_list[] = $sql_data_array['sku'] . '-' . $idx0 . $idx1;
			}
		} else {
			$sku_list[] = $sql_data_array['sku'] . '-' . $idx0;
		}
	}
	// either update, delete or insert sub skus depending on sku list
	$result = $db->Execute("select sku from " . TABLE_INVENTORY . " 
		where inventory_type = 'mi' and sku like '" . $sql_data_array['sku'] . "-%'");
	$existing_sku_list = array();
	while (!$result->EOF) {
		$existing_sku_list[] = $result->fields['sku'];
		$result->MoveNext();
	}
	$delete_list = array_diff($existing_sku_list, $sku_list);
	$update_list = array_intersect($existing_sku_list, $sku_list);
	$insert_list = array_diff($sku_list, $update_list);
	$sql_data_array['inventory_type'] = 'mi';
	foreach($delete_list as $sku) {
		$result = $db->Execute("delete from " . TABLE_INVENTORY . " where sku = '" . $sku . "'");
	}
	foreach($update_list as $sku) {
		$sql_data_array['sku'] = $sku;
		db_perform(TABLE_INVENTORY, $sql_data_array, 'update', "sku = '" . $sku . "'");
	}
	foreach($insert_list as $sku) {
		$sql_data_array['sku'] = $sku;
		db_perform(TABLE_INVENTORY, $sql_data_array, 'insert');
	}
	// update/insert into inventory_ms_list table
	$result = $db->Execute("select id from " . TABLE_INVENTORY_MS_LIST . " where sku = '" . $base_sku . "'");
	$exists = $result->RecordCount();
	$data_array = array(
		'sku'         => $base_sku,
		'attr_0'      => $attributes['ms_attr_0'],
		'attr_name_0' => $attributes['attr_name_0'],
		'attr_1'      => $attributes['ms_attr_1'],
		'attr_name_1' => $attributes['attr_name_1']);
	if ($exists) {
		db_perform(TABLE_INVENTORY_MS_LIST, $data_array, 'update', "id = " . $result->fields['id']);
	} else {
		db_perform(TABLE_INVENTORY_MS_LIST, $data_array, 'insert');
	}
  }

  function gather_history($sku) {
    global $db;
	$inv_history = array();
	$dates = gen_get_dates();
	$cur_month = $dates['ThisYear'] . '-' . substr('0' . $dates['ThisMonth'], -2) . '-01';
	for($i = 0; $i < 13; $i++) {
	  $index = substr($cur_month, 0, 7);
	  $history['purchases'][$index] = array(
	  	'post_date'    => $cur_month,
	  	'qty'          => 0,
	  	'total_amount' => 0,
	  );
	  $history['sales'][$index] = array(
	  	'post_date'    => $cur_month,
	  	'qty'          => 0,
	  	'usage'        => 0,
	  	'total_amount' => 0,
	  );
	  $cur_month = gen_specific_date($cur_month, 0, -1, 0);
	}
	$last_year = ($dates['ThisYear'] - 1) . '-' . substr('0' . $dates['ThisMonth'], -2) . '-01';

	// load the SO's and PO's and get order, expected del date
	$sql = "select m.id, m.journal_id, m.store_id, m.purchase_invoice_id, i.qty, i.post_date, i.date_1, 
	i.id as item_id 
	  from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id 
	  where m.journal_id in (4, 10) and i.sku = '" . $sku ."' and m.closed = '0' 
	  order by i.date_1";
	$result = $db->Execute($sql);
	while(!$result->EOF) {
	  switch ($result->fields['journal_id']) {
	    case  4:
		  $gl_type   = 'por';
		  $hist_type = 'open_po';
		  break;
	    case 10:
		  $gl_type   = 'sos';
		  $hist_type = 'open_so';
		  break;
	  }
	  $sql = "select sum(qty) as qty from " . TABLE_JOURNAL_ITEM . " 
		where gl_type = '" . $gl_type . "' and so_po_item_ref_id = " . $result->fields['item_id'];
	  $adj = $db->Execute($sql); // this looks for partial received to make sure this item is still on order
	  if ($result->fields['qty'] > $adj->fields['qty']) {
		$history[$hist_type][] = array(
		  'id'                  => $result->fields['id'],
		  'store_id'            => $result->fields['store_id'],
		  'purchase_invoice_id' => $result->fields['purchase_invoice_id'],
		  'post_date'           => $result->fields['post_date'],
		  'qty'                 => $result->fields['qty'],
		  'date_1'              => $result->fields['date_1'],
		);
	  }
	  $result->MoveNext();
	}

	// load the units received and sold, assembled and adjusted
	$sql = "select m.journal_id, m.post_date, i.qty, i.gl_type, i.credit_amount, i.debit_amount 
	  from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id 
	  where m.journal_id in (6, 12, 14, 16, 19, 21) and i.sku = '" . $sku ."' and m.post_date >= '" . $last_year . "' 
	  order by m.post_date DESC";
	$result = $db->Execute($sql);
	while(!$result->EOF) {
	  $month = substr($result->fields['post_date'], 0, 7);
	  switch ($result->fields['journal_id']) {
	    case  6:
	    case 21:
	      $history['purchases'][$month]['qty']          += $result->fields['qty'];
	      $history['purchases'][$month]['total_amount'] += $result->fields['debit_amount'];
		  break;
	    case 12:
	    case 19:
	      $history['sales'][$month]['qty']              += $result->fields['qty'];
	      $history['sales'][$month]['usage']            += $result->fields['qty'];
	      $history['sales'][$month]['total_amount']     += $result->fields['credit_amount'];
		  break;
	    case 14:
		  if ($result->fields['gl_type'] == 'asi') { // only if part of an assembly
	        $history['sales'][$month]['usage'] -= $result->fields['qty']; // need to negate quantity since assy.
		  }
		  break;
	    case 16:
	      $history['sales'][$month]['usage'] += $result->fields['qty'];
		  break;
	  }
	  $result->MoveNext();
	}

	// calculate average usage
	$cnt = 0;
	$history['averages'] = array();
	foreach ($history['sales'] as $key => $value) {
	  if ($cnt == 0) { 
	    $cnt++;
		continue; // skip current month since we probably don't have the full months worth
	  }
	  $history['averages']['12month'] += $history['sales'][$key]['usage'];
	  if ($cnt < 7) $history['averages']['6month'] += $history['sales'][$key]['usage'];
	  if ($cnt < 4) $history['averages']['3month'] += $history['sales'][$key]['usage'];
	  if ($cnt < 2) $history['averages']['1month'] += $history['sales'][$key]['usage'];
	  $cnt++;
	}
	$history['averages']['12month'] = round($history['averages']['12month'] / 12, 2);
	$history['averages']['6month']  = round($history['averages']['6month']  /  6, 2);
	$history['averages']['3month']  = round($history['averages']['3month']  /  3, 2);
	return $history;
  }

  function inv_calculate_sales_price($qty, $sku_id, $contact_id = 0, $type = 'c') {
    global $db, $currencies;
	$price_sheet = '';
	$contact_tax = 1;
	if ($contact_id) {
	  $contact = $db->Execute("select type, price_sheet, tax_id from " . TABLE_CONTACTS . " where id = '" . $contact_id . "'");
	  $type        = $contact->fields['type'];
	  $price_sheet = $contact->fields['price_sheet'];
	  $contact_tax = $contact->fields['tax_id'];
	}
	// get the inventory prices
	$inventory = $db->Execute("select item_cost, full_price, price_sheet, price_sheet_v, item_taxable, purch_taxable 
	  from " . TABLE_INVENTORY . " where id = '" . $sku_id . "'");
	$inv_price_sheet = ($type == 'v') ? $inventory->fields['price_sheet_v'] : $inventory->fields['price_sheet'];
	// set the default tax rates
	$purch_tax = ($contact_tax == 0 && $type=='v') ? 0 : $inventory->fields['purch_taxable'];
	$sales_tax = ($contact_tax == 0 && $type=='c') ? 0 : $inventory->fields['item_taxable'];
	// determine what price sheet to use, priority: customer, inventory, default
	if ($price_sheet <> '') {
	  $sheet_name = $price_sheet;
	} elseif ($inv_price_sheet <> '') {
	  $sheet_name = $inv_price_sheet;
	} else {
	  $default_sheet = $db->Execute("select sheet_name from " . TABLE_PRICE_SHEETS . " 
		where type = '" . $type . "' and default_sheet = '1'");
	  $sheet_name = ($default_sheet->RecordCount() == 0) ? '' : $default_sheet->fields['sheet_name'];
	}
	// determine the sku price ranges from the price sheet in effect
	$levels = false;
	if ($sheet_name <> '') {
	  $sql = "select id, default_levels from " . TABLE_PRICE_SHEETS . " 
	    where inactive = '0' and type = '" . $type . "' and sheet_name = '" . $sheet_name . "' and 
	    (expiration_date is null or expiration_date = '0000-00-00' or expiration_date >= '" . date('Y-m-d') . "')";
	  $price_sheets = $db->Execute($sql);
	  // retrieve special pricing for this inventory item
	  $sql = "select price_sheet_id, price_levels from " . TABLE_INVENTORY_SPECIAL_PRICES . " 
		where price_sheet_id = '" . $price_sheets->fields['id'] . "' and inventory_id = " . $sku_id;
	  $result = $db->Execute($sql);
	  $special_prices = array();
	  while (!$result->EOF) {
	    $special_prices[$result->fields['price_sheet_id']] = $result->fields['price_levels'];
	    $result->MoveNext();
	  }
	  $levels = isset($special_prices[$price_sheets->fields['id']]) ? $special_prices[$price_sheets->fields['id']] : $price_sheets->fields['default_levels'];
	}
	if ($levels) {
	  $prices = inv_calculate_prices($inventory->fields['item_cost'], $inventory->fields['full_price'], $levels);
	  $price = '0.0';
	  if(is_array($prices)) foreach ($prices as $value) if ($qty >= $value['qty']) $price = $currencies->clean_value($value['price']);
	} else {
	  $price = ($type=='v') ? $inventory->fields['item_cost'] : $inventory->fields['full_price'];
	}
	return array('price'=>$price, 'sales_tax'=>$sales_tax, 'purch_tax'=>$purch_tax);
  }

function inv_status_open_orders($journal_id, $gl_type) { // checks order status for order balances, items received/shipped
  global $db;
  $item_list = array();
  $orders = $db->Execute("select id from " . TABLE_JOURNAL_MAIN . " 
  	where journal_id = " . $journal_id . " and closed = '0'");
  while (!$orders->EOF) {
    $total_ordered = array(); // track this SO/PO sku for totals, to keep >= 0
    $id = $orders->fields['id'];
	// retrieve information for requested id
	$sql = " select sku, qty from " . TABLE_JOURNAL_ITEM . " where ref_id = " . $id . " and gl_type = '" . $gl_type . "'";
	$ordr_items = $db->Execute($sql);
	while (!$ordr_items->EOF) {
	  $item_list[$ordr_items->fields['sku']] += $ordr_items->fields['qty'];
	  $total_ordered[$ordr_items->fields['sku']] += $ordr_items->fields['qty'];
	  $ordr_items->MoveNext();
	}
	// calculate received/sales levels (SO and PO)
	$sql = "select i.qty, i.sku, i.ref_id 
		from " . TABLE_JOURNAL_MAIN . " m left join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
		where m.so_po_ref_id = " . $id;
	$posted_items = $db->Execute($sql);
	while (!$posted_items->EOF) {
	  foreach ($item_list as $sku => $balance) {
		if ($sku == $posted_items->fields['sku']) {
		  $total_ordered[$sku] -= $posted_items->fields['qty'];
		  $adjustment = $total_ordered[$sku] > 0 ? $posted_items->fields['qty'] : max(0, $total_ordered[$sku] + $posted_items->fields['qty']);
		  $item_list[$sku] -= $adjustment;
		}
	  }
	  $posted_items->MoveNext();
	}
	$orders->MoveNext();
  } // end for each open order
  return $item_list;
}

?>