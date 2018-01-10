<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">

	techjoomla.jQuery(document).ready(function() {
		generateStoreState(<?php echo isset($this->item->country_code)?$this->item->country_code:0;?>, <?php echo !empty($this->item->state_code)?$this->item->state_code:"0";?>);
	});

	function saveaddress(id)
	{
		values = techjoomla.jQuery('#formcustomeraddress').serialize();

		var  qtcBillForm = document.formcustomeraddress;

		if (!document.formvalidator.isValid(qtcBillForm))
		{
			return false;
		}

		var u_country = techjoomla.jQuery("#country_code option:selected").val();
		var u_state = techjoomla.jQuery("#state_code option:selected").val();
		var userId = window.parent.techjoomla.jQuery("#qtcuser option:selected").val();
		var callurl = "index.php?option=com_quick2cart&task=customer_addressform.save&userid="+userId+"&country_code="+u_country+"&state_code="+u_state+"&tmpl=component";

		techjoomla.jQuery.ajax({
			url: callurl,
			type: "GET",
			data:values,
			cache: false,
			success: function(data)
			{
				if (id == '-1')
				{
					window.parent.techjoomla.jQuery('#qtc_user_addresses').removeAttr( "style" );
					window.parent.techjoomla.jQuery('#qtc_user_addresses .qtc_user_addresses_wrapper').append(data);
					alert("<?php echo JText::_("COM_QUICK2CART_CUSTOMER_ADDRESS_ADD_MSG");?>");
				}
				else
				{
					window.parent.techjoomla.jQuery('#qtc_user_addresses').removeAttr( "style" );
					window.parent.techjoomla.jQuery('.qtc-address'+id).replaceWith(data);
					alert("<?php echo JText::_("COM_QUICK2CART_CUSTOMER_ADDRESS_UPDATE_MSG");?>");
				}

				if (window.parent.techjoomla.jQuery('#qtc_user_addresses .qtc_user_addresses_wrapper').length)
				{
					window.parent.techjoomla.jQuery('.checkout-addresses').show();
				}

				window.parent.SqueezeBox.close();
			}
		});
	}

	function generateStoreState(field_name, valToSelect)
	{
		var countryId = 'country_code';
		var country_value=techjoomla.jQuery('#'+countryId).val();

		if (valToSelect == 0)
		{
			var e = document.getElementById("state_code");
			var valToSelect = e.options[e.selectedIndex].value;
		}

		var postData = {default_value : 1}

		techjoomla.jQuery.ajax({
			type : "POST",
			url : "index.php?option=com_quick2cart&task=vendor.getRegions&tmpl=component&country_id="+country_value+"&tmpl=component",
			data:postData,
			success : function(response)
			{
				techjoomla.jQuery('#state_code').html(response);

				if (valToSelect > 0)
				{
					techjoomla.jQuery("#state_code option[value='" + valToSelect + "']").attr("selected", "true");
				}


			}
		});
	}
</script>

<div class="customer_address-edit front-end-edit">
	<?php if (!empty($this->item->id)): ?>
		<h1><?php echo JText::_('COM_QUICK2CART_EDIT_CUSTOMER_ADDRESS'); ?></h1>
	<?php else: ?>
		<h1><?php echo JText::_('COM_QUICK2CART_ADD_CUSTOMER_ADDRESS'); ?></h1>
	<?php endif; ?>
	<br>

	<form name="formcustomeraddress" id="formcustomeraddress"
		method="post" class="form-validate form-horizontal" enctype="multipart/form-data" >

	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />

	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('firstname'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('firstname'); ?></div>
	</div>
	<?php
		if ($this->params->get('qtc_middlenmae')==1)
		{
	?>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('middlename'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('middlename'); ?></div>
			</div>
	<?php
		}
	?>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('lastname'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('lastname'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('user_email'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('user_email'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('vat_number'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('vat_number'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('address'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('address'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('land_mark'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('land_mark'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('city'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('city'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('zipcode'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('zipcode'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><label><?php echo JText::_("COM_QUICK2CART_FORM_LBL_CUSTOMER_ADDRESS_COUNTRY"); ?><span class="star">&nbsp;*</span></label></div>
		<div class="controls">
			<?php
				$default_country = (isset($this->item->country_code)) ? $this->item->country_code : $this->params->get('set_default_country','');
				$options = array();
				$options[] = JHtml::_('select.option', "", JText::_('QTC_BILLIN_SELECT_COUNTRY'));

				foreach ($this->countrys as $key=>$value)
				{
					$options[] = JHtml::_('select.option', $value['id'], $value['country']);
				}

				echo $this->dropdown = JHtml::_('select.genericlist',$options,'country_code',' data-chosen="qtc" required="required" onchange=\'generateStoreState(id,"1")\' ','value','text', $default_country);
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label"><label><?php echo JText::_('QTC_BILLIN_STATE'); ?></label></div>
		<div class="controls">
			<select id="state_code" class="required='required'" data-chosen="qtc">
				<option selected="selected"><?php echo JText::_('QTC_BILLIN_SELECT_STATE')?></option>
			</select>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('phone'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('phone'); ?></div>
	</div>
		<div class="control-group">
			<div class="controls">
				<?php if ($this->canSave): ?>
				<a onclick="saveaddress('<?php echo ($this->item->id)?$this->item->id:'-1';?>')" class="btn btn-primary">
					<?php echo JText::_('JSUBMIT'); ?>
				</a>
				<?php endif; ?>
				<a class="btn btn-default"
					onclick="window.parent.SqueezeBox.close();"
					title="<?php echo JText::_('JCANCEL'); ?>">
					<?php echo JText::_('JCANCEL'); ?>
				</a>
			</div>
		</div>
		<input type="hidden" name="option" value="com_quick2cart"/>
		<input type="hidden" name="task" value="customer_addressform.save"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
