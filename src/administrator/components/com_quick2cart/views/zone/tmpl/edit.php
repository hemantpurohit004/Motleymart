<?php
/**
 * @package    Quick2Cart
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.modal');

?>
<script type="text/javascript">
	var root_url="<?php echo JUri::root(); ?>";
   /* js = jQuery.noConflict();
    js(document).ready(function() {

    });*/

    Joomla.submitbutton = function(task)
    {
        if (task == 'zone.cancel') {
            Joomla.submitform(task, document.getElementById('zone-form'));
        }
        else {

            if (task != 'zone.cancel' && document.formvalidator.isValid(document.id('zone-form'))) {

                Joomla.submitform(task, document.getElementById('zone-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
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
					url : "<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zone.getStateSelectList",
					data : data,
					success : function(response)
					{
						techjoomla.jQuery('#qtcStateContainer').html(response);
					}
				});
}
/** This function adds in zone
 */
function qtcAddZoneRule()
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
			zone_id : document.id('zone_id').value,
			country_id : document.id('qtc_ZoneCountry').value,
			region_id : document.id('jform_qtc_state_id').value,
		}
		};

		// Get country and region name
		var country = jQuery("#qtc_ZoneCountry").children("option").filter(":selected").text() ;
		var region = jQuery("#jform_qtc_state_id").children("option").filter(":selected").text() ;



		techjoomla.jQuery.ajax({
					type : "POST",
					url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zone.addZoneRule",
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
							var editLink = '<a rel="{handler:'+q+'iframe'+q+',size:{x: window.innerWidth-450, y: window.innerHeight-150},onClose: function(){window.parent.document.location.reload(true);}}" '
										+' href="index.php?option=com_quick2cart&view=zone&layout=setrule&id='+
										zoneRuleId+'&tmpl=component" 	class="modal qtc_modal"><input type="button" value="<?php echo JText::_('COM_QUICK2CART_ZONERULE_EDIT'); ?>" class=" btn btn-primary"></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
							var delLink = '<input onclick="qtcDeleteZoneRule('+
										zoneRuleId+',this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_ZONERULE_DELETE'); ?>">';

							var result='<tr><td> </td><td id="qtc_country_'+zoneRuleId+'">'+country+'</td><td id="qtc_region_'+zoneRuleId+'">'+region+'</td><td>'+ editLink + delLink +'</td></tr>';
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

/** Delete the rule from zone.
	@param field_name string select element name.
	@param field_id string select element id.

*/
function qtcDeleteZoneRule(ruleId,delBtn)
{
	var data = {
		jform : {
			zonerule_id : ruleId,
		}
	};

	techjoomla.jQuery.ajax({
		type : "POST",
		url : "<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zone.deleteZoneRule",
		data : data,
		success : function(response)
		{
			if (response.error!=1)
			{
				//techjoomla.jQuery(delete_btn).parent().parent().fadeOut();
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

<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">

	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">
					<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="zone-form" class="form-validate">

						<legend>
							<?php echo JText::_('COM_QUICK2CART_ZONE_LEGEND'); ?>
						</legend>
						<!--
						<div class="control-group">
							<div class="control-label"><?php //echo $this->form->getLabel('zone_id'); ?></div>
							<div class="controls"><?php //echo $this->form->getInput('zone_id'); ?></div>
						</div>  -->


						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
						</div>

						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('store_id'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('store_id'); ?></div>
						</div>
						<div class="control-group">
							<div class="alert alert-info"><?php echo JText::_('COM_QUICK2CART_ZONE_HELP_TEXT'); ?></div>
						</div>
						<!-- For Error Display-->
						<div class="control-group">
							<div class="error alert alert-danger qtcError" style="display: none;">
								<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
								<i class="icon-cancel pull-right" style="align: right;"
									onclick="jQuery(this).parent().fadeOut();"> </i> <br />
								<hr />
								<div id="qtczoneruleError"></div>
							</div>
					</div>


				<input type="hidden" name="id" id="zone_id" value="<?php echo $this->item->get('id')?>" />
				<input type="hidden" name="task" id="" value="" />
				<?php echo JHtml::_('form.token'); ?>
				</form>
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
						<table class="adminlist table ">
							<tbody>
								<tr>
									<td>
										<?php
										$default ="";
										$options = array();
										$options[] = JHtml::_('select.option', "", JText::_('COM_QUICK2CART_ZONE_SELECT_COUNTRY'));

										foreach($this->country as $country)
										{
											$options[] = JHtml::_('select.option', $country['id'], $country['country']);
										}
										echo $this->dropdown = JHtml::_('select.genericlist',$options,'qtc_ZoneCountry','class=""  required="" aria-invalid="false" size="1"  autocomplete="off" onchange=\'qtc_generateState("jform[qtc_state_id]","jform_qtc_state_id")\' data-chosen="qtc" ','value','text',$default,'qtc_ZoneCountry');

										?>
									</td>
									<td>
										<span id="qtcStateContainer"></span>
									</td>
									<td>
										<span >
											<input type="button" id="qtcAddZoneRules"
												value="<?php echo JText::_('COM_QUICK2CART_ZONE_ADD_COUNTRY_OR_STATE'); ?>"
												class="btn btn-success" onClick="qtcAddZoneRule()"/>
										</span>
									</td>
								</tr>

							</tbody>
						</table>

						<table class="adminlist table table-striped table-bordered">
							<thead>
								<tr>
									<th><?php echo JText::_('COM_QUICK2CART_ZONERULE_NUM'); ?>

									</td>
									<th><?php echo JText::_('COM_QUICK2CART_ZONERULE_COUNTRY'); ?>

									</td>
									<th><?php echo JText::_('COM_QUICK2CART_ZONERULE_REGION'); ?>

									</td>
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
											<td><?php echo $i++; ?>
											</td>
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
												<a rel="{handler:'iframe',size:{x: window.innerWidth-450, y: window.innerHeight-150},onClose: function(){window.parent.document.location.reload(true);}}"
												href="index.php?option=com_quick2cart&view=zone&layout=setrule&id=<?php echo $rule->id;?>&tmpl=component" class="modal qtc_modal">
													<input type="button" value="<?php echo JText::_('COM_QUICK2CART_ZONERULE_EDIT'); ?>" 	class=" btn btn-primary">
												</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

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

            </div>
        </div>
	</div>
</div> <!--qyc_admin_zone -->
