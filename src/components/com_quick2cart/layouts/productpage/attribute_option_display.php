<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

$data = $displayData;
$comquick2cartHelper = new comquick2cartHelper;
$att_currency = comquick2cartHelper::getCurrencySession();

// Get current stock settings
//$params               = JFactory::getApplication()->getParams('com_quick2cart');
$params   = JComponentHelper::getParams('com_quick2cart');
$usestock             = $params->get('usestock');
$outofstock_allowship = $params->get('outofstock_allowship');
$chekForStock = 0;
$product_id          = $data['product_id'];

if ($usestock == 1 && $outofstock_allowship == 0)
{
	$chekForStock = 1;
}

$parent = '';
$is_stock_keepingAttri = 0;

if (isset($data['parent']))
{
	$parent = $data['parent'];
}

if (!empty($data['attributeDetail']))
{
	$atri_options = $data['attributeDetail']->optionDetails;
	$is_stock_keepingAttri = $data['attributeDetail']->is_stock_keeping;
}

$select_opt   = array();
$userData     = array();
$userData[]   = 'Textbox';

if (!$data['attribute_compulsary'] && !in_array($data['fieldType'], $userData))
{
	$select_opt[] = JHtml::_('select.option', "", "");
}

$returnHtml = '';

foreach ($atri_options as $atri_option)
{
	// Check whether option is published or not
	if (empty($atri_option->state))
	{
		continue;
	}

	// For backend: use currency which is sent from data array. From front end, you can use the from session
	$useCurrency = !empty($data['currency']) ? $data['currency'] : $att_currency;

	$attOp_price = 0;

	if (!empty($atri_option->$useCurrency))
	{
		$attOp_price = (float)  $atri_option->$useCurrency;
	}

	// IF  Atrr price is 0 then don't add +0 USD
	if (!empty($attOp_price))
	{
		$priceText = $comquick2cartHelper->getFromattedPrice($attOp_price, NULL, 0);
		$opt_str   = $atri_option->itemattributeoption_name . ": " . $atri_option->itemattributeoption_prefix . " " . $priceText;
	}
	else
	{
		//  If no price than dont append like  +00.0 USD
		$opt_str = $atri_option->itemattributeoption_name;
	}

	//  Generate op according to datatype

	if (in_array($data['fieldType'], $userData))
	{
		$renderer_view = !empty($data['renderer_view']) ? $data['renderer_view'] : '';

		$attribute_selectbox_name = !empty($data['field_name']) ? $data['field_name'] : 'qtcUserField_' . $atri_option->itemattributeoption_id;

		$returnHtml = "<input type='text' name='" . $attribute_selectbox_name . "' class='input input-small " . $parent . '-' . $product_id . '_UserField' . "' >";
	}
	else
	{
		$field_name = 'attri_option';

		if (isset($data['field_name']))
		{
			$field_name = $data['field_name'];
		}

		$option = new stdClass;
		$option->value  = $atri_option->itemattributeoption_id;
		$option->text  = $opt_str;
		$option->disabled  = 0;

		if ($chekForStock && $is_stock_keepingAttri)
		{
			if (isset($atri_option->child_product_detail->stock) && $atri_option->child_product_detail->stock <= 0)
			{
				$option->disabled  = 1;
			}
		}

		// User data
		// $select_opt[] = JHtml::_('select.option', $atri_option->itemattributeoption_id, $opt_str);
		$select_opt[] = $option;
	}
}


// For extra hidden fields
if (!empty($data['extraHiddenFields']))
{
	foreach ($data['extraHiddenFields'] as $extraField)
	{

	?>
		<input type="hidden" name="<?php echo $extraField['name']?>" value="<?php echo $extraField['value']?>">
	<?php
	}
}
// For select type or radio (Future)
if (!in_array($data['fieldType'], $userData))
{
	// This field is used to give name to attribute
	$default_value = '';

	if (isset($data['default_value']))
	{
		$default_value = $data['default_value'];
	}

	$selectFieldName = !empty($data['field_name']) ? $data['field_name'] : '';
	$selectFieldOnchangeEvent = !empty($data['fieldOnChange']) ? $data['fieldOnChange'] : '';
	?>
	<select class="q2c_AttoptionsMaxWidth <?php echo $parent; ?>-<?php echo $product_id ?>_options" onchange="<?php echo $selectFieldOnchangeEvent;?>" name="<?php echo $selectFieldName; ?>">
	<?php
		foreach ($select_opt as $op_key => $option)
		{
			$optionText = $option->text;

			if (!empty($option->disabled))
			{
				if ($option->disabled == 1)
				{
					$optionText .= JText::_("COM_QUICK2CART_PROD_PAGE_OPTION_SEL_OUT_OF_STOCK");
				}
			}

			$selected = '';
			if (!empty($default_value) && $default_value == $option->value)
			{
				$selected = 'selected="selected"';
			}

			?>
				<option value="<?php echo $option->value ?>" <?php echo (isset($option->disabled) && $option->disabled == 1) ? 'disabled "': ''?>  <?php echo $selected ?> >

					<?php echo (!empty($optionText)?$optionText:JText::_('COM_QUICK2CART_ADDPROD_LOADALL_GOLB_ATTROPTIONS'))?>
				</option>
			<?php
		}
	?>
	</select>
	<?php
}

echo $returnHtml;
?>

