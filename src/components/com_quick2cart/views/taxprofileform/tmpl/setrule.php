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

?>
<script type="text/javascript">
	var root_url="<?php echo JUri::root(); ?>";

/** This function adds in zone
 */
function qtcUpdateZoneRule()
{
	var qtc_taxrate_id = document.id('jformtaxrate_id').value;
	var qtc_address = document.id('jformaddress').value;

	if(qtc_taxrate_id == '' || qtc_address == '')
	{
		techjoomla.jQuery('#qtcErrorContentDiv').html("<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_INVALID_SELECTION'); ?>");
		techjoomla.jQuery('.error').fadeIn();
		return false;
	}

	var data = {
			jform : {
				taxrule_id : document.id('taxrule_id').value,
				taxrate_id : qtc_taxrate_id,
				address : qtc_address,
			}
		};
		// Get country and region name
		var selected_rule = techjoomla.jQuery("#jformtaxrate_id").children("option").filter(":selected").text() ;
		var selected_adress = techjoomla.jQuery("#jformaddress").children("option").filter(":selected").text() ;

		techjoomla.jQuery.ajax({
					type : "POST",
					url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=taxprofileform&task=taxprofileform.updateTaxRule&tmpl=component",
					data : data,
					dataType: "json",
					success : function(response)
					{
						// If not Error
						if (response.error != 1)
						{
							// Remove Error dive content
							techjoomla.jQuery('#qtcErrorContentDiv').html('');
							techjoomla.jQuery('.qtcError').fadeOut();

							var taxrule_id= response.taxrule_id;
							// tax rate and address field value from parent window td
							window.parent.jQuery('#qtc_taxrate_'+taxrule_id).html(selected_rule);
							window.parent.jQuery('#qtc_address_'+taxrule_id).html(selected_adress);


							// intialize squeeze box again for edit button to work
							window.parent.SqueezeBox.initialize({});
							window.parent.SqueezeBox.assign($$('a.modal'), {
							parse: 'rel'
							});
							window.parent.SqueezeBox.close();
						//	location = '';

						}
						else
						{
							techjoomla.jQuery('#qtcErrorContentDiv').html(response.errorMessage);
							techjoomla.jQuery('.qtcError').fadeIn();
						}
					}
				});

		return false;
	}


</script>


 <div class="quick2cart-wrapper <?php echo Q2C_WRAPPER_CLASS; ?>  container-fluid">

    <div class="form-horizontal" id="no-more-tables">

		<div class="row">
			<form action="<?php //echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="qtcTaxRuleForm" id="qtcTaxRuleForm" class="form-validate">

			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 form-horizontal">
				<legend>
					<?php echo JText::_('COM_QUICK2CART_UPDATE_TAXRULE'); ?>
				</legend>
				<!-- For Error Display-->

				<input type="hidden" name="taxrule_id" id="taxrule_id" value="<?php echo $this->taxRule_id ?>" />
				<!-- Map the tax rule aginst tax profile -->
				<table class="table table-striped table-bordered">
					<tr>
						<td data-title="<?php echo JText::_('COM_QUICK2CART_S_FORM_LBL_TAXTRATE_TAXRATE_NAME');?>"><?php echo $this->taxrate; ?>
						</td>
						<td data-title="<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_ADDRESS_MAPPED');?>"><?php echo $this->address; ?>
						<td valign="top"><input type="button" id="CreateTaxRule"
							value="<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_UPDATE_TAXRATE'); ?>"
							class="btn btn-success" onClick="qtcUpdateZoneRule()" />
						</td>
					</tr>

						<!-- For Error Display-->
<!--
						<tr>
						<td id="zoneError" colspan="3" >
							<div class="error alert alert-danger qtcError" style="display: none;">
								<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
								<i class="<?php echo QTC_ICON_REMOVE; ?> pull-right" style="align: right;"
									onclick="techjoomla.jQuery(this).parent().fadeOut();"> </i> <br />
								<hr />
								<div id="qtcErrorContentDiv"></div>
							</div>
						</td>
						</tr>
-->

				</table>

				<div class="error alert alert-danger qtcError" style="display: none;">
					<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
					<i class="<?php echo QTC_ICON_REMOVE; ?> pull-right" style="align: right;"
						onclick="techjoomla.jQuery(this).parent().fadeOut();"> </i> <br />
					<hr />
					<div id="qtcErrorContentDiv"></div>
				</div>
            </div>
        </div>

			</form>
        <?php echo JHtml::_('form.token'); ?>

    </div>


</div> <!--qyc_admin_taxprofile set rule-->
