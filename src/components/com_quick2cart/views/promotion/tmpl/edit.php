<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2016. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHTML::_('behavior.modal');
// Import CSS
$jinput = JFactory::getApplication();
$baseUrl = $jinput->input->server->get('REQUEST_URI', '', 'STRING');
$calledFrom = (strpos($baseUrl, 'administrator'))?'backend':'frontend';
$document = JFactory::getDocument();
$comquick2cartHelper = new comquick2cartHelper;
$storeHelper = $comquick2cartHelper->loadqtcClass(JPATH_SITE . "/components/com_quick2cart/helpers/storeHelper.php", "storeHelper");
$storeList = (array) $storeHelper->getStoreList();

// Get currencies
$params = JComponentHelper::getParams('com_quick2cart');
$currencies = $params->get('addcurrency');
$currency = explode(',', $currencies);

if (empty($this->storeList))
{
?>
	<div class="alert alert-danger"><?php echo JText::_("COM_QUICK2CART_CREATE_ORDER_AUTHORIZATION_ERROR");?></div>
<?php
	return false;
}

// Get list of stores in array
foreach ($this->storeList as $key => $value)
{
	$value = (array)$value;
	$options[] = JHtml::_('select.option', $value["id"],$value['title']);
}

// Get list of types of discounts in array
foreach ($this->discount_type as $key => $value)
{
	$discount_types[] = JHtml::_('select.option', $key, $value);
}

// Get list of condition types in array
foreach ($this->condition_type as $key => $value)
{
	$condition_type[] = JHtml::_('select.option', $key, $value);
}

?>
<script>
	var conditionCnt = <?php echo !empty($this->conditionMaxCount)? $this->conditionMaxCount:0;?>;

	var conditionCount = (Number(conditionCnt)+1);

	Joomla.submitbutton = function (task)
	{
		if (task == 'promotion.apply')
		{
			if (techjoomla.jQuery(".qtc-promotion-condition-div-clone").length < 2)
			{
				alert(Joomla.JText._("COM_QUICK2CART_PROMOTION_CONDITION_MSG"));

				return false;
			}
		}

		if (task == 'promotion.cancel')
		{
			Joomla.submitform(task, document.getElementById('promotion-form'));
		}
		else
		{
			if (document.getElementById('jform_from_date').value !== "" && document.getElementById('jform_exp_date').value !== "")
			{
				if (document.getElementById('jform_from_date').value > document.getElementById('jform_exp_date').value)
				{
					alert(Joomla.JText._("COM_QUICK2CART_DATES_INVALID"));

					return false;
				}
			}

			if (document.getElementById('jform_max_use') !== null && document.getElementById('jform_max_per_user') !== null)
			{
				if (Number(document.getElementById('jform_max_use').value) < Number(document.getElementById('jform_max_per_user').value))
				{
					alert(Joomla.JText._("COM_QUICK2CART_USES_INVALID"));

					return false;
				}
			}


			if (task != 'promotion.cancel' && document.formvalidator.isValid(document.id('promotion-form')))
			{
				Joomla.submitform(task, document.getElementById('promotion-form'));
			}
			else
			{
				alert(Joomla.JText._('JGLOBAL_VALIDATION_FORM_FAILED'));
			}
		}
	}
</script>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?> my_promotions">
	<form
		action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>"
		method="post" enctype="multipart/form-data" name="adminForm" id="promotion-form" class="form-validate">
		<?php
		if ($calledFrom == 'frontend')
		{
			$active = 'promotions';
			ob_start();
			include($this->toolbar_view_path);
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
		?>
			<legend><?php echo JText::_('COM_QUICK2CART_TITLE_PROMOTION'); ?></legend>
		<?php
		}
		?>
		<div class="form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'promotions', array('active' => 'promotionrules')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'promotions', 'promotionrules', JText::_('COM_QUICK2CART_TITLE_PROMOTION', true)); ?>
			<div class="row">
				<div class="col-md-12 col-lg-12 form-horizontal">
					<fieldset class="adminform">
					<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
					<div class="form-group">
						<label for="store_id" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
							<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_FORM_DESC_PROMOTION_STORE_ID'), JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_STORE_ID'), '', JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_STORE_ID'));?>
						</label>
						<div class="col-md-10 col-sm-9 col-xs-12">
						<?php
							echo JHtml::_('select.genericlist', $options,'jform[store_id]','','value','text',$this->item->store_id,'jform_store_id');?>
						</div>
					</div>
					<div class="form-group">
						<label for="jform_name" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
							<span class="hasTooltip" title="" data-original-title="<strong><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_NAME");?></strong><br /><?php echo JText::_("COM_QUICK2CART_FORM_DESC_PROMOTION_NAME");?>"><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_NAME");?>
								<span class="star">&nbsp;*</span>
							</span>
						</label>
						<div class="col-md-10 col-sm-9 col-xs-12">
							<textarea type="text" name="jform[name]" id="jform_name"  aria-invalid="false"><?php echo $this->item->name;?></textarea>
						</div>
					</div>
					<div class="form-group">
						<label for="jform_description" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
							<span class="hasTooltip" title="" data-original-title="<strong><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_DESCRIPTION");?></strong><br /><?php echo JText::_("COM_QUICK2CART_FORM_DESC_PROMOTION_DESCRIPTION");?>"><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_DESCRIPTION");?>
								<span class="star">&nbsp;*</span>
							</span>
						</label>
						<div class="col-md-10 col-sm-9 col-xs-12">
							<textarea type="text" name="jform[description]" id="jform_description" class="textarea" aria-invalid="false"><?php echo $this->item->description;?></textarea>
						</div>
					</div>
					<div class="form-group">
						<label for="state" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
							<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_FORM_DESC_PROMOTION_PUBLISHED'), JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_PUBLISHED'), '', JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_PUBLISHED'));?>
						</label>
						<div class="col-md-10 col-sm-9 col-xs-12">
							<label class="radio-inline" for="state1">
							<?php $state = $this->item->state?>
							<input class="btn" type="radio" id="state1" name="jform[state]" <?php echo empty($this->item->state)?'':'checked="checked"';?> value="1"/>
							<?php echo JText::_("JYES");?></label>
							<label class="radio-inline" for="state0">
								<input class="btn" type="radio" <?php echo empty($this->item->state)?'checked="checked"':'';?> id="state0" name="jform[state]" value="0"/>
								<?php echo JText::_("JNO");?></label>
						</div>
					</div>
					<div class="form-group">
						<label for="jform_from_date" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label"><span class="hasTooltip" title="" data-original-title="<strong><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_FROM_DATE");?></strong><br /><?php echo JText::_("COM_QUICK2CART_FORM_DESC_PROMOTION_FROM_DATE");?>"><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_FROM_DATE");?></span></label>
						<div class="col-md-10 col-sm-9 col-xs-12">
							<div class="input-group">
								<?php echo JHtml::_('calendar', $this->item->from_date, 'jform[from_date]', 'jform_from_date', '%Y-%m-%d %H:%I:%S', array('class'=>'width160px input-small form-control', 'readonly'=>'true')); ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="jform_exp_date" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label"><span class="hasTooltip" title="" data-original-title="<strong><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_EXP_DATE");?></strong><br /><?php echo JText::_("COM_QUICK2CART_FORM_DESC_PROMOTION_EXP_DATE");?>"><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_EXP_DATE");?></span></label>
						<div class="col-md-10 col-sm-9 col-xs-12">
							<div class="input-group">
								<?php echo JHtml::_('calendar', $this->item->exp_date, 'jform[exp_date]', 'jform_exp_date', '%Y-%m-%d %H:%I:%S', array('class'=>'width160px input-small form-control', 'readonly'=>'true')); ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="discount_type" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
							<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_FORM_DESC_PROMOTION_COUPON_REQUIRED'), JText::_('COM_QUICK2CART_FORM_PROMOTION_COUPON_REQUIRED'), '', JText::_('COM_QUICK2CART_FORM_PROMOTION_COUPON_REQUIRED'));?>
						</label>
						<div class="col-md-10 col-sm-9 col-xs-12">
							<label class="radio-inline" for="coupon_required1">
								<?php $coupon_required = $this->item->coupon_required?>
								<input class="btn" type="radio" id="coupon_required1" name="jform[coupon_required]" <?php echo empty($this->item->coupon_required)?'':'checked="checked"';?> value="1"/>
								<?php echo JText::_("JYES");?>
							</label>
							<label class="radio-inline" for="coupon_required0">
								<input class="btn" type="radio" <?php echo empty($this->item->coupon_required)?'checked="checked"':'';?> id="coupon_required0" name="jform[coupon_required]" value="0"/>
								<?php echo JText::_("JNO");?>
							</label>
						</div>
					</div>
					<div class="form-group">
						<label for="discount_type" class="col-md-2 col-sm-3 col-xs-12 control-label">
							<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_FORM_DESC_PROMOTION_VAL_TYPE'), JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_VAL_TYPE'), '', JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_VAL_TYPE'));?>
						</label>
						<div class="col-md-10 col-sm-9 col-xs-12">
						<?php
						echo JHtml::_('select.genericlist', $discount_types,'jform[discount_type]','','value','text',$this->item->discount_type,'jform_discount_type');?>
						</div>
					</div>
					<div class="form-group">
						<label for="discount<?php echo !empty($curr[0]) ? $curr[0] : '' ;?>" class="col-md-2 col-sm-3 col-xs-12 control-label">
							<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_FORM_DESC_PROMOTION_VALUE'), JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_VALUE'), '', JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_VALUE'));?>
							<span class="star">&nbsp;*</span>
						</label>
						<div class="col-md-10 col-sm-9 col-xs-12">
							<div class="qtc-promotion-flat-discount-div">
							<?php
							foreach ($currency as $key => $value)
							{
								if (!empty($this->discount))
								{
									foreach ($this->discount as $discount)
									{
										if ($discount['currency'] == $value)
										{
											$dicountAmount = !empty($discount['discount'])?$discount['discount']:'';
										}
									}
								}

								$currsymbol = $comquick2cartHelper->getCurrencySymbol($value);
								?>
									<div>
										<?php if (count($currency) > 1) : ?>
											<label for="qtc_discount<?php echo trim($value);?>" class="col-lg-2 col-md-4 col-sm-4 col-xs-6 control-label qtc_currency_price_discount_lbl1 ">
												<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_FORM_DESC_PROMOTION_VALUE'), JText::_('QTC_ITEM_DIS_PRICE'), '', JText::_('COM_QUICK2CART_PRICE_DISCOUNT') . ' ' . JText::_('COM_QUICK2CART_PRICE_IN') . ' ' . trim($currsymbol));?>
											</label>
								<?php endif; ?>
										<div class="input-group curr_margin col-lg-2">
											<div style="display: none;">
											<label for="qtc_discount<?php echo trim($value);?>"><?php echo JText::_("QTC_AMOUNT");?></label>
											</div>
											<input class="qtc_currency_price_discount1 required q2c-inline form-control"
													id="qtc_discount<?php echo trim($value);?>"
													type="text"
													required="required"
													name="qtc_discount[flat][<?php echo trim($value);?>]"
													value="<?php echo !empty($dicountAmount)?$dicountAmount:'0';?>"
													placeholder="<?php echo trim($currsymbol);?>" />
											<div class="input-group-addon"><?php echo $currsymbol;?></div>

										</div>
									</div>
							<?php
							}
							?>
							</div>
							<div class="qtc-promotion-percent-discount-div">
								<input type="text"
									name="qtc_discount[percent]"
									value="<?php echo !empty($this->discount[0]['discount'])?$this->discount[0]['discount']:'0';?>"/>
							</div>
						</div>
					</div>
					<div class="qtc-promotion-discount-type-dependent">
						<div class="form-group qtc_currencey_textbox">
							<label for="discount<?php echo !empty($curr[0]) ? $curr[0] : '' ;?>" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
								<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_FORM_DESC_PROMOTION_MAX_DISCOUNTS'), JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_MAX_DISCOUNTS'), '', JText::_('COM_QUICK2CART_FORM_LBL_PROMOTION_MAX_DISCOUNTS'));?>
								<span class="star">&nbsp;*</span>
							</label>
							<div class="col-md-10 col-sm-9 col-xs-12">
							<?php
							foreach ($currency as $key => $value)
							{
								if (!empty($this->discount))
								{
									foreach ($this->discount as $discount)
									{
										if ($discount['currency'] == $value)
										{
											$maxDicountAmount = !empty($discount['max_discount'])?$discount['max_discount']:'';
										}
									}
								}

								$currsymbol = $comquick2cartHelper->getCurrencySymbol($value);

								if (count($currency) > 1)
								{
								?>
								<div class="  curr_margin">
									<label for="price_<?php echo trim($value);?>" class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label">
										<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_ITEM_PRICE_DESC'), JText::_('QTC_ITEM_PRICE'), '', JText::_('QTC_ITEM_PRICE') . ' ' . JText::_('COM_QUICK2CART_IN') . ' ' . trim($currsymbol));?> &nbsp;
									</label>
									<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 input-group">
										<input
											class="currtext required qtc_requiredoption"
											style="align:right;"
											id="price_<?php echo trim($value);?>"
											type="text"
											name="multi_cur[<?php echo trim($value);?>]"
											value="<?php echo !empty($maxDicountAmount)?$maxDicountAmount:'0';?>"
											placeholder="<?php echo trim($currsymbol);?>" />
										<div class="input-group-addon"><?php echo $currsymbol;?></div>
									</div>
									<div class="qtcClearBoth"></div>
								</div>
								<?php
								}
								else
								{
								?>
									<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 input-group curr_margin">
										<input
											class="currtext required qtc_requiredoption"
											id="price_<?php echo trim($value);?>"
											type="text"
											name="multi_cur[<?php echo trim($value);?>]"
											value="<?php echo !empty($maxDicountAmount)?$maxDicountAmount:'0';?>"
											placeholder="<?php echo trim($currsymbol);?>" />
										<div class="input-group-addon"><?php echo $currsymbol;?></div>
									</div>
									<div class="qtcClearBoth"></div>
								<?php
								}
							}
							?>
							</div>
						</div>
					</div>
					<div class="qtc-promotion-coupon-dependent">
						<div class="form-group">
							<label for="jform_coupon_code" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
								<span class="hasTooltip" title="" data-original-title="<strong><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_CODE");?></strong><br /><?php echo JText::_("COM_QUICK2CART_FORM_DESC_PROMOTION_CODE");?>"><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_CODE");?>
								</span>
							</label>
							<div class="col-md-10 col-sm-9 col-xs-12">
								<input type="text" name="jform[coupon_code]" id="jform_coupon_code"  value="<?php echo $this->item->coupon_code;?>" aria-invalid="false">
							</div>
						</div>
						<div class="form-group">
							<label for="jform_max_use" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
								<span class="hasTooltip" title="" data-original-title="<strong><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_MAX_USE");?></strong><br /><?php echo JText::_("COM_QUICK2CART_FORM_DESC_PROMOTION_MAX_USE");?>"><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_MAX_USE");?>
								</span>
							</label>
							<div class="col-md-10 col-sm-9 col-xs-12">
								<input type="text" name="jform[max_use]" id="jform_max_use"  value="<?php echo !empty($this->item->max_use) ? $this->item->max_use : 0; ?>" aria-invalid="false">
							</div>
						</div>
						<div class="form-group">
							<label for="jform_max_per_user" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
								<span class="hasTooltip" title="" data-original-title="<strong><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_MAX_PER_USER");?></strong><br /><?php echo JText::_("COM_QUICK2CART_FORM_DESC_PROMOTION_MAX_PER_USER");?>"><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_MAX_PER_USER");?>
								</span>
							</label>
							<div class="col-md-10 col-sm-9 col-xs-12">

								<input type="text" name="jform[max_per_user]" id="jform_max_per_user"  value="<?php echo !empty($this->item->max_per_user) ? $this->item->max_per_user : 0; ?>"
							</div>
						</div>
					</div>
					<div class="form-group">
<!--
						<label for="jform_terms_and_conditions" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
							<span class="hasTooltip" title="" data-original-title="<strong><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_TERMS_AND_CONDITIONS");?></strong><br /><?php echo JText::_("COM_QUICK2CART_FORM_DESC_PROMOTION_TERMS_AND_CONDITIONS");?>"><?php echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_TERMS_AND_CONDITIONS");?>
							</span>
						</label>

						<div class="col-md-10 col-sm-9 col-xs-12">
							<textarea name="jform[terms_and_conditions]" id="jform_terms_and_conditions"  aria-invalid="false"><?php echo (!empty($this->item->terms_and_conditions))?$this->item->terms_and_conditions:'';?></textarea>
						</div>
-->
					</div>
					</fieldset>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<!--Div to be cloned-starts -->
			<div class="row">
				<div id="qtc-promotion-condition-div-primary" class="qtc-promotion-condition-text">
					<div id="qtc-promotion-condition-div-cloneprimary" class="qtc-promotion-condition-div-clone" style="display:none;">
						<input type="hidden"class="input-small" name="rule[conditions][primary][id]">
						<span><?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_IF");?></span>
						<input type="hidden" name="rule[conditions][primary][condition_on]"></input>
						<select id="conditions_onprimary" name="rule[conditions][primary][condition_on_attribute]" class="qtc-promotion-margin q2c-inline" onchange="checkOperation('primary')">
							<optgroup label="<?php echo JText::_("COM_QUICK2CART_ADD_CONDITION_INFO");?>" disabled>
							</optgroup>
							<optgroup label="<?php echo JText::_("COM_QUICK2CART_PRODUCT");?>">
								<option value="category"><?php echo JText::_("COM_QUICK2CART_CAT");?></option>
								<option value="item_id"><?php echo JText::_("COM_QUICK2CART_PRODUCT");?></option>
							</optgroup>
							<optgroup label="<?php echo JText::_('COM_QUICK2CART_CONDITION_ON_CART');?>">
								<option value="cart_amount"><?php echo JText::_("COM_QUICK2CART_CONDITION_ON_CART_TOTAL_AMOUNT");?></option>
								<option value="quantity_in_store_cart"><?php echo JText::_("COM_QUICK2CART_CONDITION_ON_TOTAL_QUANTITY_IN_CART");?></option>
							</optgroup>
						</select>
						<span id="conditions_operation_wrapperprimary">
							<span><?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_IS");?> </span>
							<span id="conditions_operation_divprimary">
								<select id="conditions_operationprimary" name="rule[conditions][primary][operation]" class="qtc-promotion-margin q2c-inline">
									<option value="=">
										<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_EQUAL_TO");?>
									</option>
									<option value="<">
										<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_LESS_THAN");?>
									</option>
									<option value=">">
										<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_GREATER_THAN");?>
									</option>
									<option value=">=">
										<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_GREATER_THAN_EQUALTO");?>
									</option>
									<option value="<=">
										<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_LESS_THAN_EQUALTO");?>
									</option>
								</select>
							</span>
						</span>

						<span id="currency_wrapperprimary">
							<span id="currency_divprimary">
							</span>
						</span>

						<span id="attribute_value_wrapperprimary">
							<span id="attribute_value_divprimary">
							</span>
						</span>

						<span id="qtc-remove-conditionprimary">
							<a class="qtcHandPointer" onclick="qtcRemovePromotionCondition('primary');" title="<?php echo JText::_('COM_Q2C_REMOVE_TOOLTIP');?>">
								<img title="<?php echo JText::_('COM_QUICK2CART_REMOVE_OPTION');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/remove_rule_condition.png"/>
							</a>
						</span>

						<!-- Div for quiantity condition-start -->
						<div id="qtc-add-quantity-condition-divprimary" 	class="q2c-inline">
							<div id="qtc-add-quantity-conditionprimary" class="qtc-add-quantity-condition-padding-left">
								<a class="qtcHandPointer" class="qtcHandPointer" onclick="qtcAddQuantityCondition('primary');">
									<img title="<?php echo JText::_('COM_QUICK2CART_ADD_QUANTITY_CONDITION');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/add_rule_condition.png"/>
								</a>
							</div>
						</div>
						<hr />
						<!-- Div for quiantity condition-end -->
					</div>
				</div>
			</div>
			<!--Div to be cloned-ends-->

			<!--Div for attribute value to be cloned - starts-->
			<div class="row">
				<span id="qtc-condition-attribute-value-primary-div">
					<span id="qtc-condition-attribute-value-primary-divclone" style="display:none;">
						<input type="text" name="rule[conditions][primary][condition_attribute_value]" id="rule_conditions_primary_condition_attribute_value" class="qtc_condition_attribute_value qtc-promotion-margin q2c-inline" readonly="true"></input>
						<label id="rule_conditions_primary_condition_attribute_value_id" for="rule_conditions_primary_condition_attribute_value" class="hidden"><?php echo JText::_("COM_QUICK2CART_CONDITION_VALUE_LABEL");?></label>
						<a class="qtcHandPointer" id="rule_conditions_attribute_selectprimary" title="<?php echo JText::_('COM_QUICK2CART_PROMOTION_CONDITION_SELECT')?>" onclick="openSelector('primary')"><img src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/magnifier.png"></a>
					</span>
				</span>
			</div>
			<!--Div for attribute value to be cloned - ends-->

			<!--Currency div clone starts-->

				<?php
				if (!empty($currency))
				{
				?>
				<div class="row">
					<span id="qtc-cart-amount-currency-primary-div">
						<span id="qtc-cart-amount-currency-primary-divclone" style="display:none;">
						<?php
						foreach ($currency as $key => $value)
						{
							if (!empty($this->discount))
							{
								foreach ($this->discount as $discount)
								{
									if ($discount['currency'] == $value)
									{
										$dicountAmount = !empty($discount['discount'])?$discount['discount']:'';
									}
								}
							}

							$currsymbol = $comquick2cartHelper->getCurrencySymbol($value);
							?>
							<span class="curr_margin   q2c-inlineblock">
								<div style="display: none;">
									<label for="conditions_primary_currency_value_<?php echo $value;?>"><?php echo JText::_("QTC_AMOUNT");?></label>
								</div>
								<div class="input-group qtc_currency_price_discount_lbl ">
								<input class="qtc_currency_price_discount1 q2c-inline form-control"
										type="text"
										id="conditions_primary_currency_value_<?php echo $value;?>"
										name="rule[conditions][primary][condition_attribute_value][<?php echo $value;?>]"
										placeholder="<?php echo trim($currsymbol);?>" />
								<span class=" input-group-addon "><?php echo $currsymbol;?></span>
								</div>
								<div class="qtcClearBoth"></div>
							</span>
						<?php
						}
						?>
						</span>
					</span>
				</div>
					<?php
				}
				?>
			<!--Currency div clone ends-->

			<!-- prestored conditions display start-->
			<?php echo JHtml::_('bootstrap.addTab', 'promotions', 'conditions', JText::_('COM_QUICK2CART_FORM_DESC_PROMOTION_CONDITION', true)); ?>
			<div class="row">
			<div class="col-md-12">
				<span class="h4"><?php echo JText::_("COM_QUICK2CART_CONDITION_INFO1");?></span>
					<span id="qtc-promotion-condition-compulsory" class="q2c-inlineselect">
					<?php echo JHtml::_('select.genericlist', $condition_type,'conditions_compulsory','','value','text',(!empty($this->conditionList[0]->is_compulsary)?$this->conditionList[0]->is_compulsary:''),'conditions_compulsory');?>
					</span>
				<span class="h4">
					<?php echo JText::_("COM_QUICK2CART_CONDITION_INFO2");?>
				</span>
				<hr />
				<div id="qtc-promotion-condition-div" class="qtc-promotion-condition-text row">
					<?php
					if ($this->item->id && (!empty($this->conditionList)))
					{
						foreach ($this->conditionList as $key => $condition)
						{
					?>
						<div id="qtc-promotion-condition-div-clone<?php echo $condition->id;?>" class="qtc-promotion-condition-div-clone">
							<input type="hidden"class="input-small" name="rule[conditions][<?php echo $condition->id;?>][id]" value="<?php echo $condition->id;?>">
							<span><?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_IF");?></span>
							<input type="hidden" name="rule[conditions][<?php echo $condition->id;?>][condition_on]" value="<?php echo $condition->condition_on;?>"></input>
							<select id="conditions_on<?php echo $condition->id;?>" name="rule[conditions][<?php echo $condition->id;?>][condition_on_attribute]" class="qtc-promotion-margin q2c-inline" onchange="checkOperation('<?php echo $condition->id;?>')">
								<optgroup label="<?php echo JText::_("COM_QUICK2CART_ADD_CONDITION_INFO");?>" disabled>
								</optgroup>
								<optgroup label="<?php echo JText::_("COM_QUICK2CART_PRODUCT");?>">
									<option value="category" <?php echo ($condition->condition_on_attribute == 'category')?' selected="true" ':''?>><?php echo JText::_("COM_QUICK2CART_CAT");?></option>
									<option value="item_id" <?php echo ($condition->condition_on_attribute == 'item_id')?' selected="true" ':''?>><?php echo JText::_("COM_QUICK2CART_PRODUCT");?></option>
								</optgroup>
								<optgroup label="<?php echo JText::_('COM_QUICK2CART_CONDITION_ON_CART');?>">
									<option value="cart_amount" <?php echo ($condition->condition_on_attribute == 'cart_amount')?' selected="true" ':''?>><?php echo JText::_("COM_QUICK2CART_CONDITION_ON_CART_TOTAL_AMOUNT");?></option>
									<option value="quantity_in_store_cart" <?php echo ($condition->condition_on_attribute == 'quantity_in_store_cart')?' selected="true" ':''?>><?php echo JText::_("COM_QUICK2CART_CONDITION_ON_TOTAL_QUANTITY_IN_CART");?></option>
								</optgroup>
							</select>
							<span id="conditions_operation_wrapper<?php echo $condition->id;?>">
								<?php
								if ($condition->condition_on_attribute == 'cart_amount' || $condition->condition_on_attribute == 'quantity_in_store_cart')
								{
								?>
								<span><?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_IS");?></span>
								<span id="conditions_operation_div<?php echo $condition->id?>">
									<select id="conditions_operation<?php echo $condition->id?>" class="qtc-promotion-margin q2c-inline" name="rule[conditions][<?php echo $condition->id?>][operation]">
										<option value="=" <?php echo ($condition->operation == '=')?'selected="true"':'';?>>
											<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_EQUAL_TO");?>
										</option>
										<option value="<" <?php echo ($condition->operation == '<')?'selected="true"':'';?>>
											<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_LESS_THAN");?>
										</option>
										<option value=">" <?php echo ($condition->operation == '>')?'selected="true"':'';?>>
											<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_GREATER_THAN");?>
										</option>
										<option value=">=" <?php echo ($condition->operation == '>=')?'selected="true"':'';?>>
											<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_GREATER_THAN_EQUALTO");?>
										</option>
										<option value="<=" <?php echo ($condition->operation == '<=')?'selected="true"':'';?>>
											<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_LESS_THAN_EQUALTO");?>
										</option>
									</select>
								</span>
								<?php
								}
								else
								{
								?>
									<span><?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_IN");?></span>
								<?php
								}
								?>
							</span>

							<?php
							if (!empty($condition->condition_attribute_value))
							{
								$conditionAttributeValue = json_decode($condition->condition_attribute_value);
							}

							if ($condition->condition_on_attribute == "cart_amount")
							{
								if (!empty($currency))
								{
									?>
								<span id="currency_wrapper<?php echo $condition->id;?>">
									<span id="currency_div<?php echo $condition->id;?>">
									<?php
									foreach ($currency as $key => $value)
									{
										if (!empty($this->discount))
										{
											foreach ($this->discount as $discount)
											{
												if ($discount['currency'] == $value)
												{
													$dicountAmount = !empty($discount['discount'])?$discount['discount']:'';
												}
											}
										}

										$currsymbol = $comquick2cartHelper->getCurrencySymbol($value);
										?>
										<span class="curr_margin q2c-inlineblock">
											<div style="display: none;">
											<label for="conditions_<?php echo $condition->id;?>_currency_value_<?php echo $value;?>"><?php echo JText::_("QTC_AMOUNT");?></label>
											</div>
											<div class="input-group qtc_currency_price_discount_lbl">
												<input class="qtc_currency_price_discount1 q2c-inline form-control required"
														type="text"
														required="required"
														id="conditions_<?php echo $condition->id;?>_currency_value_<?php echo $value;?>"
														name="rule[conditions][<?php echo $condition->id;?>][condition_attribute_value][<?php echo $value;?>]"
														value="<?php echo ($condition->condition_on_attribute == 'cart_amount')?$conditionAttributeValue->$value:""?>"
														placeholder="<?php echo trim($currsymbol);?>" />
												<span class=" input-group-addon "><?php echo $currsymbol;?></span>
											</div>
											<div class="qtcClearBoth"></div>
										</span>
									<?php
									}
									?>
									</span>
								</span>
									<?php
								}
							}
							else
							{
								$conditionAttributeValue = implode(",", $conditionAttributeValue);
							?>
							<span id="currency_wrapper<?php echo $condition->id;?>">
								<span id="currency_div<?php echo $condition->id;?>">
								<input type="text" <?php echo ($condition->condition_on_attribute == 'quantity_in_store_cart')?"":"readonly='true'";?> name="rule[conditions][<?php echo $condition->id;?>][condition_attribute_value]" required="required" id="rule_conditions_<?php echo $condition->id;?>_condition_attribute_value" class="qtc_condition_attribute_value required qtc-promotion-margin q2c-inline" value="<?php echo ($condition->condition_on_attribute == 'cart_amount')?"":$conditionAttributeValue;?>"></input>
								<label id="rule_conditions_<?php echo $condition->id;?>_condition_attribute_value_id" for="rule_conditions_<?php echo $condition->id;?>_condition_attribute_value" class="hidden"><?php echo JText::_("COM_QUICK2CART_CONDITION_VALUE_LABEL");?></label>
								<a class="qtcHandPointer" <?php echo ($condition->condition_on_attribute == 'quantity_in_store_cart')?"class='qtc-product-attribute-selector-hide'":"";?> id="rule_conditions_attribute_select<?php echo $condition->id;?>" title="<?php echo JText::_('COM_QUICK2CART_PROMOTION_CONDITION_SELECT')?>" onclick="openSelector('<?php echo $condition->id;?>')"><img src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/magnifier.png"></a>
								</span>
							</span>
							<?php
							}
							?>

							<span id="qtc-remove-conditionprimary">
								<a class="qtcHandPointer" onclick="qtcRemovePromotionCondition('<?php echo $condition->id;?>');">
									<img title="<?php echo JText::_('COM_QUICK2CART_REMOVE_OPTION');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/remove_rule_condition.png"/>
								</a>
							</span>
							<div id="qtc-add-quantity-condition-div<?php echo $condition->id;?>" class="q2c-inline">
								<div id="qtc-add-quantity-condition<?php echo $condition->id;?>" class="qtc-add-quantity-condition-padding-left q2c-inline">
									<?php

									if (($condition->condition_on_attribute == 'category' || $condition->condition_on_attribute == 'item_id'))
									{
										if (!empty($condition->quantity))
										{
									?>
										<span><?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_QUANTITY_INFO");?></span>
										<span id="conditions_operation_wrapper<?php echo $condition->id;?>">
										</span>
										<span id="conditions_operation_div<?php echo $condition->id?>">
											<select id="conditions_operation<?php echo $condition->id;?>" name="rule[conditions][<?php echo $condition->id;?>][operation]" class="qtc-promotion-margin q2c-inline">
												<option value="=" <?php echo ($condition->operation == '=')?'selected="true"':'';?>>
													<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_EQUAL_TO");?>
												</option>
												<option value="<" <?php echo ($condition->operation == '<')?'selected="true"':'';?>>
													<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_LESS_THAN");?>
												</option>
												<option value=">" <?php echo ($condition->operation == '>')?'selected="true"':'';?>>
													<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_GREATER_THAN");?>
												</option>
												<option value=">=" <?php echo ($condition->operation == '>=')?'selected="true"':'';?>>
													<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_GREATER_THAN_EQUALTO");?>
												</option>
												<option value="<=" <?php echo ($condition->operation == '<=')?'selected="true"':'';?>>
													<?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_LESS_THAN_EQUALTO");?>
												</option>
											</select>
										</span>
										<label  for="rule_conditions_<?php echo $condition->id;?>_quantity" class="hidden"><?php echo JText::_("COM_QUICK2CART_CONDITION_ON_PRODUCT_QUANTITY");?></label>
										<input type='text' id='rule_conditions_<?php echo $condition->id;?>_quantity' name='rule[conditions][<?php echo $condition->id;?>][quantity]' class='required qtc_condition_quantity q2c-inline' required='required' value="<?php echo $condition->quantity;?>"><label  for='rule_conditions_"<?php echo $condition->id;?>"_quantity' class='hidden'>Condition : Quantity</label>
										<a class="qtcHandPointer" onclick="qtcRemoveQuantityCondition('<?php echo $condition->id;?>');" title="<?php echo JText::_('COM_Q2C_REMOVE_TOOLTIP');?>">
											<img title="<?php echo JText::_('COM_QUICK2CART_REMOVE_OPTION');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/remove_rule_condition.png"/>
										</a>
										<?php
										}
										else
										{
										?>
											<a class="qtcHandPointer" onclick="qtcAddQuantityCondition('<?php echo $condition->id;?>');">
												<img title="<?php echo JText::_('COM_QUICK2CART_ADD_QUANTITY_CONDITION');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/add_rule_condition.png"/>
											</a>
										<?php
										}
									}
									?>
								</div>
							</div>
							<hr />
						</div>
					<?php
						}
					}?>
				</div>
			</div>
			</div>
			<div>
				<div id="qtc-promotion-condition"></div>
				<a class="qtcHandPointer" onclick="qtcAddPromotionCondition();" >
					<img title="<?php echo JText::_('COM_QUICK2CART_ADD_CONDITION');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/add_rule_condition.png"/>
				</a>
			</div>
			<?php
				if(!empty($this->promotionDescription))
				{
			?>
				<div class="qtc_promotion_rule_description_div alert alert-info">
					<?php
						echo JText::_("COM_QUICK2CART_FORM_LBL_PROMOTION_DESCRIPTION") . " : " . ucwords($this->promotionDescription);
					?>
					<div>
					<?php
						echo JText::_("COM_QUICK2CART_PROMOTION_DESCRIPTION_NOTE");
					?>
					</div>
				</div>
			<?php
				}
			?>
			<?php echo JHtml::_('bootstrap.endTab');?>
			<?php echo JHtml::_('bootstrap.endTabSet');?>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token');?>
			<?php

			if ($calledFrom == 'frontend')
			{
			?>
			<div class="center qtc-button-top-margin">
				<input type="button" class="btn btn-success" value="<?php echo JText::_('BUTTON_SAVE_TEXT');?>" onclick="Joomla.submitbutton('promotion.apply');">
				<input type="button" class="btn btn-danger" value="<?php echo JText::_('BUTTON_CANCEL_TEXT');?>" onclick="Joomla.submitbutton('promotion.cancel');">
			</div>
			<?php
			}
			?>
		</div>
	</form>
</div>
