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

$app = JFactory::getApplication();
$shipProfileStoreId = !empty($this->storeDetails['id']) ? $this->storeDetails['id'] : 0;
$comquick2cartHelper = new comquick2cartHelper;
$store_cp_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=cp');

$actionViewName = !empty($actionViewName) ? $actionViewName :'shipprofileform';
$actionControllerName = !empty($actionControllerName) ? $actionControllerName :'shipprofileform';
$formName = !empty($actionViewName) ? $actionViewName :'shipprofileform';
?>
<script type="text/javascript">

	function deleteShipProfileMethod(methodId,delBtn)
	{
		var data = {
			jform : {
				shipMethodId : methodId,
			}
		};

		techjoomla.jQuery.ajax({
			type : "POST",
			url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=<?php echo $actionViewName; ?>&task=<?php echo $actionControllerName; ?>.deleteShipProfileMethod",
			data : data,
			success : function(response)
			{
				if (response.error!=1)
				{
					//techjoomla.jQuery(delete_btn).parent().parent().fadeOut();
					techjoomla.jQuery(delBtn).closest('tr').remove();
				}
				else
				{
					techjoomla.jQuery('#qtczoneruleError').html(response.errorMessage);
					techjoomla.jQuery('.error').fadeIn();
				}
			}
		});
	}

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
			store_id : <?php echo $shipProfileStoreId; ?>,
		};

		techjoomla.jQuery.ajax({
				type : "POST",
				url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=<?php echo $actionViewName; ?>&task=<?php echo $actionControllerName; ?>.qtcLoadPlgMethods",
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

    function qtc_addShipMethod()
    {
		var qtcShipPluginId = document.id('qtcShipPlugin').value;
		var qtc_shipMethodId = document.id('qtc_shipMethod').value;

		if(qtcShipPluginId == '' || qtc_shipMethodId == '')
		{
			techjoomla.jQuery('#qtcErrorContentDiv').html("<?php echo JText::_('COM_QUICK2CART_S_SHIPPLUGIN_INVALID_SELECTION'); ?>");
			techjoomla.jQuery('.qtcError').fadeIn();
			return false;
		}

		var qtcShipprofile_id = document.id('jform_id').value;
		var data = {
			jform : {
				shipprofile_id : qtcShipprofile_id,
				qtcShipPluginId : qtcShipPluginId,
				methodId : qtc_shipMethodId,
			}
		};

		var qtc_selectedShipPlugin = techjoomla.jQuery("#qtcShipPlugin").children("option").filter(":selected").text() ;
		var qtc_selectedShipMethod = techjoomla.jQuery("#qtc_shipMethod").children("option").filter(":selected").text() ;

		techjoomla.jQuery.ajax({
					type : "POST",
					url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=<?php echo $actionViewName; ?>&task=<?php echo $actionControllerName; ?>.addShipMethod",
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
							var editHref = 'index.php?option=com_quick2cart&view=<?php echo $actionViewName; ?>&layout=setrule&id='+qtcShipprofile_id+'&shipmethId='+shipProfileMethodId+'&tmpl=component';

							var editLink = '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-450, y: window.innerHeight-250}}" href="'+editHref+'" class="modal qtc_modal">'+editbtn+'</a> &nbsp;';

							var delLink = '<input onclick="deleteShipProfileMethod('+
										shipProfileMethodId+',this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_PROFILERULE_DELETE'); ?>">';

							var result='<tr><td id="qtcPlugnameTd_'+shipProfileMethodId+'">'+qtc_selectedShipPlugin+'</td><td id="qtcShipMethTd_'+shipProfileMethodId+'">'+qtc_selectedShipMethod+'</td><td>' + editLink + delLink + '</td></tr>';
							techjoomla.jQuery('#qtcShipMethTableBody').append(result);

							// intialize squeeze box again for edit button to work
							SqueezeBox.initialize({});
							SqueezeBox.assign($$('a.modal'),
							{
								parse: 'rel'
							});
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

	<div class="row-fluid">
		<div class="form-horizontal">
			<fieldset class="adminform">
				<?php
				if (!$app->isAdmin())
				{
				?>
					<legend><?php echo JText::_('COM_QUICK2CART_SHIPPROFILE'); ?></legend>
				<?php
				}
				?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
				</div>

				<div class="control-group" style="display:none;">
					<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
				</div>

				<?php
				if (empty($this->item->id))
				{
					?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('store_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('store_id'); ?>
							<span class="help-block">
								<i class="icon-hand-right"></i>
								<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_CAN_NOT_CAHGNE_STORE_MSG'); ?></span>
						</div>
					</div>
					<?php
				}
				else
				{
					?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('store_id'); ?></div>
						<div class="controls"><?php echo ucfirst($this->storeDetails['title']); ?></div>
					</div>
					<?php
				} ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
				</div>
				<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
				<div class="alert alert-info">
					<?php echo JText::_('COM_QUICK2CART_SHIPPROFILES_SAVE_HELP_TEXT');?>
				</div>
				<input type="hidden" name="jform[ordering]" value="<?php echo !empty($this->item->ordering)	? $this->item->ordering : ''; ?>" />
				<input type="hidden" name="option" value="com_quick2cart" />
				<input type="hidden" name="task" value="<?php echo $formName; ?>.save" />
				<?php echo JHtml::_('form.token'); ?>

				<input type="hidden" name="id" id="id" value="<?php echo $this->item->get('id')?>" />
				<input type="hidden" name="jform[id]" id="jform_shippofile_id" value="<?php echo $this->item->id; ?>" />

			</fieldset>
		</div>
			<?php
			if (!empty($this->item->id))
			{
			?>
				<legend>
					<b><?php echo JText::_('COM_QUICK2CART_SHIPPROFILE_ADD_SHIPMEHODS'); ?></b>
						<small><?php echo JText::_('COM_QUICK2CART_SHIPPROFILE_TAXRATE_MAP_HELP'); ?></small>
				</legend>

				<!-- Map the tax rule aginst tax profile -->
				<div class="row-fluid">
					<div class="span4">
						<?php echo $this->shipPluglist; ?>
					</div>
					<div class="span4">
						<span id="qtcShipMethContainer">
							<?php
							$default ="";
							$options = array();
							$options[] = JHtml::_('select.option', "", JText::_("COM_QUICK2CART_SHIPPLUGIN_SELECT_SHIP_METH"));

							echo $this->dropdown = JHtml::_('select.genericlist',$options,'qtc_shipMethod','class=""  aria-invalid="false" data-chosen="qtc" size="1"  autocomplete="off" ','value','text',$default,'qtc_shipMethod');
							?>
						</span>
						<span class="com_quick2cart_ajax_loading" style="display:none;">
							<img class="" src="<?php echo JUri::root() ?>components/com_quick2cart/assets/images/loadin16x16.gif" height="15" width="15">
						</span>
					</div>
					<div class="span4">
						<input type="button" id="qtcAddShipMeth"
							value="<?php echo JText::_('COM_QUICK2CART_SHIPPLUGIN_ADD_SHIP_METH'); ?>"
							class="btn btn-success" onClick="qtc_addShipMethod()" />
					</div>
				</div>
				<!-- For Error Display-->
				<div class="row-fluid">
					<div class="error alert alert-danger qtcError" style="display: none;">
						<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
						<i class="icon-cancel pull-right" style="align: right;"
							onclick="techjoomla.jQuery(this).parent().fadeOut();"> </i> <br />
						<hr />
						<div id="qtcErrorContentDiv"></div>
					</div>
				</div>
				<!-- Show the taxprofile rules -->

				<table class="  table table-striped table-bordered">
				<thead>
					<tr>
						<th width="40%"><?php echo JText::_('COM_QUICK2CART_SHIPPROFILE_PLUGIN_NAME'); ?> </th>
						<th width="35%"><?php echo JText::_('COM_QUICK2CART_SHIPPROFILE_PLUGIN_METHOD'); ?></th>
						<th width="20%"><?php echo JText::_('COM_QUICK2CART_SHIPPROFILE_ACTION'); ?></th>
					</tr>
				</thead>
				<tbody id="qtcShipMethTableBody">
					<?php
				$i=1;

				foreach ($this->shipMethods as $meths)
				{
					?>
					<tr>
						<td id="qtcPlugnameTd_<?php echo $meths->id; ?>" >
							<?php echo $meths->plugName;?>
						</td>
						<td id="qtcShipMethTd_<?php echo $meths->methodId; ?>"><?php
							// Get shipping method description
							$import = JPluginHelper::importPlugin('tjshipping', $meths->client);
							$dispatcher = JDispatcher::getInstance();
							$result = $dispatcher->trigger('TjShip_getShipMethodDetail', array($meths->methodId));
							$shipMethDetail = array();

							if (!empty($result))
							{
								$shipMethDetail = $result[0];
							}


							echo !empty($shipMethDetail['name']) ? ucfirst($shipMethDetail['name']) : '';
						?>
						</td>
						<td>
							<a rel="{handler:'iframe',size:{x: window.innerWidth-450, y: window.innerHeight-150}}"
							href="index.php?option=com_quick2cart&view=<?php echo $actionViewName; ?>&layout=setrule&id=<?php echo $meths->shipprofile_id;?>&shipmethId=<?php echo $meths->id; ?>&tmpl=component" class="modal qtc_modal">
								<input type="button" value="<?php echo JText::_('COM_QUICK2CART_SHIPPROFIL_METH_EDIT'); ?>" 	class=" btn btn-primary">
							</a>

							<input onclick="deleteShipProfileMethod(<?php echo $meths->id;?>,this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_SHIPPROFIL_METH_DELETE'); ?>">
						</td>
					</tr>

					<?php
				}//end for?>
				</tbody>

			</table>

			<?php
			}
			?>

	</div> <!-- ROW-FLUID END-->

		<?php
		$isadmin = $app->isAdmin();
		if (!$isadmin)
		{
			?>
		<div class="form-horizontal">
			<div class="form-actions">
				<button type="button" class="btn btn-success validate" title="<?php echo JText::_('COM_QUICK2CART_SAVE_ITEM'); ?>" onclick="qtcsubmitAction('save');">
				<?php echo JText::_('COM_QUICK2CART_SAVE_ITEM'); ?>
			</button>
				<?php if ($this->item->id): ?>
					<button type="button" class="btn  validate" title="<?php echo JText::_('COM_QUICK2CART_COMMON_SAVE_AND_CLOSE'); ?>" onclick="qtcsubmitAction('saveAndClose');">
					<?php echo JText::_('COM_QUICK2CART_COMMON_SAVE_AND_CLOSE'); ?>
					</button>
				<?php endif; ?>
				<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&task=' . $formName. '.cancel&id='.$this->item->id); ?>&Itemid=<?php echo $store_cp_itemid; ?>" class="btn btn-inverse" title="<?php echo JText::_('COM_QUICK2CART_CANCEL_ITEM'); ?>">
				<?php echo JText::_('COM_QUICK2CART_CANCEL_ITEM'); ?>
				</a>
			</div>
		</div>
		<?php
		} ?>


