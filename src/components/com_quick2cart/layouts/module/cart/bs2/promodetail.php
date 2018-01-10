<?php
// <!-- FOr promotion start -->
$promotions = $displayData->promotions;
$tprice = $displayData->tprice;
$ccode = $displayData->ccode;

$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

if (!class_exists('comquick2cartHelper'))
{
	JLoader::register('comquick2cartHelper', $path);
	JLoader::load('comquick2cartHelper');
}

$comquick2cartHelper = new comquick2cartHelper;
$maximumDiscount = 0;
$maxDisPromo =  array();
$showIfDiscountPresent = "q2c-display-none";
$data = new stdClass;
$data->promotions = $promotions;

if (!empty($data->promotions) && !empty($data->promotions->maxDisPromo))
{
	$maxDisPromo = $data->promotions->maxDisPromo;
	$showIfDiscountPresent = "";
}

if (!empty($maxDisPromo) && !empty($maxDisPromo->applicableMaxDiscount))
{
	$tprice = $tprice - $maxDisPromo->applicableMaxDiscount;
?>
	<tr class="highlightedrow active">
		<td class="">
			<div>
			<strong><?php echo JText::_('MOD_QUICK2CART_PROMOTION_DICOUNT');?></strong>
			</div>
				(
				<?php
				if (!empty($ccode))
				{
					?>
					<small><strong><?php echo JText::_('COM_MOD_QUICK2CART_CUPCODE') . " : " . $ccode . " "; ?> </strong>
					</small>
					<?php
				}
				?>
				<span class="promDicountTitle"><small>
					<?php echo $maxDisPromo->name ?>
				</small></span>
				)
		</td>
		<td class="">
			<strong><span id= "" >
				<?php echo $comquick2cartHelper->getFromattedPrice(number_format($maxDisPromo->	applicableMaxDiscount,2)); ?>
				</span>
			</strong>
		</td>
	</tr>

	<tr class="highlightedrow active">
		<td class="">
			<b>
				<?php echo JText::_("MOD_QUICK2CART_AMT_AFTER_PROMOTION");?>
			</b>
		</td>
		<td class="">
		<b>
		<?php echo $comquick2cartHelper->getFromattedPrice(number_format($tprice,2)); ?>
		</b>
		</td>
	</tr>
<?php
}
// <!-- FOr promotion End -->
?>

<tr class="active">
	<td colspan="2">
		<?php
		$msg_order_js = "'".JText::_('QTC_CART_EMPTY_CONFIRMATION')."','".JText::_('QTC_CART_EMPTIED')."'";
		?>
		<?php
			$jinput = JFactory::getApplication()->input;
			// Used while updating module on add to cart ajax
			$AjaxUpdateCurrentURI = $jinput->post->get("currentPageURI",'',"STRING");

			if (empty($AjaxUpdateCurrentURI))
			{
				$baseUrl = $jinput->server->get('REQUEST_URI', '', 'STRING');
			}
			else
			{
				$baseUrl = $AjaxUpdateCurrentURI;
			}
		?>
		<!-- FOr promotion End -->
		<!-- Show apply ccode row when no ccode in session-->
		<?php
		if (empty($ccode))
		{
		?>
		<div class="input-append modulecartview" id="mod_applyCopWrapper">
			<input type="text" class="input input-small qtc_coupon_input_box" id="cart_coupon_code" name="cop" value="" placeholder="<?php echo JText::_('COM_MOD_QUICK2CART_CUPCODE');?>" style="max-width:180px;"></input>
			<button  class="btn btn-default"  onclick="cart_applycoupon('<?php echo JText::_('COM_MOD_QUICK2CARTENTER_COP_COD')?>', '.modulecartview #cart_coupon_code')"><?php echo JText::_('COM_MOD_QUICK2CART_APPLY');?></button>
		</div>
		<div class="clearfix"></div>
		 <?php
		}
		else
		{
		?>
		<span class="label label-success qtcHandPointer" onclick="remove_cop()">
			<?php echo JText::_("MOD_QUICK2CART_REMOVE_COP");?>
			<i class="<?php echo QTC_ICON_REMOVE ?>"></i>

		</span>

		<?php
		}
		?>

	</td>
</tr>

