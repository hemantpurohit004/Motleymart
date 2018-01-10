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

?>
<div class="clearfix"></div>
<div class="">

	<!-- for Length & weight class option -->
	<?php
	//$qtc_shipping_opt_status = $params->get('shipping');
	$qtc_shipping_opt_style = ($qtc_shipping_opt_status==1) ? "display:block" : "display:none";
	$storeHelper = $comquick2cartHelper->loadqtcClass(JPATH_SITE.DS."components".DS."com_quick2cart".DS."helpers".DS."storeHelper.php","storeHelper");
	$legthList = (array) $storeHelper->getStoreShippingLegthClassList($storeid = 0);
	$weigthList = (array) $storeHelper->getStoreShippingWeigthClassList($storeid = 0);

	if ($isTaxationEnabled)
	{	?>
		<div class="control-group">
			<label class="control-label" for="qtcTaxprofileSel">
				<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_TAXPROFILE_DESC_TOOLTIP'), JText::_('COM_QUICK2CART_TAXPROFILE_DESC'), '', JText::_('COM_QUICK2CART_TAXPROFILE_DESC'));?>
			</label>
			<div class="controls taxprofile">&nbsp;</div>
			<div class="clearfix">&nbsp;</div>
		</div>
	<?php
	} 	?>
	<?php
	if ($qtc_shipping_opt_status)
	{
	?>
	<div class='control-group ' style="<?php echo $qtc_shipping_opt_style;?>">
		<label class="control-label" for="qtc_item_length">
			<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PROD_DIMENSION_LENGTH_LABEL_TOOLTIP'), JText::_('COM_QUICK2CART_PROD_DIMENSION_LENGTH_LABEL'), '', JText::_('COM_QUICK2CART_PROD_DIMENSION_LENGTH_LABEL'));?>
		</label>
		<div class="controls">
			<input type="text" class=" input-mini" Onkeyup='checkforalpha(this,46,<?php echo $entered_numerics; ?>);' name='qtc_item_length' id='qtc_item_length' value='<?php echo (!empty($minmaxstock->item_length)) ?  number_format($minmaxstock->item_length, 2, '.', '') : '' ?>' placeholder="<?php echo JText::_('COM_QUICK2CART_LENGTH_HINT') ?>" />
			x
			<input type="text" class=" input-mini" Onkeyup='checkforalpha(this,46,<?php echo $entered_numerics; ?>);' name='qtc_item_width' id='qtc_item_width' value='<?php  echo (!empty($minmaxstock->item_width)) ?  number_format($minmaxstock->item_width, 2, '.', '') : '' ?>' placeholder="<?php echo JText::_('COM_QUICK2CART_WIDTH_HINT') ?>" />
			x
			<div class="input-append ">
				<input type="text" class=" input-mini" Onkeyup='checkforalpha(this,46,<?php echo $entered_numerics; ?>);' name='qtc_item_height' id='qtc_item_height' value='<?php echo (!empty($minmaxstock->item_height)) ?  number_format($minmaxstock->item_height, 2, '.', '') : '' ?>' placeholder="<?php echo JText::_('COM_QUICK2CART_HEIGHT_HINT') ?>" />

				<?php
					// Get store configued length id.
					// The get default value

					$lenUniteId = 0;
					if (isset($minmaxstock) && $minmaxstock->item_length_class_id)
					{
						// While edit used item class id
						$lenUniteId = $minmaxstock->item_length_class_id;
					}
					elseif (isset($this->defaultStoreSettings['length_id']))
					{
						// If for store default length unite has set
						$lenUniteId = $this->defaultStoreSettings['length_id'];
					}

					$lenUnitDetail = $storeHelper->getProductLengthDetail($lenUniteId);
					?>

				<?php
					$lenUnitDetail = $storeHelper->getProductLengthDetail($lenUniteId);
					echo JHtml::_('select.genericlist', $this->lengthClasses, "length_class_id", '', "id", "title", $lenUnitDetail['id']);
				?>

			</div>
		</div>
	</div>


	<!-- weight unit-->
	<div class='control-group qtc_item_weight' style="<?php echo $qtc_shipping_opt_style;?>">
		<label class="control-label" for="qtc_item_weight">
			<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PROD_DIMENSION_WEIGTH_LABEL_TOOLTIP'), JText::_('COM_QUICK2CART_PROD_DIMENSION_WEIGTH_LABEL'), '', JText::_('COM_QUICK2CART_PROD_DIMENSION_WEIGTH_LABEL'));?>
		</label>
		<div class="controls">
			<div class="input-append ">
				<input type="text" class=" input-mini" Onkeyup='checkforalpha(this,46,<?php echo $entered_numerics; ?>);' name='qtc_item_weight' id="qtc_item_weight" value='<?php if (isset($minmaxstock)) echo number_format($minmaxstock->item_weight, 2, '.', '');?>' />
				<?php
					// Get store configued length id.
					// The get default value

					$weightUniteId = 0;
					if (isset($minmaxstock) && $minmaxstock->item_weight_class_id)
					{
						// While edit used item class id
						$weightUniteId = $minmaxstock->item_weight_class_id;
					}
					elseif (isset($this->defaultStoreSettings['weight_id']))
					{
						// If for store default length unite has set
						$weightUniteId = $this->defaultStoreSettings['weight_id'];
					}

					$weightUniteDetail = $storeHelper->getProductWeightDetail($weightUniteId);
					echo JHtml::_('select.genericlist', $this->weightClasses, "weigth_class_id", '', "id", "title", $weightUniteDetail['id']);
					?>
			</div>
		</div>
	</div>
	<!-- END for Legth & weigth class option -->
	<!-- Shipping Profile-->
	<div class="control-group">
		<label class="control-label" for="qtc_shipProfileSelList">
			<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_S_SEL_SHIPPROFILE_TOOLTIP'), JText::_('COM_QUICK2CART_S_SEL_SHIPPROFILE'), '', JText::_('COM_QUICK2CART_S_SEL_SHIPPROFILE'));?>
		</label>

		<div class="controls qtc_shipProfileList">
			<span id="qtc_shipProfileSelListWrapper">
			<?php
				// Here default_store_id - before saving the item, value =first store id
				// While edit default_store_id- item's store id
				$defaultProfile = !empty($this->itemDetail['shipProfileId']) ? $this->itemDetail['shipProfileId'] : '';
				$shipDefaultStore = !empty($this->itemDetail['store_id']) ? $this->itemDetail['store_id'] : $this->store_id;
				// Get qtc_shipProfileSelList
				echo $shipProfileSelectList = $qtcshiphelper->qtcLoadShipProfileSelectList($shipDefaultStore, $defaultProfile);
			?>
			</span>
		</div>
	</div>

	<?php
	}
	?>
</div>
