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
$storeHelper = new storeHelper;
?>


<?php
// Taxprofile and ship profile
$isShippingEnabled = $this->params->get('shipping', 0);
$isTaxationEnabled = $this->params->get('enableTaxtion', 0);

?>

<!--  Length and weight unit  -->
<div class="control-group">
	<label for="qtc_length_class" class="control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_LENGTH_VENDOR_UNIT_DESC'), JText::_('COM_QUICK2CART_LENGTH_VENDOR_UNIT'), '', JText::_('COM_QUICK2CART_LENGTH_VENDOR_UNIT'));?>
	</label>
	<div class="controls">
		<?php
			if (!empty($this->legthList))
			{
				echo $this->legthList;
			}
			else
			{
				echo JText::_('COM_QUICK2CART_NO_LENGTH_VENDOR_UNITS');
			}
		?>
	</div>
</div>
<div class="control-group">
	<label for="qtc_weight_class" class="control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_WEIGHT_VENDOR_UNIT_DESC'), JText::_('COM_QUICK2CART_WEIGHT_VENDOR_UNIT'), '', JText::_('COM_QUICK2CART_WEIGHT_VENDOR_UNIT'));?>
	</label>
	<div class="controls">
		<?php
			if (!empty($this->weigthList))
			{
				echo $this->weigthList;
			}
			else
			{
				echo JText::_('COM_QUICK2CART_NO_WEIGHT_VENDOR_UNITS');
			}

		?>
	</div>
</div>

<!-- Default tax and shipping profile -->
<div class="control-group">
	<label class="control-label" for="taxprofile_id">
		<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_VEN_TAXPROFILE_DESC'), JText::_('COM_QUICK2CART_VEN_TAXPROFILE'), '', JText::_('COM_QUICK2CART_VEN_TAXPROFILE'));?>
	</label>

	<div class="controls qtc_shipProfileList">
		<span id="qtc_shipProfileSelListWrapper">
		<?php
		if ($isTaxationEnabled  && !empty($this->storeinfo[0]->id))
		{
			$defaultProfile = !empty($this->storeinfo[0]->taxprofile_id) ? $this->storeinfo[0]->taxprofile_id : '';
			echo $tax_listSelect = $storeHelper->getStoreTaxProfilesSelectList($this->storeinfo[0]->id, $defaultProfile, $fieldName = 'taxprofile_id',$fieldClass = '', $fieldId = 'taxprofile_id');

			if (empty($tax_listSelect))
			{
				echo JText::_('COM_QUICK2CART_VEN_U_NEED_TO_SETUP_TAXPROFILE_FIRST');
			}
		}
		else
		{
			echo JText::_('COM_QUICK2CART_VEN_U_NEED_TO_SETUP_TAXPROFILE_FIRST');
		}

		?>
		</span>
	</div>
</div>
<?php
if ($isShippingEnabled)
{
?>
<!-- Default tax and shipping profile -->
<div class="control-group">
	<label class="control-label" for="qtc_shipProfileSelList">
		<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_VEN_SHIPPROFILE_DESC'), JText::_('COM_QUICK2CART_VEN_SHIPPROFILE'), '', JText::_('COM_QUICK2CART_VEN_SHIPPROFILE'));?>
	</label>

	<div class="controls qtc_shipProfileList">
		<span id="qtc_shipProfileSelListWrapper">
		<?php

		if (!empty($this->storeinfo[0]->id))
		{
			// Here default_store_id - before saving the item, value =first store id
			// While edit default_store_id- item's store id
			$defaultProfile = !empty($this->storeinfo[0]->shipprofile_id) ? $this->storeinfo[0]->shipprofile_id : '';
			// Get qtc_shipProfileSelList
			echo $shipProfileSelectList = $qtcshiphelper->qtcLoadShipProfileSelectList($this->storeinfo[0]->id, $defaultProfile);
		}
		else
		{
			echo JText::_('COM_QUICK2CART_VEN_U_NEED_TO_SETUP_SHIPPROFILE_FIRST');
		}
		?>
		</span>
	</div>
</div>
<?php
} ?>

