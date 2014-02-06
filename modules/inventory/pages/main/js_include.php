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
//  Path: /modules/inventory/pages/main/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass some php variables
var image_delete_text = '<?php echo TEXT_DELETE; ?>';
var image_delete_msg  = '<?php echo INV_MSG_DELETE_INV_ITEM; ?>';
var text_sku          = '<?php echo TEXT_SKU; ?>';
var delete_icon_HTML  = '<?php echo substr(html_icon("emblems/emblem-unreadable.png", TEXT_DELETE, "small", "onclick=\"if (confirm(\'" . INV_MSG_DELETE_INV_ITEM . "\')) removeBOMRow("), 0, -2); ?>';
// required function called with every page load
function init() {
	$(function() { $('#detailtabs').tabs(); });
	$('#inv_image').dialog({ autoOpen:false, width:800 });
  <?php if ($action <> 'new' && $action <> 'edit') { // set focus for main window
	echo "  document.getElementById('search_text').focus();";
	echo "  document.getElementById('search_text').select();";
  } ?>
  <?php if ($action == 'edit' && $cInfo->inventory_type == 'ms') { // set focus for main window
	echo '  masterStockTitle(0);';
	echo '  masterStockTitle(1);';
	echo '  masterStockBuildSkus();';
  } ?>
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  if (error == 1) {
	alert(error_message);
	return false;
  } else {
	return true;
  }
}

function check_sku() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  var sku = document.getElementById('sku').value;
  if (sku == "") {
	error_message = error_message + "<?php echo JS_SKU_BLANK; ?>";
	error = 1;
  }

  if (error == 1) {
	alert(error_message);
	return false;
  } else {
	return true;
  }
}

function setSkuLength() {
	var sku_val = document.getElementById('sku').value;
	if (document.getElementById('inventory_type').value == 'ms') {
		sku_val.substr(0, <?php echo (MAX_INVENTORY_SKU_LENGTH - 5); ?>);
		document.getElementById('sku').value = sku_val.substr(0, <?php echo (MAX_INVENTORY_SKU_LENGTH - 5); ?>);
		document.getElementById('sku').maxLength = <?php echo (MAX_INVENTORY_SKU_LENGTH - 5); ?>;
	} else {
		document.getElementById('sku').maxLength = <?php echo MAX_INVENTORY_SKU_LENGTH; ?>;
	}
}

function deleteItem(id) {
	location.href = 'index.php?module=inventory&page=main&action=delete&cID='+id;
}

function showImage() {
	$('#inv_image').dialog('open');	
}

function copyItem(id) {
	var skuID = prompt('<?php echo INV_MSG_COPY_INTRO; ?>', '');
	if (skuID) {
		location.href = 'index.php?module=inventory&page=main&action=copy&cID='+id+'&sku='+skuID;
	} else {
		return false;
	}
}

function renameItem(id) {
	var skuID = prompt('<?php echo INV_MSG_RENAME_INTRO; ?>', '');
	if (skuID) {
		location.href = 'index.php?module=inventory&page=main&action=rename&cID='+id+'&sku='+skuID;
	} else {
		return false;
	}
}

function priceMgr(id, cost, price, type) {
  if (!cost)  cost  = document.getElementById('item_cost')  ? cleanCurrency(document.getElementById('item_cost').value)  : 0;
  if (!price) price = document.getElementById('full_price') ? cleanCurrency(document.getElementById('full_price').value) : 0;
  window.open('index.php?module=inventory&page=popup_price_mgr&iID='+id+'&cost='+cost+'&price='+price+'&type='+type,"price_mgr","width=820,height=400,resizable=1,scrollbars=1,top=150,left=200");
}

function InventoryList(rowCnt) {
  window.open("index.php?module=inventory&page=popup_inv&rowID="+rowCnt+"&search_text="+document.getElementById('sku_'+rowCnt).value,"inventory","width=700,height=550,resizable=1,scrollbars=1,top=150,left=200");
}

function loadSkuDetails(iID, rID) {
    $.ajax({
      type: "GET",
	  url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=skuDetails&iID='+iID+'&rID='+rID,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
      },
	  success: processSkuDetails
    });
}

function processSkuDetails(sXml) { // call back function
  var text = '';
  var xml = parseXml(sXml);
  if (!xml) return;
  var rowID = $(xml).find("rID").text();
  document.getElementById('sku_'+rowID).value       = $(xml).find("sku").text();
  document.getElementById('sku_'+rowID).style.color = '';
  document.getElementById('desc_'+rowID).value      = $(xml).find("description_short").text();
}

function addBOMRow() {
	var cell = Array(4);
	var newRow = document.getElementById("bom_table").insertRow(-1);
	var newCell;
	rowCnt = newRow.rowIndex;
	// NOTE: any change here also need to be made below for reload if action fails
	cell[0] = '<td align="center">';
	cell[0] += buildIcon(icon_path+'16x16/emblems/emblem-unreadable.png', image_delete_text, 'style="cursor:pointer" onclick="if (confirm(\''+image_delete_msg+'\')) removeBOMRow('+rowCnt+');"') + '<\/td>';
	cell[1] = '<td align="center">';
	// Hidden fields
	cell[1] += '<input type="hidden" name="id_'+rowCnt+'" id="id_'+rowCnt+'" value="">';
	// End hidden fields
	cell[1] += '<input type="text" name="assy_sku[]" id="sku_'+rowCnt+'" value="" size="<?php echo (MAX_INVENTORY_SKU_LENGTH + 1); ?>" maxlength="<?php echo MAX_INVENTORY_SKU_LENGTH; ?>">&nbsp;';
	cell[1] += buildIcon(icon_path+'16x16/actions/system-search.png', text_sku, 'align="top" style="cursor:pointer" onclick="InventoryList('+rowCnt+')"') + '&nbsp;<\/td>';
	cell[2] = '<td><input type="text" name="assy_desc[]" id="desc_'+rowCnt+'" value="" size="64" maxlength="64"><\/td>';
	cell[3] = '<td><input type="text" name="assy_qty[]" id="qty_'+rowCnt+'" value="0" size="6" maxlength="5"><\/td>';

	for (var i=0; i<cell.length; i++) {
		newCell = newRow.insertCell(-1);
		newCell.innerHTML = cell[i];
	}
	return rowCnt;
}

function removeBOMRow(delRowCnt) {
  var acctIndex;
  // remove row from display by reindexing and then deleting last row
  for (var i=delRowCnt; i<(document.getElementById("bom_table").rows.length); i++) {
	// move the delete icon from the previous row
	if (document.getElementById("bom_table").rows[i].cells[0].innerHTML == '&nbsp;') {
		document.getElementById("bom_table").rows[i-1].cells[0].innerHTML = '&nbsp;';
	} else {
		document.getElementById("bom_table").rows[i-1].cells[0].innerHTML = delete_icon_HTML + i + ');">';
	}
	document.inventory.elements['qty_'+i].value  = document.inventory.elements['qty_'+(i+1)].value;
	document.inventory.elements['sku_'+i].value  = document.inventory.elements['sku_'+(i+1)].value;
	document.inventory.elements['desc_'+i].value = document.inventory.elements['desc_'+(i+1)].value;
// Hidden fields
	document.inventory.elements['id_'+i].value   = document.inventory.elements['id_'+(i+1)].value;
// End hidden fields
  }
  document.getElementById("bom_table").deleteRow(-1);
}

function masterStockTitle(id) {
  if(document.all) { // IE browsers
    document.getElementById('sku_list').rows[1].cells[id+1].innerText = document.getElementById('attr_name_'+id).value;
  } else { //firefox
    document.getElementById('sku_list').rows[1].cells[id+1].textContent = document.getElementById('attr_name_'+id).value;
  }
}

function masterStockBuildList(action, id) {
  switch (action) {
    case 'add':
	  if (document.getElementById('attr_id_'+id).value == '' || document.getElementById('attr_id_'+id).value == '') {
	    alert('<?php echo JS_MS_INVALID_ENTRY; ?>');
		return;
	  }
	  var newOpt = document.createElement("option");
	  newOpt.text = document.getElementById('attr_id_'+id).value + ' : ' + document.getElementById('attr_desc_'+id).value;
	  newOpt.value = document.getElementById('attr_id_'+id).value + ':' + document.getElementById('attr_desc_'+id).value;
	  document.getElementById('attr_index_'+id).options.add(newOpt);
	  document.getElementById('attr_id_'+id).value = '';
	  document.getElementById('attr_desc_'+id).value = '';
	  break;

	case 'delete':
	  if (confirm('<?php echo INV_MSG_DELETE_INV_ITEM; ?>')) {
        var elementIndex = document.getElementById('attr_index_'+id).selectedIndex;
	    document.getElementById('attr_index_'+id).remove(elementIndex);
	  } else {
	    return;
	  }
	  break;

	default:
  }
  masterStockBuildSkus();
}

function masterStockBuildSkus() {
  var newRow, newCell, newValue0, newValue1, newValue2, attrib0, attrib1;
  var ms_attr_0 = '';
  var ms_attr_1 = '';
  while (document.getElementById('sku_list_body').rows.length > 0) {
	document.getElementById('sku_list_body').deleteRow(0);
  }
  var sku = document.getElementById('sku').value;
  newValue0 = '';
  newValue1 = '';
  newValue2 = '';
  if (document.getElementById('attr_index_0').length) {
    for (i=0; i<document.getElementById('attr_index_0').length; i++) {
	  attrib0 = document.getElementById('attr_index_0').options[i].value;
	  ms_attr_0 += attrib0 + ',';
	  attrib0 = attrib0.split(':');
  	  newValue0 = sku + '-' + attrib0[0];
	  newValue1 = attrib0[1];
      if (document.getElementById('attr_index_1').length) {
        for (j=0; j<document.getElementById('attr_index_1').length; j++) {
	      attrib1 = document.getElementById('attr_index_1').options[j].value
	      attrib1 = attrib1.split(':');
  	      newValue0 = sku + '-' + attrib0[0] + attrib1[0];
	      newValue2 = attrib1[1];
          insertTableRow(newValue0, newValue1, newValue2);
        }
	  } else {
        insertTableRow(newValue0, newValue1, newValue2);
	  }
    }
  } else { // blank row
    insertTableRow(newValue0, newValue1, newValue2);
  }

  for (j=0; j<document.getElementById('attr_index_1').length; j++) {
    attrib1 = document.getElementById('attr_index_1').options[j].value;
	ms_attr_1 += attrib1 + ',';
  }

  document.getElementById('ms_attr_0').value = ms_attr_0;
  document.getElementById('ms_attr_1').value = ms_attr_1;
}

function insertTableRow(newValue0, newValue1, newValue2) {
  newRow = document.getElementById('sku_list_body').insertRow(-1);
  if(document.all) { // IE browsers
    newCell = newRow.insertCell(-1);
    newCell.innerText = newValue0;
    newCell = newRow.insertCell(-1);
    newCell.innerText = newValue1;
    newCell = newRow.insertCell(-1);
    newCell.innerText = newValue2;
  } else { //firefox
    newCell = newRow.insertCell(-1);
    newCell.textContent = newValue0;
    newCell = newRow.insertCell(-1);
    newCell.textContent = newValue1;
    newCell = newRow.insertCell(-1);
    newCell.textContent = newValue2
  }
}

// ******* BOF - AJAX BOM Cost function pair *********/
function ajaxAssyCost() {
  var id = document.getElementById('rowSeq').value;
  if (id) {
    $.ajax({
      type: "GET",
	  url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=bomCost&iID='+id,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
      },
	  success: showBOMCost
    });
  }
}

function showBOMCost(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("assy_cost").text()) {
    alert('<?php echo JS_INV_TEXT_ASSY_COST; ?>'+formatPrecise($(xml).find("assy_cost").text()));
  }
}
// ******* EOF - AJAX BOM Cost function pair *********/

// ******* BOF - AJAX BOM Where Used pair *********/
function ajaxWhereUsed() {
  var id = document.getElementById('rowSeq').value;
  if (id) {
    $.ajax({
      type: "GET",
	  url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=whereUsed&iID='+id,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
      },
	  success: showWhereUsed
    });
  }
}

function showWhereUsed(sXml) {
  var text = '';
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("sku_usage").text()) {
    $(xml).find("sku_usage").each(function() {
	  text += $(this).find("text_line").text() + "\n";
    });
	alert(text);
  }
}
// ******* EOF - AJAX BOM Where Used pair *********/

// -->
</script>