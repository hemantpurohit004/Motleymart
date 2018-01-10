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

// Don't render any module html when cart is empty
if ($hideOnCartEmpty == 1 && empty($cart))
{
	return;
}

$comquick2cartHelper = new comquick2cartHelper();
// Load cart model
$path                = JPATH_SITE . "/components/com_quick2cart/models/cart.php";
$comquick2cartHelper->loadqtcClass($path, 'Quick2cartModelcart');
$Quick2cartModelcart = new Quick2cartModelcart;

$lang = JFactory::getLanguage();
$lang->load('mod_quick2cart', JPATH_ROOT);
$comparams = JComponentHelper::getParams( 'com_quick2cart' );
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true) . '/components/com_quick2cart/assets/css/quick2cart.css' );

$Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=cart');
?>

<script type="text/javascript">
	function divHideShow(key)
	{
		/* toggle is for changing state - from hide to visible and vice versa*/
		techjoomla.jQuery("#qtc_showhide"+key).slideToggle('', '', function()
		{
			var isVisible = techjoomla.jQuery('#qtc_showhide'+key).is(':visible');
			var className = techjoomla.jQuery('#qtc_item_id'+key).attr('class');

			if (isVisible)
			{
				techjoomla.jQuery('#qtc_item_id'+key).removeClass('qtc_icon-plus').addClass('qtc_icon-minus');
			}
			else
			{
				techjoomla.jQuery('#qtc_item_id'+key).removeClass('qtc_icon-minus').addClass('qtc_icon-plus');
			}
		});
	}
</script>

<div class="<?php echo Q2C_WRAPPER_CLASS . ' ' . $moduleclass_sfx ?> qtcModuleWrapper" >
	<?php
	if (isset($beforecartmodule))
	{
		echo $beforecartmodule;
	}

	$default_currency = $comquick2cartHelper->getCurrencySession();

	$comparams = JComponentHelper::getParams('com_quick2cart');
	$currencies = $comparams->get('addcurrency');
	$currencies_sym = $comparams->get('addcurrency_sym');

	//"INR,USD,AUD";
	//@TODO get this from the component params
	$multi_curr = $currencies;
	$option = array();
	$currcount = 0;

	if ($multi_curr)
	{
		$multi_currs = explode(",", $multi_curr);
		$currcount = count($multi_currs);
		$currencies_syms = explode(",", $currencies_sym);

		foreach ($multi_currs as $key => $curr)
		{
			if (!empty($currencies_syms[$key]))
			{
				$currtext = $currencies_syms[$key];
			}
			else
			{
				$currtext = $curr;
			}

			$option[] = JHtml::_('select.option', trim($curr), trim($currtext));
		}

		if ($currcount>1)
		{
			?>
			<form method="post" name="qtc_mod_form" id="adminForm2" action="index.php?option=com_quick2cart&task=cartcheckout.setCookieCur">
				<div class="row-fluid">
					<div class="span6">
						<?php echo JText::_('QTC_SEL_CURR');?>
					</div>
					<div class="span6">
					<?php
					//write a func change_curr(); in order.js file to set session for Currency via Ajax.
					echo JHtml::_('select.genericlist', $option, "multi_curr", 'class="" onchange=" document.qtc_mod_form.submit();" autocomplete="off"', "value", "text", $default_currency );
					?>
					</div>

				</div>

				<input type="hidden" name="qtc_current_url" value="<?php echo JUri::getInstance()->toString();?>"/>

			</form>
			<?php
		}
	}
	?>

	<div>
		<div class="qtcClearBoth"></div>
		<table class="table table-condensed ">
			<!-- detailed view of modulecart Table -->
				<!-- Lakhan -- added condition-- Hide table heading if cart is empty -->
				<?php
				if (!empty($cart))
				{
				?>
				<thead class="qtc_cart_module_head">
					<tr class="qtcborderedrow">
						<th><?php echo JText::_('QTC_MOD_ITEM');?></th>
						<th class="rightalign" width="28%"><?php echo JText::_('QTC_MOD_PRICE');?></th>
					</tr>
				</thead>
				<?php
					}
				?>
			<tbody class="qtc_modulebody">
				<?php
				// IF cart is empty
				if (!empty($cart))
				{
					$doc = JFactory::getDocument();
					$root_url = JUri::root();
					$Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=cart');
					$tprice = 0;
					$totqty = 0;
					$cart_item_array = array();

					foreach ($cart as $key=>$item)
					{
						if (!empty($item['item_id']))
						{
							// Item details
							$item_details = $Quick2cartModelcart->getItemRec($item['item_id']);
						}

						$cart_item_array[] = $item['item_id'];
						$showoptioncol = 0;

						if (!empty($item['options']))
						{
							// Atleast one found then show
							$showoptioncol = 1;
						}
						?>

						<tr class="qtcborderedrow">
							<td>
								<?php
								if (!empty($showoptioncol))
								{
									?>
									<i class="qtc_icon-plus" id="qtc_item_id<?php echo $key?>" onclick="divHideShow('<?php echo $key;?>')"></i>
									<?php
								}

								echo $item['title']."( ". $item['qty'] ." )"?>
							</td>
							<td class=""><?php echo $comquick2cartHelper->getFromattedPrice($item['tamt']);?></td>
						</tr>

						<?php
						$attoptionIds = $cart[$key]['product_attributes'];
						$option = $cart[$key]['options'];

						$attoptionIds = array_filter(explode(',', $attoptionIds), "trim");
						$option = array_filter(explode(',', $option), "trim");

						// Getprefix return as "+ 5.00  USD"
						// model is acquired in mod_qick2cart.php
						$prefix = $model->getPrefix($attoptionIds);
						?>

						<tr class="qtc_showhide qtcborderedrow" id="qtc_showhide<?php echo  $key;?>" style="display:none;" >
							<td colspan=2>
								<?php
								foreach ($option as $k=>$op)
								{
									?>
									<div>
										<?php echo $op . " " . $prefix[$k];?>
									</div>
									<?php
								}
								?>
							</td>
						</tr>

						<?php
						$tprice += $item['tamt'];
						$totqty += $item['qty'];
					}

					$msg_order_js = "'".JText::_('QTC_CART_EMPTY_CONFIRMATION')."','".JText::_('QTC_CART_EMPTIED')."'";
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
					<tr class="highlightedrow cartitem_tprice active">
						<td >
							<strong><?php echo JText::_('MOD_QUICK2CART_SUBTOTAL_TOTALPRICE'); ?></strong>
						</td>
						<td class="rightalign" ><strong><span name="total_amt" id="total_amt"><?php echo $comquick2cartHelper->getFromattedPrice(number_format($tprice,2)); ?></span></strong>
						</td>
					</tr>
					<?php
					// <!-- FOr promotion start -->
					$layoutData = new stdclass;
					$layoutData->promotions = $promotions;
					$layoutData->tprice = $tprice;
					$layoutData->ccode = $coupon;
					$layout = new JLayoutFile("module.cart.bs2.promodetail");
					echo $response = $layout->render($layoutData, array('debug' => true));
					// <!-- FOr promotion End -->
					?>
					<tr class="active">
						<td colspan="2">
							<div class="row-fluid">
							<button class="span6 btn btn-danger btn-small btn_margin pull-left " onclick="emptycart(<?php echo $msg_order_js . ",'" . $baseUrl."'"; ?>);" >
								<i class="icon-trash icon22-white22"></i>&nbsp;<?php echo JText::_('QTC_MOD_EMPTY_CART')?>
							</button>

							<?php
							$ckout_btname = (!empty($ckout_text)) ? $ckout_text : JText::_('QTC_CHKOUT'); ?>
							<button class="span6 btn btn-primary btn-small btn_margin pull-right " onclick="window.open('<?php echo JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&Itemid='.$Itemid);?>','_self')" >
								<i class="icon-chevron-right icon22-white22"></i>&nbsp;<?php echo $ckout_btname;?>
							</button>
							<div class="clearfix"></div>
							<!-- Differnt cart count -->
							<input type="hidden" name="qtc_cartDiffItemCount" id="qtc_cartDiffItemCount" value="<?php echo count($cart);?>">

							</div>
							<div class="clearfix"></div>
						</td>
					</tr>
				<?php
					if (!empty($aftercartdisplay))
					{
						?>
						<tr>
							<td colspan="2">
								<?php echo $aftercartdisplay; ?>
							</td>
						</tr>
						<?php
					}
				}
				else
				{
					?>
					<tr>
						<td colspan="2">
							<div class="well"><?php echo JText::_('QTC_MOD_CART_EMPTY_CART'); ?></div>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
</div>
