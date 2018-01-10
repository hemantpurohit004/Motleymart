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
$app = JFactory::getApplication();
$previousId = (int) $app->getUserState('com_quick2cart.edit.zone.id');
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_quick2cart/css/quick2cart.css');
?>
<script type="text/javascript">

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
				alert("<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>");
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
						url : "<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zoneform.getStateSelectList&tmpl=component",
						data : data,
						success : function(response)
						{
							techjoomla.jQuery('#qtcStateContainer').html(response);
						}
					});

			//return false;

		/*
		var countryId = 'qtc_ZoneCountry';
		var country=techjoomla.jQuery('#'+countryId).val();
		if (country==undefined)
		{
			return (false);
		}
		techjoomla.jQuery.ajax({
			url:root_url+ '?option=com_quick2cart&controller=cartcheckout&task=loadState&tmpl=component&country='+country+'&tmpl=component&format=raw',
			type: 'GET',
			dataType: 'json',
			success: function(data)
			{
				//techjoomla.jQuery('#qtc_stateWapper').html(data);
				qtc_generateoption(countryId,data);
			}
		});*/
	}
	/** This function adds in zone
	 */
	function qtcUpdateZoneRule()
	{
		var data = {
			jform : {
				zonerule_id : document.id('zonerule_id').value,
				country_id : document.id('qtc_ZoneCountry').value,
				region_id : document.id('jform_qtc_state_id').value,
			}
			};

			// Get country and region name
			var country = jQuery("#qtc_ZoneCountry").children("option").filter(":selected").text() ;
			var region = jQuery("#jform_qtc_state_id").children("option").filter(":selected").text() ;

			techjoomla.jQuery.ajax({
				type : "POST",
				url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zoneform.updateZoneRule&tmpl=component",
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

						var zoneRuleId= response.zonerule_id;
						window.parent.jQuery('#country_'+zoneRuleId).html(country);
						window.parent.jQuery('#region_'+zoneRuleId).html(region);


						/* intialize squeeze box again for edit button to work */
						/*window.parent.SqueezeBox.initialize({});
						window.parent.SqueezeBox.assign($$('a.modal'), {
						parse: 'rel'
						});
						window.parent.SqueezeBox.close();*/
						window.parent.location.reload();
					/*	location = ''; */

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
				url : "<?php echo JUri::base();?>index.php?option=com_quick2cart&task=zoneform.deleteZoneRule&tmpl=component",
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
<div class = "<?php echo Q2C_WRAPPER_CLASS; ?>">
	<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $previousId ); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="zone-form" class="form-validate">


		<div class="row-fluid">
			<legend>
				<?php echo JText::_('COM_QUICK2CART_ZONE_COUNTRIES_AND_ZONES'); ?>
			</legend>

			<div class="span12">
				<div class="span4">
					<?php
					$default = $this->ruleDetail->country_id;
					$options = array();
					$options[] = JHtml::_('select.option', "", JText::_('COM_QUICK2CART_ZONE_SELECT_COUNTRY'));

					foreach($this->country as $country)
					{
						$options[] = JHtml::_('select.option', $country['id'], $country['country']);
					}
					echo $this->dropdown = JHtml::_('select.genericlist',$options,'qtc_ZoneCountry','class=""  required="" aria-invalid="false"   autocomplete="off" onchange=\'qtc_generateState("jform[qtc_state_id]","jform_qtc_state_id")\' ','value','text',$default,'qtc_ZoneCountry');
					?>
				</div>
				<div class="span4">
					<span id="qtcStateContainer">
						<?php

						$options = array();
						$options[] = JHtml::_('select.option', 0,JTEXT::_('COM_QUICK2CART_ZONE_ALL_STATES'));

						if ($this->getRegionList)
						{
							$default_region =  $this->ruleDetail->region_id;

							foreach ($this->getRegionList as $state)
							{
								// This is only to generate the <option> tag inside select tag da i have told n times
								$options[] = JHtml::_('select.option', $state['id'],$state['region']);
							}

							// now we must generate the select list and echo that
							 echo $stateList = JHtml::_('select.genericlist', $options, 'jform[qtc_state_id]', '  autocomplete="off"', 'value', 'text',$default_region,'jform_qtc_state_id');
						}

						?>
					</span>
				</div>
				<div class="span4">
					<span >
						<input type="button" id="qtcAddZoneRules"
							value="<?php echo JText::_('COM_QUICK2CART_ZONE_UPDATE_COUNTRY_OR_STATE'); ?>"
							class="btn btn-success" onClick="qtcUpdateZoneRule(<?php echo $this->rule_id; ?>)"/>
					</span>
				</div>

			</div>
			<!-- For Error Display-->
			<div class="control-group">
				<div class="error alert alert-error qtcError" style="display: none;">
					<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
					<i class="icon-cancel pull-right" style="align: right;"
						onclick="jQuery(this).parent().fadeOut();"> </i> <br />
					<hr />
					<div id="qtczoneruleError"></div>
				</div>
			</div>
			<input type="hidden" name="zonerule_id" id="zonerule_id" value="<?php echo $this->rule_id; ?>" />
		</div>
	</form>
</div>
