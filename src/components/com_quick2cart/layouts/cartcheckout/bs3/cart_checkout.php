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
$data = $displayData;
$showoptioncol = 0;

if (isset($data->showoptioncol))
{
	$showoptioncol = $data->showoptioncol;
}

$path = JPATH_SITE . '/components/com_quick2cart/helpers/promotion.php';

if (!class_exists('PromotionHelper'))
{
	JLoader::register('PromotionHelper', $path);
	JLoader::load('PromotionHelper');
}

$PromotionHelper = new PromotionHelper;
$ccode = isset($data->coupon)?$data->coupon : array() ;
$coupanexist = empty($ccode)?0:1;

// TO use lanugage cont in javascript
JText::script('COM_QUICK2CART_CHECKOUT_ITEM_UPDTATED_SUCCESS', true);
JText::script('COM_QUICK2CART_CHECKOUT_ITEM_UPDTATED_FAIL', true);

?>
	<!-- CART DETAIL START-->
<div class="qtc_chekout_cartdetailWrapper  broadcast-expands" ><!-- LM Removed qtcAddBorderToWrapper -->
	<!--<legend><?php echo JText::_('QTC_CART')?>&nbsp;<small><?php echo JText::_('QTC_CART_DESC')?></small></legend> -->
	<?php $align_style='align="right"'; ?>
	<div>
		<?php echo (!empty($this->beforecart))?$this->beforecart:""; ?>
	</div>

	<?php
	$comparams = JComponentHelper::getParams('com_quick2cart');
	$currencies=$comparams->get('addcurrency');
	$currencies_sym=$comparams->get('addcurrency_sym');
	$comquick2cartHelper = new comquick2cartHelper;
	$default = $comquick2cartHelper->getCurrencySession();

	$option = array();

	if ($currencies)
	{
	?>
		<!-- ///drop down  -->
		<div class="qtcChekoutCurrSelect" style="">
			<br>
			<?php
			$multi_currs = explode(",",$currencies);
			$currencies_syms = explode(",",$currencies_sym);

			foreach ($multi_currs as $key => $curr)
			{
				if (!empty($currencies_syms[$key]) )
				{
					$currtext = $currencies_syms[$key];
				}
				else
				{
					$currtext = $curr;
				}

				$option[] = JHtml::_('select.option', trim($curr), trim($currtext));
			}

			$cur_display = '';

			if (count($multi_currs) == 1)
			{
				$cur_display = 'style="display:none"';
			}
			?>
			<div <?php echo $cur_display;?> > <span><?php echo JText::_('QTC_SEL_CURR');?> </span>
			<?php
			echo JHtml::_('select.genericlist',$option, "multi_curr", 'class="" onchange=" document.getElementById(\'task\').value=\'cartcheckout.setCookieCur\';document.adminForm.submit();" autocomplete="off" ', "value", "text", $default );
			?>
			<br><br>
			</div>
	</div>
	<!-- ///drop down END -->
	<div style="clear:both;"></div>
	<?php
	}

	$showqty_style = "";
	$showqty = $comparams->get('qty_buynow', 1);

	if (empty($showqty))
	{
		$showqty_style = "display:none;";
	}
	?>
	<div class="table-responsive">
		<table class="table table-checkout qtc-table ">
			<thead>
				<tr class="qtcborderedrow">
					<th class="cartitem_name"  align="left"><b><?php echo JText::_( 'QTC_CART_TITLE' ); ?> </b></th>
					<?php
					if ($showoptioncol == 1)
					{
					?>
					<th class="cartitem_opt " 	align="left"><b><?php echo JText::_( 'QTC_CART_OPTS' ); ?></b> </th>
					<?php
					}
					?>

					<th class="cartitem_price rightalign"	><b><?php echo JText::_( 'QTC_CART_PRICE' ); ?></b> </th>
					<th style="<?php echo $showqty_style; ?>" class="cartitem_qty rightalign" 	><b><?php echo JText::_( 'QTC_CART_QTY' ); ?></b> </th>
					<th class="cartitem_tprice rightalign" 	<?php echo $align_style ?>><b><?php echo JText::_( 'QTC_CART_TOTAL_PRICE' ); ?> </b></th>
					<th style="width:70px;"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$tprice = 0;
				$store_array = array();
				$params = JComponentHelper::getParams('com_quick2cart');
				$multivendor_enable=$params->get('multivendor');
				$storeHelper = new storeHelper();

				foreach ($data->cart as $cart)
				{
					// IF MUTIVENDER ENDABLE
					if (!empty($multivendor_enable))
					{
						if (!in_array($cart['store_id'], $store_array))
						{
							$store_array[]=$cart['store_id'];
							$storeinfo=$comquick2cartHelper->getSoreInfo($cart['store_id']);
							$storeLink   = $storeHelper->getStoreLink($cart['store_id']);
							?>
							<tr class="info">
								<td colspan="<?php echo (($showoptioncol == 1) ?"7" : "6" ); ?>" >
									<strong><a href="<?php echo $storeLink; ?>"><?php echo $storeinfo['title'];?></a></strong>
								</td>
							</tr>
						<?php
						}
					}?>
					<?php
					$product_link = $comquick2cartHelper->getProductLink($cart['item_id']);
					?>
					<tr class="qtcborderedrow">
						<td class="cartitem_name" >
							<input class="inputbox cart_fields" id="" name="<?php echo 'cartDetail[' . $cart['id'] . '][cart_item_id]'; ?>" type="hidden" value="<?php echo $cart['id']; ?>" >

							<?php	$images = $cart['item_images'];
							if (empty($product_link))
							{
								echo $cart['title'];
							}
							else
							{
							?>
								<a href="<?php echo $product_link;?>"><?php echo $cart['title']; ?></a>
							<?php
							}
							?>
						</td>

							<!-- Product Options (Show editable cart attributes) -->
							<?php

						if($showoptioncol==1)
						{ ?>
							<td class="cartitem_opt" >
								<?php

								//$cart['prodAttributeDetails'] = '';
								if (!empty($cart['prodAttributeDetails']))
								{
									// seleted product attributes ids
									$product_attributes = explode(',', $cart['product_attributes']);

									// Show each product attribute
									foreach ($cart['prodAttributeDetails'] as $key=>$attribute)
									{
										?>
										<div class="qtc_bottom ">
											<span class=""><?php echo $attribute->itemattribute_name; ?></span>
											<input class="" id="" name="<?php echo 'cartDetail[' . $cart['id'] . '][attrDetail][' . $attribute->itemattribute_id . '][type]'; ?>" type="hidden" value="<?php echo $attribute->attributeFieldType ?>" >


											<?php
											// For text type attribute
											if (! empty($attribute->attributeFieldType) && $attribute->attributeFieldType == 'Textbox')
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

												<input type="text"
													name="<?php echo 'cartDetail[' . $cart['id'] . '][attrDetail][' . $attribute->itemattribute_id . '][value]' ?>"
													class="input input-small"
													value ="<?php echo $value; ?>"
												/>
												<!-- Attribute option id -->
												<input type="hidden" name="<?php echo 'cartDetail[' . $cart['id'] . '][attrDetail][' . $attribute->itemattribute_id . '][itemattributeoption_id]' ?>" class="input input-small" value ="<?php echo $itemattributeoption_id; ?>" />
											<?php
											}
											else
											{
												$attributeData = array();
												foreach ($attribute->optionDetails as $optionDetail)
												{
													if (in_array($optionDetail->itemattributeoption_id, $product_attributes))
													{
														$attributeData['default_value'] = $optionDetail->itemattributeoption_id;
														break;
													}
												}

												$productHelper = new productHelper();
												$attributeData['itemattribute_id'] = $attribute->itemattribute_id;
												$attributeData['fieldType'] = $attribute->attributeFieldType;
												$attributeData['product_id'] = $cart['item_id'];
												$attributeData['attribute_compulsary'] = $attribute->attribute_compulsary;

												if (!empty($attributeData['default_value']))
												{
													$attrDetailsObject = $cart['product_attributes_values'][$attributeData['default_value']];
												}

												//$attributeData['field_name'] = 'attri_option'.$attrDetailsObject->cartitemattribute_id;
												$attributeData['field_name'] = 'cartDetail[' . $cart['id'] . '][attrDetail][' . $attribute->itemattribute_id . '][value]';

												// Generate field html (select box)
												$fieldHtml = $productHelper->getAttrFieldTypeHtml($attributeData);
												?>
													<?php echo $fieldHtml;?>

												<?php
											}
											// else end
											?>
										</div>
										<div class="qtcClearBoth">&nbsp;</div>
									<?php
									}
								}
								?>

							</td>
							<?php
						}
						?>

						<td class="cartitem_price rightalign" id="cart_price" name="cart_price[]">
							<div>
								<?php
								$original_prod_price = $pro_price=$cart['amt'] + $cart['opt_amt'];
								echo $comquick2cartHelper->getFromattedPrice(number_format($original_prod_price,2));?>
							</div>
						</td>
						<td style="<?php echo $showqty_style; ?>" class="cartitem_qty rightalign" >
							<?php
							$minmax=$comquick2cartHelper->getMinMax($cart['item_id']);
							$minmsg=JText::_( 'QTC_MIN_LIMIT_MSG' );
							$maxmsg= JText::_( 'QTC_MAX_LIMIT_MSG' );
							$qtc_min=isset($minmax['min_quantity'])?$minmax['min_quantity']:1;
							$qtc_max=isset($minmax['max_quantity'])?$minmax['max_quantity']:999;
							$caltotal_params="'".$cart['id'] ."',".$cart['amt'].",".$qtc_min.",".$qtc_max.",'".$minmsg."','".$maxmsg."'";
							?>
							<input type ="hidden" id="quantity_parmas_<?php echo $cart['id'];?>" value="<?php echo $caltotal_params ;?> " />

							<input id ="quantity_field_<?php echo $cart['id'];?>" class="cart_fields pull-right input qtc-input-small" id="cart_count" name="<?php echo 'cartDetail[' . $cart['id'] . '][cart_count]' ?>" type="text" value="<?php echo $cart['qty'];?>"  maxlength="3">
						</td>
						<td class="cartitem_tprice rightalign" <?php //echo $align_style ?> >
							<span id="cart_total_price<?php echo $cart['id'];?>"><?php echo $comquick2cartHelper->getFromattedPrice(number_format(($pro_price * $cart['qty']) ,2));  ?>
							</span>
							<?php
							$tprice = $tprice + ($pro_price * $cart['qty']);

							?>
							</td>
							<td>
						<div class="qtc_float_right">
							<span class="qtcHandPointer">
								<span class="qtcHandPointer qtcUpdateItemImg <?php echo QTC_ICON_REFRESH; ?>"   title="<?php echo JText::_('COM_QUICK2CART_UPDAE_CART_ITEM_DESC');?> " onclick="updateCartItemsAttribute('<?php echo $cart['id'];?>', '<?php echo $cart['item_id']; ?>')"> </span>
							</span>

							<span class="qtcHandPointer" onclick="removecart('<?php echo $cart['id'];?>');" >

								<span class="qtcHandPointer qtcUpdateItemImg <?php echo QTC_ICON_REMOVE; ?>"   title="<?php echo JText::_('QTC_CKOUT_REMVOVE_FROM_CART'); ?>" onclick="updateCartItemsAttribute('<?php echo $cart['id'];?>', '<?php echo $cart['item_id']; ?>')"> </span>

							<?php //echo "&times;" ; // JHtml::tooltip(JText::_('QTC_CKOUT_REMVOVE_FROM_CART'), '', '', '&times;' ) ;?>

							</span>
						</div>
						</td>
					</tr>

				<?php
				} // END OF FOR EACH
				?>
				<!-- LM End of 1st table-->
				<?php
				$totalprice = $tprice;
				?>
		<tr class="qtcborderedrow highlightedrow">
			<?php
			$col = 2;

			if ($showoptioncol == 1)
			{
				$col = 3;
			}
			?>

			<?php
			$msg_order_js = "'" . JText::_('QTC_CART_EMPTY_CONFIRMATION') . "','".JText::_('QTC_CART_EMPTIED') . "'";
			?>
			<td colspan = "<?php echo $col; ?>">
				<div class = "form-inline">
				<input type="checkbox" id = "coupon_chk"  autocomplete="off" name = "coupon_chk" value="" size= "10" onchange="show_cop(<?php echo $coupanexist; ?>)" <?php echo ($ccode) ? 'checked' : '' ; ?>  />
				<label class="checkbox-inline">
					<?php echo JText::_('QTC_HAVE_COP');?>
				</label>
				<span id = "cop_tr">
					<span class="input-group">
						<input type="text" class="form-control input input-medium"   id = "coupon_code" name="cop" value="<?php echo $ccode ?>"    placeholder="<?php echo JText::_('QTC_CUPCODE');?>"/>
						<span class="input-group-btn">
							<input type="button"  class="btn btn-default"  onclick="cart_applycoupon('<?php echo JText::_('QTC_ENTER_COP_COD')?>','#coupon_code')" value="<?php echo JText::_('QTC_APPLY');?>" >
						</span>
					</span>
				</span>
			</div>
			</td>
			<td  class="cartitem_tprice_label rightalign <?php echo !empty($showIfDiscountPresent) ? $showIfDiscountPresent : '' ; ?>" >
				<strong><?php echo JText::_( 'QTC_TOTALPRICE' ); ?></strong>
			</td>
			<td class="cartitem_tprice rightalign" ><strong><span name="total_amt" id="total_amt"><?php echo $comquick2cartHelper->getFromattedPrice(number_format($totalprice,2)); ?></span></strong>
			</td>
			<td></td>
		</tr>

		<?php $col = 2;
		if ($showoptioncol == 1)
		{
			$col = 3;
		}
		?>

		<!-- FOr promotion start -->
		<?php
			$maximumDiscount = 0;
			$maxDisPromo =  array();
			$showIfDiscountPresent = "q2c-display-none";

			if (!empty($data->promotions) && !empty($data->promotions->maxDisPromo))
			{
				$maxDisPromo = $data->promotions->maxDisPromo;
				$showIfDiscountPresent = "";
			}
		?>
		<?php
		if (!empty($maxDisPromo))
		{
			$tprice = $tprice - $maxDisPromo->applicableMaxDiscount;
		?>
			<tr class="dis_tr qtcborderedrow highlightedrow" >

				<td colspan = "<?php echo ($col + 1);?>" class="cartitem_tprice_label rightalign">
					<div>
					<strong><?php echo JText::_('COM_QUICK2CART_PROMOTION_DICOUNT');?></strong>
					</div>
					(
						<?php
						if (!empty($ccode))
						{
							?>
							<small><strong><?php echo JText::_('QTC_DISCOUNT_CODE') . " : " . $ccode . " "; ?> </strong>
							</small>
							<?php
						}
						?>
						<span class="promDicountTitle"><small>
							<?php echo $maxDisPromo->name ?>
						</small></span>
					)


				</td>
				<td class="cartitem_tprice rightalign"  >
					<strong><span id= "dis_cop" >
						<?php echo $comquick2cartHelper->getFromattedPrice(number_format($maxDisPromo->	applicableMaxDiscount,2)); ?>
						</span>
					</strong>

				</td>
				<td></td>
			</tr>

		<?php
		}
		?>
		<!-- FOr promotion End -->
		<?php
		$col = 2;
		if ($showoptioncol == 1)
		{
			$col = 3;
		}
		?>
		<tr class="dis_tr qtcborderedrow highlightedrow <?php echo !empty($showIfDiscountPresent) ? $showIfDiscountPresent : '' ;  ?>"   >
			<td colspan = "<?php echo $col;?>"></td>
			<td class="cartitem_tprice_label rightalign"  ><strong><?php echo JText::_('QTC_NET_AMT_PAY');?></strong></td>
			<td class="cartitem_tprice rightalign"  ><strong><span id= "dis_amt" ><?php echo $comquick2cartHelper->getFromattedPrice(number_format($tprice,2)); ?></strong></span>
			</td>
			<td></td>
		</tr>
		<?php
		// taxation plugin
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('qtctax');//@TODO:need to check plugim type..
		$taxresults='';// $dispatcher->trigger('addTax',array($tprice));//Call the plugin and get the result

		if (!empty($taxresults))
		{
			$tax_total = 0;

			foreach ($taxresults as $tax)
			{
				if(!empty($tax))
				{
				?>
					<!-- doubt ***** -->
					<?php $col = 2;

					if ($showoptioncol == 1)
					{
						$col = 3;
					}?>
					<tr class=" qtcborderedrow highlightedrow">
						<td colspan = "<?php echo $col;?>" ></td>
						<td class="cartitem_tprice_label rightalign" ><?php echo JText::sprintf('QTC_TAX_AMT_PAY',$tax[0]); ?></td>
						<td class="cartitem_tprice rightalign"  ><span id= "tax_amt" ><?php echo $comquick2cartHelper->getFromattedPrice(number_format($tax[1],2)); ?></span>
						<input type="hidden" class="inputbox" value="<?php echo $tax[0]; ?>"	name="tax[val][]"	id="tax[val][]">
						<input type="hidden" class="inputbox" value="<?php echo $tax[1]; ?>"	name="tax[amt][]"	id="tax[amt][]">

						</td>
						<td></td>
					</tr>
				<?php
				$tax_total += $tax[1];
				}
			}

			if ($tax_total)
			{
				$taxval = $comquick2cartHelper->calamt($tprice,$tax_total);
				?>
					<tr class=" qtcborderedrow highlightedrow">
						<td colspan = "<?php echo $col;?>" ></td>
						<td class="cartitem_tprice_label rightalign"   ><?php echo JText::_('QTC_TAX_TOTAL_AMT_PAY');?></td>
						<td class="cartitem_tprice rightalign" >
							<span id= "after_tax_amt" ><?php echo $comquick2cartHelper->getFromattedPrice(number_format($taxval,2)); ?></span>
						</td>
						<td></td>
					</tr>
				<?php
			}
			else
			{
				$taxval = $tprice;
			}
		}
		else
		{
			$taxval = $tprice;
		}
		?>
			</tbody>

		</table>
		</div>
		<div>
			<input type="hidden" class="inputbox" value="<?php echo $taxval; ?>" name="total_after_tax"	id="total_after_tax">
			<?php echo (!empty($this->aftercart))?$this->aftercart:'';?>
		</div>
		<?php
			$jinput = JFactory::getApplication();
			$baseUrl = $jinput->input->server->get('REQUEST_URI', '', 'STRING');
		?>
		<button type="button" class="btn btn-default btn-danger btn-sm" onclick="emptycart(<?php echo $msg_order_js . ",'" . $baseUrl."'"; ?>);" ><i class="<?php echo Q2C_ICON_TRASH; ?> <?php echo Q2C_ICON_WHITECOLOR; ?> <?php echo Q2C_ICON_WHITECOLOR; ?>"></i>&nbsp;<?php echo JText::_('QTC_BTN_EMPTY_CART')?></button>
</div>
