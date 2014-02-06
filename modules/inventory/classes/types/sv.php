<?php
require_once(DIR_FS_MODULES . 'inventory/classes/inventory.php');
class inventory_sv extends inventory {//Service
	var $product = array();
	var $security_level ;
	public function __construct($id, $sku, $inactive, $description_short, $description_purchase, $description_sales, $image_with_path, $account_sales_income, $account_inventory_wage, $account_cost_of_sales, $item_taxable, $purch_taxable, $item_cost, $cost_method, $price_sheet, $price_sheet_v, $full_price, $item_weight, $quantity_on_hand, $quantity_on_order, $quantity_on_sales_order, $quantity_on_allocation, $minimum_stock_level, $reorder_quantity, $vendor_id, $lead_time, $upc_code, $serialize, $creation_date, $last_update, $last_journal_date){
		$product['id']  					= $id ;
		$product['sku'] 					= $sku ;
		$product['$inactive'] 				= $inactive; 
		$product['inventory_type'] 			= 'sv'; 
		$product['description_short'] 		= $description_short; 
		$product['description_purchase']	= $description_purchase; 
		$product['description_sales'] 		= $description_sales; 
		$product['image_with_path'] 		= $image_with_path;
		$product['account_sales_income']	= $account_sales_income; 
		$product['account_inventory_wage'] 	= $account_inventory_wage; 
		$product['account_cost_of_sales'] 	= $account_cost_of_sales; 
		$product['item_taxable'] 			= $item_taxable; 
		$product['purch_taxable']			= $purch_taxable; 
		$product['item_cost'] 				= $item_cost; 
		$product['cost_method'] 			= $cost_method;
		$product['price_sheet'] 			= $price_sheet;
		$product['price_sheet_v']			= $price_sheet_v; 
		$product['full_price']				= $full_price; 
		$product['item_weight'] 			= $item_weight;  
		$product['quantity_on_hand'] 		= $quantity_on_hand;  
		$product['quantity_on_order'] 		= $quantity_on_order;  
		$product['quantity_on_sales_order'] = $quantity_on_sales_order; 
		$product['quantity_on_allocation'] 	= $quantity_on_allocation; 
		$product['minimum_stock_level'] 	= $minimum_stock_level; 
		$product['reorder_quantity'] 		= $reorder_quantity; 
		$product['vendor_id'] 				= $vendor_id; 
		$product['lead_time'] 				= $lead_time; 
		$product['upc_code'] 				= $upc_code; 
		$product['serialize'] 				= $serialize; 
		$product['creation_date'] 			= $creation_date; 
		$product['last_update'] 			= $last_update ;
		$product['last_journal_date'] 		= $last_journal_date; 
		$security_level = validate_user(SECURITY_ID_MAINTAIN_INVENTORY);
	}
	//this is to check if you are allowed to create a new product
	function check_create_new() {
		global $messageStack;
		if ($security_level < 2) {
		  	$messageStack->add_session(ERROR_NO_PERMISSION, 'error');
		  	return false;
		}
		if (!$sku) {
		  	$messageStack->add(INV_ERROR_SKU_BLANK, 'error');
		  	return false;
		}
		if (gen_validate_sku($sku)) {
		  	$messageStack->add(INV_ERROR_DUPLICATE_SKU, 'error');
			return false;
		}
		
	}
	//this is the general create new inventory item
	function create_new() {
		$product['creation_date']  = 'now()';
		$product['last_update']    = 'now()';
		db_perform(TABLE_INVENTORY, $product, 'insert');
		gen_add_audit_log(INV_LOG_INVENTORY . TEXT_ADD, TEXT_TYPE . ': ' . $inventory_type . ' - ' . $sku);
	}
	
	
	
	//this is to check if you are allowed to remove
	function check_remove() {
		global $messageStack, $db;
		if ($security_level < 4) {
		  	$messageStack->add_session(ERROR_NO_PERMISSION, 'error');
		  	return false;
		}
		// check to see if there is inventory history remaining, if so don't allow delete
		$result = $db->Execute("select id from " . TABLE_INVENTORY_HISTORY . " where sku = '" . sku . "' and remaining > 0");
		if ($result->RecordCount() > 0) {
		 	$messageStack->add(INV_ERROR_DELETE_HISTORY_EXISTS, 'error');
		 	return false;
		}
		$result = $db->Execute( "select id from " . TABLE_JOURNAL_ITEM . " where sku = '" . $sku . "' limit 1");
		if ($result->Recordcount() == 0) {
	  		$this->remove();
		}
			
	}
	//this is the general remove function 
	function remove(){
		$db->Execute("delete from " . TABLE_INVENTORY . " where id = " . $id);
	  	if ($image_with_path) { // delete image
			$file_path = DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/';
			if (file_exists($file_path . $image_with_path)) unlink ($file_path . $image_with_path);
	  	}
	  	$db->Execute("delete from " . TABLE_INVENTORY_SPECIAL_PRICES . " where inventory_id = '" . $id . "'");
		gen_add_audit_log(INV_LOG_INVENTORY . TEXT_DELETE, $sku);
	}
	
	
	//this is to check if you are allowed to save
	function check_save() {
		global $messageStack;
		if ($security_level < 2) {
		  $messageStack->add_session(ERROR_NO_PERMISSION,'error');
		  gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		  break;
		}
		$this->save();
	}	
	
	// this is the general save function.
	function save() {
		
		db_perform(TABLE_INVENTORY, $product, 'update', "id = " . $product['id']);
	}
	
}