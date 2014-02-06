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
//  Path: /modules/inventory/pages/main/template_tab_hist.php
//
// start the history tab html
?>
<div id="tab_history">
  <fieldset>
    <legend><?php echo INV_SKU_HISTORY; ?></legend>
	<table class="ui-widget" style="border-style:none;">
	 <tbody class="ui-widget-content">
	  <tr>
	    <td><?php echo INV_DATE_ACCOUNT_CREATION; ?></td>
	    <td><?php echo html_input_field('creation_date', gen_locale_date($cInfo->creation_date), 'readonly="readonly" size="20"', false); ?></td>
	    <td><?php echo INV_DATE_LAST_UPDATE; ?></td>
	    <td><?php echo html_input_field('last_update', gen_locale_date($cInfo->last_update), 'readonly="readonly" size="20"', false); ?></td>
	    <td><?php echo INV_DATE_LAST_JOURNAL_DATE; ?></td>
	    <td><?php echo html_input_field('last_journal_date', gen_locale_date($cInfo->last_journal_date), 'readonly="readonly" size="20"', false); ?></td>
	  </tr>
	  </tbody>
	</table>
  </fieldset>
  <fieldset>
   <legend><?php echo INV_SKU_ACTIVITY; ?></legend>
   <table class="ui-widget" style="border-collapse:collapse;width:100%">
	  <tr><td valign="top" width="50%">
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		 <thead class="ui-widget-header">
		  <tr><th colspan="4"><?php echo INV_OPEN_PO; ?></th></tr>
		  <tr>
		    <th><?php echo INV_PO_NUMBER; ?></th>
		    <th><?php echo INV_PO_DATE; ?></th>
		    <th><?php echo TEXT_QUANTITY; ?></th>
		    <th><?php echo INV_PO_RCV_DATE; ?></th>
		  </tr>
		 </thead>
		 <tbody class="ui-widget-content">
		  <?php 
			if ($sku_history['open_po']) {
			  $odd = true;
			  foreach ($sku_history['open_po'] as $value) {
				echo '<tr class="' . ($odd?'odd':'even') . '">' . chr(10);
				echo '  <td align="center"><a href="' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;action=edit&amp;jID=4&amp;oID=' . $value['id'], 'SSL') . '">' . $value['purchase_invoice_id'] . '</a></td>' . chr(10);
				echo '  <td align="center">' . gen_locale_date($value['post_date']) . '</td>' . chr(10);
				echo '  <td align="center">' . ($value['qty'] ? $value['qty'] : '&nbsp;') . '</td>' . chr(10);
				echo '  <td align="center">' . gen_locale_date($value['date_1']) . '</td>' . chr(10);
				echo '</tr>' . chr(10);
				$odd = !$odd;
			  }
			} else {
			  echo '<tr><td align="center" colspan="4">' . INV_NO_RESULTS . '</td></tr>' . chr(10);
			}
		  ?>
		 </tbody>
		</table>
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		 <thead class="ui-widget-header">
		  <tr><th colspan="4"><?php echo INV_OPEN_SO; ?></th></tr>
		  <tr>
		    <th><?php echo INV_SO_NUMBER; ?></th>
		    <th><?php echo INV_SO_DATE; ?></th>
		    <th><?php echo TEXT_QUANTITY; ?></th>
		    <th><?php echo TEXT_REQUIRED_DATE; ?></th>
		  </tr>
		 </thead>
		 <tbody class="ui-widget-content">
		  <?php 
			if ($sku_history['open_so']) {
			  $odd = true;
			  foreach ($sku_history['open_so'] as $value) {
				echo '<tr class="' . ($odd?'odd':'even') . '">' . chr(10);
				echo '  <td align="center"><a href="' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;action=edit&amp;jID=10&amp;oID=' . $value['id'], 'SSL') . '">' . $value['purchase_invoice_id'] . '</a></td>' . chr(10);
				echo '  <td align="center">' . gen_locale_date($value['post_date']) . '</td>' . chr(10);
				echo '  <td align="center">' . ($value['qty'] ? $value['qty'] : '&nbsp;') . '</td>' . chr(10);
				echo '  <td align="center">' . gen_locale_date($value['date_1']) . '</td>' . chr(10);
				echo '</tr>' . chr(10);
				$odd = !$odd;
			  }
			} else {
			  echo '<tr><td align="center" colspan="4">' . INV_NO_RESULTS . '</td></tr>' . chr(10);
			}
		  ?>
		 </tbody>
		</table>
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		 <thead class="ui-widget-header">
		  <tr><th colspan="4"><?php echo TEXT_AVERAGE_USAGE; ?></th></tr>
		  <tr>
		    <th><?php echo TEXT_LAST_MONTH; ?></th>
		    <th><?php echo TEXT_LAST_3_MONTH; ?></th>
		    <th><?php echo TEXT_LAST_6_MONTH; ?></th>
		    <th><?php echo TEXT_LAST_12_MONTH; ?></th>
		  </tr>
		 </thead>
		 <tbody class="ui-widget-content">
		  <tr>
		    <td align="center"><?php echo $sku_history['averages']['1month']; ?></td>
		    <td align="center"><?php echo $sku_history['averages']['3month']; ?></td>
		    <td align="center"><?php echo $sku_history['averages']['6month']; ?></td>
		    <td align="center"><?php echo $sku_history['averages']['12month']; ?></td>
		  </tr>
		</tbody>
		</table>
	  </td>
	  <td valign="top" width="25%">
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		 <thead class="ui-widget-header">
		  <tr><th colspan="3"><?php echo INV_PURCH_BY_MONTH; ?></th></tr>
		  <tr>
		    <th><?php echo TEXT_MONTH; ?></th>
		    <th><?php echo TEXT_QUANTITY; ?></th>
		    <th><?php echo INV_PURCH_COST; ?></th>
		  </tr>
		 </thead>
		 <tbody class="ui-widget-content">
		  <?php 
		if ($sku_history['purchases']) {
		  $odd = true;
		  foreach ($sku_history['purchases'] as $value) {
		    echo '<tr class="' . ($odd?'odd':'even') . '">' . chr(10);
			echo '  <td align="center">' . gen_locale_date($value['post_date']) . '</td>' . chr(10);
		    echo '  <td align="center">' . ($value['qty'] ? $value['qty'] : '&nbsp;') . '</td>' . chr(10);
		    echo '  <td align="right">' . ($value['total_amount'] ? $currencies->format($value['total_amount']) : '&nbsp;') . '</td>' . chr(10);
			echo '</tr>' . chr(10);
			$odd = !$odd;
		  }
		} else {
		  echo '<tr><td align="center" colspan="4">' . INV_NO_RESULTS . '</td></tr>' . chr(10);
		}
	  ?>
		</tbody>
		</table>
	  </td>
	  <td valign="top" width="25%">
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		 <thead class="ui-widget-header">
		  <tr><th colspan="3"><?php echo INV_SALES_BY_MONTH; ?></th></tr>
		  <tr>
		    <th><?php echo TEXT_MONTH; ?></th>
		    <th><?php echo TEXT_QUANTITY; ?></th>
		    <th><?php echo INV_SALES_INCOME; ?></th>
		  </tr>
		 </thead>
		 <tbody class="ui-widget-content">
		  <?php 
		if ($sku_history['sales']) {
		  $odd = true;
		  foreach ($sku_history['sales'] as $value) {
		    echo '<tr class="' . ($odd?'odd':'even') . '">' . chr(10);
			echo '  <td align="center">' . gen_locale_date($value['post_date']) . '</td>' . chr(10);
		    echo '  <td align="center">' . ($value['qty'] ? $value['qty'] : '&nbsp;') . '</td>' . chr(10);
		    echo '  <td align="right">' . ($value['total_amount'] ? $currencies->format($value['total_amount']) : '&nbsp;') . '</td>' . chr(10);
			echo '</tr>' . chr(10);
			$odd = !$odd;
		  }
		} else {
		  echo '<tr><td align="center" colspan="4">' . INV_NO_RESULTS . '</td></tr>' . chr(10);
		}
	  ?>
		</tbody>
		</table>
	  </td>
	  </tr>
    </table>
  </fieldset>
</div>
