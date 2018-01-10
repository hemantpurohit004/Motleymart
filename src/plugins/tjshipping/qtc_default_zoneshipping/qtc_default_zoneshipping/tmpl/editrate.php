<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// no direct access
JHTML::_('behavior.modal');
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/plugins/tjshipping/qtc_default_zoneshipping/qtc_default_zoneshipping/qtczoneShipHelper.php');
$qtczoneShipHelper = new qtczoneShipHelper;
$comquick2cartHelper=new comquick2cartHelper;
$productHelper = new productHelper;
$qtcshiphelper = new qtcshiphelper;

$taxHelper = new taxHelper;

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$extension_id = $jinput->get('extension_id');
$rateId = $jinput->get('rateId',0);
$methodId = $jinput->get('methodId',0);

$shipMethDetail = $qtcshiphelper->getShipMethDetail($methodId);
$itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=cp');

if (!empty($methodId))
{
	$status = $comquick2cartHelper->store_authorize('', $shipMethDetail['store_id']);

	if (!$status)
	{
		$zoneHelper->showUnauthorizedMsg();
		return false;
	}
}

?>
<script type="text/javascript">

function qtcUpdateShipMethRates()
{
	var extension_id = <?php echo $extension_id; ?>;
	var shipMethodId = <?php echo $methodId; ?>;
	var rateId = <?php echo $rateId; ?>;

	var SelectedZoneVal  = document.id('zone_id').value;
	var flag = 1;

	if (document.id('qtc_shipping_range_start') && document.id('qtc_shipping_range_end'))
	{
		var SelectedRangeLow  = document.id('qtc_shipping_range_start').value;
		var SelectedRangeHigh  = document.id('qtc_shipping_range_end').value;

		if(SelectedRangeLow == '')
		{
			var msg = "<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_INVALID_RANGE_MIN_SELECTION'); ?>"
			alert(msg);
			document.id('qtc_shipping_range_start').focus();
			document.id('qtc_shipping_range_start').value = "";

			return false;
		}

		if(SelectedRangeHigh  == '')
		{
			var msg = "<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_INVALID_RANGE_MAX_SELECTION'); ?>"
			alert(msg);
			document.id('qtc_shipping_range_end').focus();
			document.id('qtc_shipping_range_end').value = "";
			return false;
		}

		if (Number(SelectedRangeLow) >= Number(SelectedRangeHigh))
		{
			var msg = "<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_INVALID_RANGE'); ?>"
			alert(msg);
			return false;
		}
	}

	if (SelectedZoneVal == '')
	{
		var msg = "<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_INVALID_ZONE_SELECTION'); ?>"
		alert(msg);
		return false;
	}

	techjoomla.jQuery('.qtc_ship_rates_validate input').each(function(){

		if (techjoomla.jQuery(this).val() == '')
		{
			techjoomla.jQuery(this).focus();
			techjoomla.jQuery(this).val('');

			flag = 0;
		}
	});

	if (flag == 0)
	{
		var msg = "<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_INVALID_SHIPPING_COST'); ?>"
		alert(msg);

		return false;
	}

	techjoomla.jQuery('.qtc_handle_rates_validate input').each(function(){

		if (techjoomla.jQuery(this).val() == '')
		{
			var msg = "<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_INVALID_HANDLING_FEE'); ?>"
			alert(msg);

			techjoomla.jQuery(this).focus();
			techjoomla.jQuery(this).val('');

			flag = 0;
		}
	});

	if (flag == 0)
	{
		return false;
	}

	values = techjoomla.jQuery('#qtcEditRateform').serialize();
	techjoomla.jQuery.ajax({
			//url: '?option=com_quick2cart&task=cartcheckout.qtc_autoSave&stepId='+stepId,
			url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&task=shipping.qtcHandleShipAjaxCall&plugview=setrates&extension_id=" +extension_id +"&methodId="+ shipMethodId +"&rateId="+ rateId + '&tmpl=component',
			type: 'POST',
			async:false,
			data:values,
			dataType: 'json',
			beforeSend: function() {},
			complete: function() {	},
			success: function(response)
			{
				 // Remove Error dive content
				techjoomla.jQuery('#qtcErrorContentDiv').html('');
				techjoomla.jQuery('.qtcError').fadeOut();

				// Get selected zone
				var SelectedZone = techjoomla.jQuery("#zone_id").children("option").filter(":selected").text() ;
				var taxrule_id= 1;
				var rateId = response.rateId;
				var q="'";
				var editbtn = '<input type="button" value="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_EDIT"); ?>" class="btn btn-primary">';
				var editHref = 'index.php?option=com_quick2cart&view=taxprofileform&layout=setrule&id='+rateId+'&tmpl=component';
				var editLink = '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-450, y: window.innerHeight-250}}" href="'+editHref+'" class="modal qtc_modal">'+editbtn+'</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

				var delLink = '<input onclick="qtcDeleteShipRate('+
							rateId+',this);" class="btn btn-danger" type="button" value="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_DELETE"); ?>">';

				// Zone change
				window.parent.jQuery('#qtczoneTd_'+rateId).html(SelectedZone);

				<?php
				if ($shipMethDetail['shipping_type'] !=3)
				{
				?>
				window.parent.jQuery('#qtcRangeTd_'+rateId).html(response.rangeTd);

				<?php
				}
				?>
				window.parent.jQuery('#qtcShipCostTd_'+rateId).html(response.shipCostTd);
				window.parent.jQuery('#qtcHandleCostTd_'+rateId).html(response.handleCostTd);
/*
					var result='<tr id="qtcRateId_"' + rateId +'><td></td><td>'+SelectedZone+'</td><td >'  + response.rangeTd+ '</td>  <td>' +response.shipCostTd + '</td> <td>' +response.handleCostTd + '</td><td>' + editLink + delLink + '</td></tr>';
				techjoomla.jQuery('#tableBody').append(result);*/

				// intialize squeeze box again for edit button to work
				SqueezeBox.initialize({});
				SqueezeBox.assign($$('a.modal'),
				{
					parse: 'rel'
				});
				window.parent.SqueezeBox.close();
			},
			error: function(response) {	}
		});
}

</script>

<form name='qtcEditRateform' id="qtcEditRateform" method="post" >
<legend id="" ><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_UPDATE_SHIP_RATE") ?>&nbsp; <?php echo !empty($shipMethDetail['name']) ? ucfirst($shipMethDetail['name']) : ''; ?><small><?php //echo JText::_('QTC_BILLIN_DESC')?></small></legend>
	<div class="">
		<table class=" table table-striped">
			<thead>
				<tr>
					<th width="20%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_ZONES"); ?></th>

					<?php
					if ($shipMethDetail['shipping_type'] !=3)
					{
					?>
					<th width="20%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_RANGE"); ?></th>

					<?php
					}
					?>
					<th width="25%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_SHIP_COST"); ?></th>
					<th width="25%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_HANDLE_FEE"); ?></th>
					<th width="10%"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php
						$default = !empty($shipFormData['rateDetail']['zone_id']) ? $shipFormData['rateDetail']['zone_id'] : 0;
						$options = array();
						$options[] = JHtml::_('select.option', "", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SEL_ZONE'));

						foreach ($shipFormData['zonelist'] as $zone)
						{
							$profileText = '';
							$profileText = $zone['name'] . ' [' . JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE') . ':' . $zone['storeName'] . ' ] ';
							$options[] = JHtml::_('select.option', $zone['id'], $profileText);
						}

						echo JHtml::_('select.genericlist',$options,"zone_id",'class=""  required="" aria-invalid="false" size="1" ','value','text',$default,'zone_id');
						?>
					</td>

					<?php

					if ($shipMethDetail['shipping_type'] !=3)
					{
					?>
					<td>
						<input type="text" id="qtc_shipping_range_start" class="input-mini" name="rangeFrom" value="<?php echo isset($shipFormData['rateDetail']['rangeFrom']) ? $shipFormData['rateDetail']['rangeFrom'] : '';?>" size="8" />
						<br/>
						<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_TO"); ?>
						<br/>
						<input type="text" id="qtc_shipping_range_end" class="input-mini" name="rangeTo" value="<?php echo isset($shipFormData['rateDetail']['rangeTo']) ? $shipFormData['rateDetail']['rangeTo'] : '';?>" size="8" />
					</td>

					<?php
					}
					?>
					<td class="qtc_ship_rates_validate">
						<?php
						$shipCostCurr = array();
						// create ship Currency array
						if (!empty($shipFormData['rateDetail']['rateCurrDetails']))
						{
							foreach ($shipFormData['rateDetail']['rateCurrDetails'] as $dbRate)
							{
								$key = $dbRate['currency'];
								$shipCostCurr[$key] = $dbRate['shipCost'];
							}
						}
						// Show shipping cost fields
						echo $productHelper->getMultipleCurrFields($name = 'shipCost', $shipCostCurr);
						?>
					</td>

					<td class="qtc_handle_rates_validate">
						<?php
						$handleCostCurr = array();

						// create handle Currency array
						if (!empty($shipFormData['rateDetail']['rateCurrDetails']))
						{
							foreach ($shipFormData['rateDetail']['rateCurrDetails'] as $dbRate)
							{
								$key = $dbRate['currency'];
								$handleCostCurr[$key] = $dbRate['handleCost'];
							}
						}
						// Show handling cost fields
						echo $productHelper->getMultipleCurrFields($name = 'handleCost', $handleCostCurr);
						?>
					</td>
					<td>
						<?php
							$fn_para =  $extension_id . "," . $rateId . ",'".  JUri::base() . "'" ;

						?>
						<input class="btn btn-primary" type="button" onclick="qtcUpdateShipMethRates()" value="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_UPDATE_RATE"); ?>"
						class="button" />
					</td>
				</tr>
			</tbody>

		</table>
	</div> <!-- End table responsive idv-->
	<!-- plugin related things -->
	<input type="hidden" name="plugview" value="default" />
	<input type="hidden" name="shipMethId" value="" />
	<input type="hidden" name="plugtask" value="updateShipMethRate" />
	<input type="hidden" name="boxchecked" value="0" />
<!--	<input type="hidden" name="plugNextView" value="new" /> -->
</form>

<!-- For Error Display-->
<div class='row'>
	<div class="error alert alert-danger qtcError" style="display: none;">
		<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
		<i class="icon-cancel pull-right" style="align: right;"
			onclick="techjoomla.jQuery(this).parent().fadeOut();"> </i> <br />
		<hr />
		<div id="qtcErrorContentDiv"></div>
	</div>
</div>

