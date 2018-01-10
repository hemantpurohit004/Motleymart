<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal');
?>

<script type="text/javascript">
	var root_url="<?php echo JUri::root(); ?>";

	function qtcsubmitAction(action)
	{
		var valid =document.formvalidator.isValid(document.id('zoneForm'));
		if(valid == false)
		{
			alert("<?php echo $this->escape(JText::_('COM_QUICK2CART_ZONEFORM_FILL_REQUIRED_FIELDS')); ?>");
			return false;
		}
		var form = document.zoneForm;

		switch(action)
		{
			case 'save': form.task.value='zoneform.save';
			break

			case 'saveAndClose':
			form.task.value='zoneform.saveAndClose';
			break
		}

		form.submit();

		return;
	}


	/** Generate state select box
		@param field_name string select element name.
		@param field_id string select element id.
		@param country_value string selected country value.
		@param default_option string default option to set.

	*/
	/*function qtc_generateState(field_name, field_id)
	{
		var countryId = 'qtc_ZoneCountry';
		var default_option=0;
		var country_value=techjoomla.jQuery('#'+countryId).val();

		var data = {
			jform : {
				country_id : country_value,
				default_option : default_option,
				field_name : field_name,
				field_id : field_id
			}
		};

		techjoomla.jQuery.ajax({
					type : "POST",
					url : "<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zoneform.getStateSelectList&tmpl=component",
					data : data,
					success : function(response)
					{
						techjoomla.jQuery('#qtcStateContainer').html(response);
					}
				});
	}
*/

	/** Generate state select box
		@param field_name string select element name.
		@param field_id string select element id.
		@param country_value string selected country value.
		@param default_option string default option to set.

	*/

	function qtc_generateState(field_name, field_id)
	{
		var countryId = 'qtc_ZoneCountry';
		var default_option=0;
		var country_value=techjoomla.jQuery('#'+countryId).val();

		var data = {
			jform : {
				country_id : country_value,
				default_option : default_option,
				field_name : field_name,
				field_id : field_id
			}
		};

		techjoomla.jQuery.ajax({
			type : "POST",
			url : "<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zoneform.getStateSelectList&tmpl=component",
			data : data,
			success : function(response)
			{
				techjoomla.jQuery('#qtcStateContainer').html(response);
			}
		});
	}

	/** This function adds in zone
	 */
	function qtcAddZoneRule(zone_id)
	{
		/* Clear old msg */
		techjoomla.jQuery('#qtczoneruleError').html("");
		techjoomla.jQuery('.qtcError').fadeOut();

		var qtc_ZoneCountry = document.id('qtc_ZoneCountry').value;
		if (qtc_ZoneCountry == '')
		{
			techjoomla.jQuery('#qtczoneruleError').html("<?php echo JText::_('COM_QUICK2CART_ZONE_INVALID_COUNTRY_SEL', true); ?>");
			techjoomla.jQuery('.qtcError').fadeIn();
			return false;
		}

		var data = {
			jform : {
				zone_id : zone_id,
				country_id : document.id('qtc_ZoneCountry').value,
				region_id : document.id('jform_qtc_state_id').value,
			}
		};

		// Get country and region name
		var country = techjoomla.jQuery("#qtc_ZoneCountry").children("option").filter(":selected").text() ;
		var region = techjoomla.jQuery("#jform_qtc_state_id").children("option").filter(":selected").text() ;
		techjoomla.jQuery.ajax({
			type : "POST",
			url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zoneform.addZoneRule&tmpl=component",
			data : data,
			dataType: "json",
			success : function(response)
			{
				// If not Error
				if (response.error != 1)
				{
					// Remove Error dive content
					techjoomla.jQuery('#qtczoneruleError').html('');
					techjoomla.jQuery('.qtcError').fadeOut();

					var zoneRuleId = response.zonerule_id;
					var q = "'";
					var url = "index.php?option=com_quick2cart&view=zoneform&layout=setrule&id="+zone_id+"&zonerule_id="+zoneRuleId+"&tmpl=component";


					var editLink = '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-350, y: window.innerHeight-150},onClose: function(){window.parent.document.location.reload(true);}}" '
								+' href="'+url+'"  class="modal qtc_modal"><input type="button" value="<?php echo JText::_('COM_QUICK2CART_ZONERULE_EDIT'); ?>" class=" btn btn-primary"></a> &nbsp;&nbsp;&nbsp;';
					var delLink = '<input onclick="qtcDeleteZoneRule('+
								zoneRuleId+',this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_ZONERULE_DELETE'); ?>">';

					var result=' <tr><td id="qtc_country_'+zoneRuleId+'">'+country+'</td><td id="qtc_region_'+zoneRuleId+'">'+region+'</td><td>'+ editLink + delLink +'</td></tr>';
					techjoomla.jQuery('#qtcTableBody').append(result);

					// intialize squeeze box again for edit button to work
					SqueezeBox.initialize({});
					SqueezeBox.assign($$('a.modal'),
					{
						parse: 'rel'
					});
				}
				else
				{
					techjoomla.jQuery('#qtczoneruleError').html(response.errorMessage);
					techjoomla.jQuery('.qtcError').fadeIn();
				}
			}
		});

		return false;
	}

	function qtcDeleteZoneRule(ruleId,delBtn)
	{
		var data = {
			jform : {
				zonerule_id : ruleId,
			}
		};

		techjoomla.jQuery.ajax({
			type : "POST",
			url : "<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zoneform.deleteZoneRule&tmpl=component",
			data : data,
			success : function(response)
			{
				if (response.error!=1)
				{
					//techjoomla.techjoomla.jQuery(delete_btn).parent().parent().fadeOut();
					techjoomla.jQuery(delBtn).closest('tr').fadeOut();
				}
				else
				{
					techjoomla.jQuery('#qtczoneruleError').html(response.errorMessage);
					techjoomla.jQuery('.error').fadeIn();
				}
			}
		});
	}

</script>

<div class="qyc_admin_zones <?php echo Q2C_WRAPPER_CLASS; ?>  container-fluid">

	<?php
	// Add store toolbar
	$helperobj=new comquick2cartHelper;
	$active = 'zones';
	$order_currency=$helperobj->getCurrencySession();
	$view=$helperobj->getViewpath('vendor','toolbar');
	ob_start();
		include $view;
		$html = ob_get_contents();
	ob_end_clean();
	echo $html;
	?>
	<div class="row">
		<div class="form-horizontal">
			<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="zoneForm" id="zoneForm" class="form-validate">
			<div style="clear:both;"></div>
				<fieldset class="adminform">
					<legend>
						<?php echo JText::_('COM_QUICK2CART_ZONE_LEGEND'); ?>
					</legend>
					<!--
					<div class="form-group">
						<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php //echo $this->form->getLabel('zone_id'); ?></div>
						<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12"><?php //echo $this->form->getInput('zone_id'); ?></div>
					</div>  -->


					<div class="form-group">
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo $this->form->getLabel('name'); ?></div>
						<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12"><?php echo $this->form->getInput('name'); ?></div>
					</div>

					<div class="form-group">
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<div class="form-group">
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo $this->form->getLabel('store_id'); ?></div>
						<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12"><?php echo $this->form->getInput('store_id'); ?></div>
					</div>
					<div class="form-group">
						<div class="alert alert-info"><?php echo JText::_('COM_QUICK2CART_ZONE_HELP_TEXT'); ?></div>
					</div>
					<!-- For Error Display-->
					<div class="form-group">
						<div class="error alert alert-danger qtcError" style="display: none;">
							<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
							<i class="<?php echo QTC_ICON_REMOVE; ?> pull-right" style="align: right;"
								onclick="jQuery(this).parent().fadeOut();"> </i> <br />
							<hr />
							<div id="qtczoneruleError"></div>
						</div>
					</div>
				</fieldset>


				<fieldset>
					<?php
					if (!empty($this->item->id))
					{
					?>
					<div class=" form-horizontal">
						<legend>
							<?php echo JText::_('COM_QUICK2CART_ZONE_COUNTRIES_AND_REGIONS'); ?>
						</legend>
						<div class="row">
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
								<?php
								$default ="";
								$options = array();
								$options[] = JHtml::_('select.option', "", JText::_('COM_QUICK2CART_ZONE_SELECT_COUNTRY'));

								foreach ($this->country as $country)
								{
									$options[] = JHtml::_('select.option', $country['id'], $country['country']);
								}
								echo $this->dropdown = JHtml::_('select.genericlist',$options,'qtc_ZoneCountry','class="col-lg-12 col-md-12 col-sm-12 col-xs-12"  aria-invalid="false"   autocomplete="off" onchange=\'qtc_generateState("jform[qtc_state_id]","jform_qtc_state_id")\' ','value','text',$default,'qtc_ZoneCountry');

								?>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
								<span id="qtcStateContainer"></span>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
								<span >
									<input type="button" id="qtcAddZoneRules"
										value="<?php echo JText::_('COM_QUICK2CART_ZONE_ADD_COUNTRY_OR_STATE'); ?>"
										class="btn btn-success" onClick="qtcAddZoneRule(<?php echo $this->item->id; ?>)"/>
								</span>
							</div>

						</div>

						<div class="clearfix">&nbsp;</div>
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th><?php echo JText::_('COM_QUICK2CART_ZONERULE_COUNTRY'); ?></th>
									<th><?php echo JText::_('COM_QUICK2CART_ZONERULE_REGION'); ?></th>
									<th></th>
								</tr>
							</thead>
							<tbody id="qtcTableBody">
								<?php
								$i=1;
								if (!empty($this->geozonerules))
								{
									foreach ($this->geozonerules as $rule)
									{ ?>
										<tr>
											<td id="qtc_country_<?php echo $rule->id; ?>" ><?php echo $rule->country; ?>
											</td>
											<td id="qtc_region_<?php echo $rule->id; ?>"><?php

											if (empty($rule->region))
											{
												echo JText::_('COM_QUICK2CART_ZONERULE_ALL_REGION');
											}
											else
											{
												echo $rule->region;
											}

											?>
											</td>
											<td>
												<a rel="{handler:'iframe',size:{x: window.innerWidth-350, y: window.innerHeight-150},onClose: function(){window.parent.document.location.reload(true);}}"
												href="index.php?option=com_quick2cart&view=zoneform&layout=setrule&id=<?php echo $this->item->id; ?>&zonerule_id=<?php echo $rule->id;?>&tmpl=component" class="modal qtc_modal">
													<input type="button" value="<?php echo JText::_('COM_QUICK2CART_ZONERULE_EDIT'); ?>" 	class=" btn btn-primary">
												</a> &nbsp;&nbsp;&nbsp;

												<input onclick="qtcDeleteZoneRule(<?php echo $rule->id;?>,this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_ZONERULE_DELETE'); ?>">
											</td>
										</tr>

										<?php }//end for?>

								<?php
								}
								?>
							</tbody>


						</table>
					</div>
					<?php
					}
					?>
					</fieldset>

			<!-- Action part -->
			<div class=" ">

				<button type="button" class="btn btn-success validate" title="<?php echo JText::_('COM_QUICK2CART_SAVE_ITEM'); ?>" onclick="qtcsubmitAction('save');">
					<?php echo JText::_('COM_QUICK2CART_SAVE_ITEM'); ?>
				</button>

				<?php if(!empty($this->item) && $this->item->get('id') ):?>
					<button type="button" class="btn btn-default validate" title="<?php echo JText::_('COM_QUICK2CART_COMMON_SAVE_AND_CLOSE'); ?>" onclick="qtcsubmitAction('saveAndClose');">
						<?php echo JText::_('COM_QUICK2CART_COMMON_SAVE_AND_CLOSE'); ?>
					</button>
				<?php endif;?>

				 <a href="<?php echo JRoute::_('index.php?option=com_quick2cart&task=zoneform.cancel&id='.$this->item->id); ?>" class="btn btn-default" title="<?php echo JText::_('COM_QUICK2CART_CANCEL_ITEM'); ?>">
					<?php echo JText::_('COM_QUICK2CART_CANCEL_ITEM'); ?>
				 </a>

				<input type="hidden" name="jform[id]" id="zone_id" value="<?php echo $this->item->get('id')?>" />
				<input type="hidden" name="option" value="com_quick2cart" />
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>

		</div>
	</div>
</div>
