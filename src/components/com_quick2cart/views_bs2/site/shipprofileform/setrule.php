<?php
/**
 * @version     2.2
 * @package     com_quick2cart
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://techjoomla.com
 */
// no direct access
defined('_JEXEC') or die;
 JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.modal');

$jinput = JFactory::getApplication()->input;
?>
<script type="text/javascript">

function qtcLoadPlgMethods()
{
	var qtcShipPluginId = document.id('qtcShipPlugin').value;

	if(qtcShipPlugin == '' )
	{
		techjoomla.jQuery('#qtcErrorContentDiv').html("<?php echo JText::_('COM_QUICK2CART_S_SHIPPLUGIN_INVALID_SELECTION'); ?>");
		techjoomla.jQuery('.qtcError').fadeIn();
		return false;
	}
	var data = {
		qtcShipPluginId : qtcShipPluginId,
		store_id : '<?php echo $store_id; ?>',
	};

	techjoomla.jQuery.ajax({
			type : "POST",
			url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=shipprofileform&task=shipprofileform.qtcLoadPlgMethods&tmpl=component",
			data : data,
			dataType: "json",
			beforeSend: function()
			{
				// REMOVE ALL STATE OPTIONS
				techjoomla.jQuery('#qtc_shipMethod').find('option').remove().end();
				techjoomla.jQuery('.com_quick2cart_ajax_loading').show();
			},
			complete: function()
			{
				techjoomla.jQuery('.com_quick2cart_ajax_loading').hide();
			},
			success : function(response)
			{
				if (response.error != 1)
				{
					techjoomla.jQuery('#qtcShipMethContainer').html(response.shipMethList);
				}
				else
				{
					techjoomla.jQuery('#qtcErrorContentDiv').html(response.errorMessage);
					techjoomla.jQuery('.error').fadeIn();
				}

			}
		});

}
    function qtc_updateShipMethod()
    {
		var qtcShipPluginId = document.id('qtcShipPlugin').value;
		var qtc_shipMethodId = document.id('qtc_shipMethod').value;
		var qtc_shipProfileMethodId = <?php echo $jinput->get('shipmethId'); ?>;

		if(qtcShipPluginId == '' || qtc_shipMethodId == '')
		{
			techjoomla.jQuery('#qtcErrorContentDiv').html("<?php echo JText::_('COM_QUICK2CART_S_SHIPPLUGIN_INVALID_SELECTION'); ?>");
			techjoomla.jQuery('.qtcError').fadeIn();
			return false;
		}

		var data = {
			jform : {
				shipprofile_id : document.id('qtcShipProfileId').value,
				qtcShipPluginId : qtcShipPluginId,
				methodId : qtc_shipMethodId,
				qtc_shipProfileMethodId : qtc_shipProfileMethodId,
			}
		};

		var qtc_selectedShipPlugin = techjoomla.jQuery("#qtcShipPlugin").children("option").filter(":selected").text() ;
		var qtc_selectedShipMethod = techjoomla.jQuery("#qtc_shipMethod").children("option").filter(":selected").text() ;

		techjoomla.jQuery.ajax({
					type : "POST",
					url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=shipprofileform&task=shipprofileform.updateShipMethod&tmpl=component",
					data : data,
					dataType: "json",
					beforeSend: function()
					{
						techjoomla.jQuery('.qtcError').fadeOut();
					},
					complete: function()
					{
					},
					success : function(response)
					{

						if (response.error == 0)
						{
							// Remove Error dive content
							techjoomla.jQuery('#qtcErrorContentDiv').html('');
							techjoomla.jQuery('.qtcError').fadeOut();

							var shipProfileMethodId= response.shipProfileMethodId;
							var q="'";
							var editbtn = '<input type="button" value="<?php echo JText::_('COM_QUICK2CART_SHIPPROFIL_METH_EDIT'); ?>" class="btn btn-primary">';
							var editHref = 'index.php?option=com_quick2cart&view=shipprofileform&layout=setrule&id='+shipProfileMethodId+'&tmpl=component';
							var editLink = '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-450, y: window.innerHeight-250}}" href="'+editHref+'" class="modal qtc_modal">'+editbtn+'</a> &nbsp;';

							var delLink = '<input onclick="deleteShipProfileMethod('+
										shipProfileMethodId+',this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_PROFILERULE_DELETE'); ?>">';
							//alert(links);


							var result='<tr><td id="qtcPlugnameTd_'+shipProfileMethodId+'">'+qtc_selectedShipPlugin+'</td><td id="qtcShipMethTd_'+shipProfileMethodId+'">'+qtc_selectedShipMethod+'</td><td>' + editLink + delLink + '</td></tr>';

							//window.parent.techjoomla.jQuery('#qtcShipMethTableBody').append(result);
							//window.parent.techjoomla.jQuery('#qtcPlugnameTd_'+qtc_shipMethodId).html(qtc_selectedShipPlugin);
							//window.parent.techjoomla.jQuery('#qtcShipMethTd_'+qtc_shipMethodId).html(qtc_selectedShipMethod);
							//window.parent.SqueezeBox.close();
							window.parent.location.reload();

						}
						else
						{
							techjoomla.jQuery('#qtcErrorContentDiv').html(response.errorMessage);
							techjoomla.jQuery('.error').fadeIn();
						}

					}
				});

		return false;
	}


</script>

<div class=" <?php echo Q2C_WRAPPER_CLASS; ?>">

	<div class="row-fluid">

		<legend>
			<?php echo JText::_('COM_QUICK2CART_SHIPPROFILE_ADD_SHIPMEHODS'); ?>
			<small><?php echo JText::_('COM_QUICK2CART_SHIPPROFILE_TAXRATE_MAP_HELP'); ?></small>
		</legend>
		<!-- SHIPPROFILE ID-->
		<input type="hidden" name="qtcShipProfileId" id="qtcShipProfileId" value="<?php echo $this->qtcShipProfileId; ?>" />
		<!-- Map the tax rule aginst tax profile -->
		<table class="  table">
			<tr>
				<td><?php echo $this->shipPluglist; ?></td>
				<td>
					<span id="qtcShipMethContainer">
						<?php
							echo $this->response['shipMethList'];
						?>

					</span>
				<span class="com_quick2cart_ajax_loading" style="display:none;">
					<img class="" src="<?php echo JUri::root() ?>components/com_quick2cart/assets/images/loadin16x16.gif" height="15" width="15">
				</span>

				</td>
				<td valign="top">
					<input type="button" id="qtcAddShipMeth"
					value="<?php echo JText::_('COM_QUICK2CART_SHIPPLUGIN_UPDATE_SHIP_METH'); ?>"
					class="btn btn-success" onClick="qtc_updateShipMethod()" />
				</td>
			</tr>

			<tfoot>
				<!-- For Error Display-->
				<tr>
					<td id="" colspan="3">
						<div class="error alert alert-error qtcError" style="display: none;">
							<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
							<i class="<?php echo QTC_ICON_REMOVE; ?> pull-right" style="align: right;"
								onclick="techjoomla.jQuery(this).parent().fadeOut();"> </i> <br />
							<hr />
							<div id="qtcErrorContentDiv"></div>
						</div>
					</td>
				</tr>
			</tfoot>

		</table>

	</div>

</div> <!--com_quick2cart_wrapper -->
