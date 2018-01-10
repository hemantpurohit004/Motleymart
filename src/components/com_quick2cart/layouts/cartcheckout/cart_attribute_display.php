<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

$data = $displayData;
$attribute = $data['attributeDetail'];
$cart = $data['cart'];

$productHelper = new productHelper;
$comquick2cartHelper = new comquick2cartHelper;
?>
<input class="" id="" name="<?php echo 'cartDetail[' . $cart['id'] . '][attrDetail][' . $attribute->itemattribute_id . '][type]'; ?>" type="hidden" value="<?php echo $attribute->attributeFieldType ?>" >
<?php
$userData     =  productHelper::getUserFieldValues();

// For text type attribute
if (in_array($attribute->attributeFieldType, $userData))
{
	if(isset($attribute->optionDetails[0]->itemattributeoption_id))
	{
		$itemattributeoption_id = $attribute->optionDetails[0]->itemattributeoption_id;
	}
	else
	{
		$itemattributeoption_id = 'new';
	}

	$value = isset($cart['product_attributes_values'][$attribute->optionDetails[0]->itemattributeoption_id]->cartitemattribute_name)?$cart['product_attributes_values'][$attribute->optionDetails[0]->itemattributeoption_id]->cartitemattribute_name:'';
	?>
	<br/>

	<?php
	if ($attribute->attributeFieldType == "Textarea")
	{
		?>
		<textarea rows="3" cols="" class="qtcCartTextAreaAttr <?php echo $parent . '-' . $product_id . '_UserField';?>" name="<?php echo 'cartDetail[' . $cart['id'] . '][attrDetail][' . $attribute->itemattribute_id . '][value]' ?>">
		<?php echo $value; ?>
		</textarea>
		<?php
	}
	else
	{
		?>
		<input type="text"
			name="<?php echo 'cartDetail[' . $cart['id'] . '][attrDetail][' . $attribute->itemattribute_id . '][value]' ?>"
			class="input input-small"
			value ="<?php echo $value; ?>"
		/>
	<?php
	} ?>
	<!-- Attribute option id -->
	<input type="hidden" name="<?php echo 'cartDetail[' . $cart['id'] . '][attrDetail][' . $attribute->itemattribute_id . '][itemattributeoption_id]' ?>" class="input input-small" value ="<?php echo $itemattributeoption_id; ?>" />
<?php
}
else
{
	foreach ($attribute->optionDetails as $optionDetail)
	{
		if(	in_array($optionDetail->itemattributeoption_id, $product_attributes)	)
		{
			$data['default_value'] = $optionDetail->itemattributeoption_id;
			break;
		}
	}

	$productHelper = new productHelper();
	$data['itemattribute_id'] = $attribute->itemattribute_id;
	$data['fieldType'] = $attribute->attributeFieldType;
	$data['product_id'] = $cart['item_id'];
	$data['attribute_compulsary'] = $attribute->attribute_compulsary;

	$attrDetailsObject = $cart['product_attributes_values'][$data['default_value']];
	//$data['field_name'] = 'attri_option'.$attrDetailsObject->cartitemattribute_id;
	$data['field_name'] = 'cartDetail[' . $cart['id'] . '][attrDetail][' . $attribute->itemattribute_id . '][value]';

	// Generate field html (select box)
	$fieldHtml = $productHelper->getAttrFieldTypeHtml($data);
	?>
		<?php echo $fieldHtml;?>

	<?php
}
// else end
?>
