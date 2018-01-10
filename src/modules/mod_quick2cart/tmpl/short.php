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

$lang = JFactory::getLanguage();
$lang->load('mod_quick2cart', JPATH_ROOT);
$comparams = JComponentHelper::getParams( 'com_quick2cart' );
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true) . '/components/com_quick2cart/assets/css/quick2cart.css' );
$comquick2cartHelper = new comquick2cartHelper;
$Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=cart');
?>
<div class="qtcModuleWrapper <?php echo Q2C_WRAPPER_CLASS . ' ' . $moduleclass_sfx ?>" >
	<?php
	if (isset($beforecartmodule))
	{
		echo $beforecartmodule;
	}

	$default_currency = $comquick2cartHelper->getCurrencySession();
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
				<div class="row">
					<div class="col-sm-8 col-xs-12">
						<?php echo JText::_('QTC_SEL_CURR');?>
					</div>
					<div class="col-sm-4 col-xs-12">
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
		<table class="table table-condensed table-mod-cart qtc-table">
			<!-- detailed view of modulecart Table -->
				<!-- Lakhan -- added condition-- Hide table heading if cart is empty -->
				<?php
				if (!empty($cart))
				{
					/*
				?>
				<thead class="qtc_cart_module_head">
					<tr class="qtcborderedrow">
						<th><?php echo JText::_('QTC_MOD_ITEM');?></th>
						<th class="rightalign"><?php echo JText::_('QTC_MOD_PRICE');?></th>
					</tr>
				</thead>
				<?php
				*/
				}
				?>

			<tbody class="qtc_modulebody">
				<?php
				// IF cart is not empty
				if (!empty($cart))
				{
					$root_url = JUri::root();
					?>

					<tr>
						<td><?php echo JText::_('QTC_MOD_CART_ITEMS');?></td>
						<td><?php echo count($cart); ?></td>
					</tr>

					<?php
					$tprice = 0;
					$cart_item_array = array();

					foreach ($cart as $cart1)
					{
						$cart_item_array[] = $cart1['item_id'];
						$tprice += $cart1['tamt'];
					}

					?>
					<tr>
						<td><strong> <?php echo JText::_('MOD_QUICK2CART_SUBTOTAL_TOTALPRICE');?></strong> </td>
						<td width="28%"><span><?php echo $comquick2cartHelper->getFromattedPrice(number_format($tprice,2));?></span></td>
					</tr>

					<?php
					// <!-- FOr promotion start -->
					$layoutData = new stdclass;
					$layoutData->promotions = $promotions;
					$layoutData->tprice = $tprice;
					$layoutData->ccode = $coupon;
					$layout = new JLayoutFile("module.cart.bs3.promodetail");
					echo $response = $layout->render($layoutData, array('debug' => true));
					// <!-- FOr promotion End -->
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
					<tr class="active">
						<td colspan="2">
							<button class="btn btn-danger btn-sm btn_margin pull-left" onclick="emptycart(<?php echo $msg_order_js . ",'" . $baseUrl."'"; ?>);" >
								<i class="icon-trash icon-white"></i>&nbsp;<?php echo JText::_('QTC_MOD_EMPTY_CART')?>
							</button>

							<?php
							$Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=cart');
							$ckout_btname=(isset($ckout_text))?$ckout_text:JText::_('QTC_CHKOUT'); ?>
							<button class="btn btn-primary btn-sm btn_margin pull-right" onclick="window.open('<?php echo JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&Itemid='.$Itemid);?>','_self')" >
								<i class="icon-chevron-right icon-white"></i>&nbsp;<?php echo $ckout_btname;?>
							</button>
							<div class="clearfix"></div>
							<!-- Differnt cart count -->
							<input type="hidden" name="qtc_cartDiffItemCount" id="qtc_cartDiffItemCount" value="<?php echo count($cart);?>">
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
