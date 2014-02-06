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
//  Path: /modules/phreebooks/ajax/low_stock.php
//
/**************   Check user security   *****************************/
$xml   = NULL;
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$cID = db_prepare_input($_GET['cID']); //vendor
$sID = db_prepare_input($_GET['sID']); //store
$rID = db_prepare_input($_GET['rID']); //row
if (isset($sID) && ENABLE_MULTI_BRANCH) $where = " store_id = $sID and ";
$where .= " vendor_id = $cID";	
if (ENABLE_MULTI_BRANCH) {
  $quantity = " sum(remaining) as 'quantity' ";
  $quantity_where = "remaining";
  $sql = "select a.id, a.sku, full_price, item_weight, lead_time, reorder_quantity, " . $quantity . ", inactive, account_inventory_wage, item_cost, purch_taxable, description_purchase, description_short  
	from ".TABLE_INVENTORY. " a join " . TABLE_INVENTORY_HISTORY . "  c ON a.sku = c.sku
	where minimum_stock_level <> 0 and inactive = '0' and ".$quantity_where." < (minimum_stock_level + quantity_on_sales_order - quantity_on_order - quantity_on_allocation) and ".$where." 
	group by sku order by sku";
} else {
  $quantity = " quantity_on_hand as 'quantity' ";
  $quantity_where = "quantity_on_hand";
  $sql = "select id, sku, full_price, item_weight, lead_time, reorder_quantity, " . $quantity . ", inactive, account_inventory_wage, item_cost, purch_taxable, description_purchase, description_short  
	from ".TABLE_INVENTORY."
	where minimum_stock_level <> 0 and inactive = '0' and ".$quantity_where." < (minimum_stock_level + quantity_on_sales_order - quantity_on_order - quantity_on_allocation) and ".$where." 
    order by sku";
}
$result = $db->Execute($sql);
while (!$result->EOF) {
	$xml .= "<LowStock>\n";
	$xml .= "\t" . xmlEntry("id",   				  $result->fields['id']);
	$xml .= "\t" . xmlEntry("sku",   				  $result->fields['sku']);
	$xml .= "\t" . xmlEntry("full_price",			  $result->fields['full_price']);
	$xml .= "\t" . xmlEntry("item_weight",			  $result->fields['item_weight']);
	$xml .= "\t" . xmlEntry("quantity",				  $result->fields['quantity']);
	$xml .= "\t" . xmlEntry("lead_time",			  $result->fields['lead_time']);
	$xml .= "\t" . xmlEntry("inactive",				  $result->fields['inactive']);
	$xml .= "\t" . xmlEntry("reorder_quantity", 	  $result->fields['reorder_quantity']<=0 ? 1 : $result->fields['reorder_quantity']);
	$xml .= "\t" . xmlEntry("account_inventory_wage", $result->fields['account_inventory_wage']);
	$xml .= "\t" . xmlEntry("item_cost",			  $result->fields['item_cost']);
	$xml .= "\t" . xmlEntry("purch_taxable",		  $result->fields['purch_taxable']);
	$xml .= "\t" . xmlEntry("description_purchase",	  $result->fields['description_purchase']);
	$xml .= "\t" . xmlEntry("description_short",	  $result->fields['description_short']);
	$xml .= "\t" . xmlEntry("rID", 					  $rID++);
	$xml .= "</LowStock>\n";
	$result->MoveNext();
}
$xml .= xmlEntry('debug', $debug);
echo createXmlHeader() . $xml . createXmlFooter();
die;
	