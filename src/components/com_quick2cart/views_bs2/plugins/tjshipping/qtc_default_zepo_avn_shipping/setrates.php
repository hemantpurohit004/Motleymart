<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.modal');

require_once(JPATH_SITE.'/plugins/tjshipping/qtc_default_zoneshipping/qtc_default_zoneshipping/qtczoneShipHelper.php');
$qtczoneShipHelper = new qtczoneShipHelper;
$comquick2cartHelper=new comquick2cartHelper;
$productHelper = new productHelper;
$zoneHelper = new zoneHelper;
$qtcshiphelper = new qtcshiphelper;

$taxHelper = new taxHelper;

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$extension_id = $jinput->get('extension_id');
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

function qtcAddShipMethRates()
{
	var SelectedZoneVal  = document.id('zone_id').value;


	if(SelectedZoneVal  == '')
	{
		var msg = "<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_INVALID_ZONE_SELECTION'); ?>"
		alert(msg);
		return false;
	}

	var extension_id = <?php echo $extension_id; ?>;
	var shipMethodId = <?php echo $methodId; ?>;

	values = techjoomla.jQuery('#qtcSetRateform').serialize();
	techjoomla.jQuery.ajax({
			//url: '?option=com_quick2cart&task=cartcheckout.qtc_autoSave&stepId='+stepId,
			url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&task=shipping.qtcHandleShipAjaxCall&plugview=setrates&extension_id=" +extension_id +"&methodId="+ shipMethodId + '&tmpl=component',
			type: 'POST',
			async:false,
			data:values,
			dataType: 'json',
			beforeSend: function() {},
			complete: function() {	},
			success: function(response)
			{

				// Get selected zone
				var SelectedZone = techjoomla.jQuery("#zone_id").children("option").filter(":selected").text() ;

				// Remove Error dive content
				techjoomla.jQuery('#qtcErrorContentDiv').html('');
				techjoomla.jQuery('.qtcError').fadeOut();

				var taxrule_id= 1;
				var rateId = response.rateId;
				var q="'";
				var editbtn = '<input type="button" value="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_EDIT"); ?>" class="btn btn-primary">';
				var editHref = 'index.php?option=com_quick2cart&view=taxprofileform&layout=setrule&id='+rateId+'&tmpl=component';
				var editLink = '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-450, y: window.innerHeight-250}}" href="'+editHref+'" class="modal qtc_modal">'+editbtn+'</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

				var delLink = '<input onclick="qtcDeleteShipRate('+
							rateId+',this);" class="btn btn-danger" type="button" value="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_DELETE"); ?>">';

				var showRangeTd = <?php echo ($shipMethDetail['shipping_type'] == 3 ? 1 : 0 ) ; 	?> ;
				var RangeTdContent = '';
				if (showRangeTd == 1)
				{
					 RangeTdContent = '';
				}
				else
				{
					 RangeTdContent = '<td >'  + response.rangeTd+ '</td> ';
				}
				var result='<tr id="qtcRateId_"' + rateId +'><td>'+SelectedZone+'</td>' + RangeTdContent +' <td>' +response.shipCostTd + '</td> <td>' +response.handleCostTd + '</td><td>' + editLink + delLink + '</td></tr>';
				techjoomla.jQuery('#tableBody').append(result);

				// intialize squeeze box again for edit button to work
				SqueezeBox.initialize({});
				SqueezeBox.assign($$('a.modal'),
				{
					parse: 'rel'
				});

				// Added by Deepali

				  techjoomla.jQuery('#qtc_shipping_list').find('input:text').each(function() {techjoomla.jQuery(this).val('');});
			},
			error: function(response) {	}
		});


}

function qtcDeleteShipRate(rateId, delBtn)
{
	var data = {
				rateId : rateId,
				plugtask : 'qtcDelshipMethRate'
		};

	var extension_id = <?php echo $extension_id; ?>;
	var shipMethodId = <?php echo $methodId; ?>;

	techjoomla.jQuery.ajax({
		type : "POST",
		url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&task=shipping.qtcHandleShipAjaxCall&plugview=setrates&extension_id=" +extension_id +"&methodId="+ shipMethodId + '&tmpl=component',
		data : data,
		dataType: 'json',
		beforeSend: function() {},
		success : function(response)
		{
			if (response.error == 0)
			{
				// No error
				//techjoomla.jQuery(delete_btn).parent().parent().fadeOut();
				techjoomla.jQuery(delBtn).closest('tr').remove();
			}
			else
			{
				techjoomla.jQuery('#qtcErrorContentDiv').html(response.errorMsg);
				techjoomla.jQuery('.qtcError').fadeIn();
			}
		}
	});
}
</script>

<form name='qtcSetRateform' id="qtcSetRateform"  method="post" >
	<legend id="" ><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_SET_RATEFOR_") ?>&nbsp; <?php echo !empty($shipMethDetail['name']) ? ucfirst($shipMethDetail['name']) : ''; ?>
		<small><?php //echo JText::_('QTC_BILLIN_DESC')?></small>
		<span class="pull-right" >
			<?php
			 $backlink = JRoute::_("index.php?option=com_quick2cart&view=shipping&layout=list&plugview=default&extension_id=" . $extension_id . "&Itemid=" . $itemid . "&methodId=" . $methodId . '');
			 ?>
			<button type="button" onClick="location.href='<?php echo $backlink; ?>'" title="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_SHIPTAXPROFILE_SETRATE_BACK_TITLE"); ?>" class="btn  btn-primary " >
				<i class="<?php echo QTC_ICON_BACK; ?>"></i>
				<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_SET_RATEFOR_BACK"); ?>
			</button>
		</span>
	</legend>

	<div class="alert alert-info">
		<p><?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SETRATES_HELP'); ?></p>
	</div>
	<div class="alert alert-warning">
		<i class="icon-info"></i> &nbsp;<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_COMPLETE_RANGE_HELP'); ?>
	</div>

	<!-- div id Added by Deepali -->
	 <div id="qtc_shipping_list">
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
					<?php //endif; ?>

					<th width="25%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_SHIP_COST"); ?></th>
					<th width="25%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_HANDLE_FEE"); ?></th>
					<th width=""></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php
						$default = 0;
						$options = array();
						$options[] = JHtml::_('select.option', "", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SEL_ZONE'));

						foreach ($shipFormData['zonelist'] as $zone)
						{
							$profileText = '';
							$profileText = $zone['name'] . '  [' . JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE') . ':' . $zone['storeName'] . ' ] ';
							$options[] = JHtml::_('select.option', $zone['id'], $profileText);
						}

						echo JHtml::_('select.genericlist',$options,"zone_id",'class=""  required="" aria-invalid="false" size="1" ','value','text',$default,'zone_id');
						?>
					</td>

					<?php
					if ($shipMethDetail['shipping_type'] !=3)
					{

						$showWeightUnite = ($shipMethDetail['shipping_type'] == 2) ? 1 : 0;
						$weightUniteSymbol  = $qtczoneShipHelper->getWeightUniteSymbol();
					?>
					<td>
						<div class="input-append curr_margin ">
							<input type="text" name="rangeFrom" size="" id="qtc_shipping_range_start" value="" class=" input-mini " placeholder="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_FROM_HINT"); ?>">
							<?php
							if ($showWeightUnite ==1 )
							{?>
								<span class="add-on "><?php echo $weightUniteSymbol; ?></span>
							<?php } ?>
						</div>


						<br/>
						<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_TO"); ?>
						<br/>

						<div class="input-append curr_margin ">
							<input type="text" name="rangeTo" size="" id="qtc_shipping_range_end" value="" class=" input-mini " placeholder="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_TO_HINT"); ?>">
							<?php
							if ($showWeightUnite ==1 )
							{?>
							<span class="add-on "><?php echo $weightUniteSymbol; ?></span>
							<?php } ?>
						</div>


					</td>
					<?php
					}
					?>
				<?php //endif; ?>

					<td>
						<?php
						// Show shipping cost fields
						echo $productHelper->getMultipleCurrFields($name = 'shipCost');
						?>
					</td>
					<td>
						<?php
						// Show shipping cost fields
						echo $productHelper->getMultipleCurrFields($name = 'handleCost');
						?>
					</td>
					<td>
						<input class="btn btn-primary" type="button" onclick="qtcAddShipMethRates()" value="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_ADD_RATE"); ?>"
						class="button" />
					</td>
				</tr>
			</tbody>

		</table>
	</div>
		<!-- plugin related things -->
		<input type="hidden" name="plugview" value="default" />
		<input type="hidden" name="shipMethId" value="" />
		<input type="hidden" name="plugtask" value="addShipMethRate" />
		<input type="hidden" name="boxchecked" value="0" />
	<!--	<input type="hidden" name="plugNextView" value="new" /> -->
</form>

<!-- For Error Display-->
<div class='row-fluid'>
	<div class="error alert alert-danger qtcError" style="display: none;">
		<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
		<i class="icon-cancel pull-right" style="align: right;"
			onclick="techjoomla.jQuery(this).parent().fadeOut();"> </i> <br />
		<hr />
		<div id="qtcErrorContentDiv"></div>
	</div>
</div>

<div class="">
	<!-- Show rates list -->
	<table class="adminlist table table-striped table-bordered">
		<thead>
			<tr>
				<th width="25%"><?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_ZONES'); ?> </th>

				<?php
				if ($shipMethDetail['shipping_type'] !=3)
				{
				?>
				<th width="10%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_RANGE"); ?></th>

				<?php
				}
				?>
				<th width="20%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_SHIP_COST"); ?></th>
				<th width="20%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_HANDLE_FEE"); ?></th>
				<th width="20%"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_ACTION"); ?></th>
			</tr>

		</thead>
		<tbody id="tableBody">
		<?php
		$i=1;

		if (!empty($shipFormData['ratelist']))
		{
			$rateList = $shipFormData['ratelist'];
			foreach ($rateList as $rate)
			{
				$qtcRateId = $rate['rateId'];
				?>
				<tr id="qtcRateId_<?php echo $rate['rateId'];?>">
					<td id="qtczoneTd_<?php echo $qtcRateId;?>" >
					<?php
						if (!empty($rate['zone_id']))
						{
							$zoneDetail =  $zoneHelper->getZoneDetail($rate['zone_id']);
							echo  $zoneDetail['name'] . ' <br/>[' . JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE') . ':' . $zoneDetail['title'] . ' ] ';

						}
					?>
					</td>

					<?php
					if ($shipMethDetail['shipping_type'] !=3)
					{
					?>
					<td id="qtcRangeTd_<?php echo $qtcRateId;?>" >
						<?php
						echo $rate['rangeFrom'];
						echo " " . JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_TO") . " " ;
						echo $rate['rangeTo'];
					?>
					</td>
					<?php
					}
					?>

					<td id="qtcShipCostTd_<?php echo $qtcRateId;?>" >
						<?php
						// Shipping cost
						$shipCost = array();

						foreach ($rate['rateCurrDetails'] as $rateDetail)
						{
							$shipCost[] =	number_format($rateDetail['shipCost'], 2) . '  ' . $rateDetail['currency'];
						}
							echo implode(', ',$shipCost);
						?>
					</td>

					<td id="qtcHandleCostTd_<?php echo $qtcRateId;?>" >
						<?php
						// Handling cost.
						$handleCost = array();

						foreach ($rate['rateCurrDetails'] as $rateDetail)
						{
							$handleCost[] =	number_format($rateDetail['handleCost'], 2) . '  ' . $rateDetail['currency'];
						}
						echo implode(', ',$handleCost);
						?>
					</td>

					<!-- Action -->
					<td id="qtcActionTd_<?php echo $qtcRateId;?>" >
						<?php
						$editRateLink = "index.php?option=com_quick2cart&view=shipping&layout=list&plugview=editrate&extension_id=" . $extension_id . "&Itemid=" . $itemid . "&methodId=" . $methodId . "&rateId=" . $rate['rateId'] . '&tmpl=component';
						?>
						<a rel="{handler:'iframe',size:{x: window.innerWidth-450, y: window.innerHeight-150}}"
						href="<?php echo JRoute::_($editRateLink); ?>" class="modal qtc_modal">
							<input type="button" value="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_EDIT"); ?>" 	class=" btn btn-primary">
						</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

						<input onclick="qtcDeleteShipRate(<?php echo $rate['rateId']; ?>, this);" class="btn btn-danger" type="button" value="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_DELETE"); ?>">
					</td>
				</tr>

				<?php
			}//end for

		}?>


		</tbody>

	</table>
</div>

