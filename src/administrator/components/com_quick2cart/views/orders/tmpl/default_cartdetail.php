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
$this->params             = JComponentHelper::getParams('com_quick2cart');
$orderid = $this->orderid;
?>
<!-- Start Cart detail -->
<?php
if (in_array('cart', $order_blocks))
{?>
	<h4><?php echo JText::_("COM_QUICK2CART_CART_DETAILS")?></h4>
	<div>
		<?php
		$price_col_style = "style=\"" . (!empty($orders_email) ? 'text-align: right;' : '') . "\"";
		$showoptioncol = 0;
		$itemLevalTaxship = 0;
		$orderItemIds = array();

		// Atleast one found (Tax or ship) then show
		foreach ($this->orderitems as $citem)
		{
			if (!empty($citem->product_attribute_names) && $showoptioncol == 0)
			{
				$showoptioncol = 1;
				//break;
			}

			if ($itemLevalTaxship == 0)
			{
				if ($citem->item_tax > 0 || $citem->item_shipcharges > 0)
				{
					$itemLevalTaxship = 1;
				}
			}
		}

		?>
		<div class="table table-responsive qtc-table">
			<form action="" name="orderItemForm" id="orderItemForm" class=" form-validate " method="post">
				<table width="100%" class="table table-condensed table-bordered adminlist">
					<tr>
						<th class="cartitem_num" width="5%"  style="<?php echo ($orders_email)?'text-align: left;' :'';  ?>" ><?php echo JText::_('QTC_NO'); ?></th>
						<th class="cartitem_name"  style="<?php echo ($orders_email)?'text-align: left;' :'';  ?>" ><?php echo  JText::_('QTC_PRODUCT_NAM'); ?></th>
						<?php
						if ($showoptioncol == 1)
						{ ?>
							<th class="cartitem_opt"  style="<?php echo ($orders_email)?'text-align: left;' :'';  ?>" ><?php echo JText::_('QTC_PRODUCT_OPTS'); ?></th>
						<?php
						} ?>
						<th class="cartitem_qty q2c_width_10"  style="<?php echo ($orders_email)?'text-align: left;' :'';  ?>" ><?php echo JText::_('QTC_PRODUCT_QTY'); ?></th>
						<th class="cartitem_price q2c_width_25" width="20%"
							<?php echo $price_col_style;  ?>><?php echo JText::_('QTC_PRODUCT_PRICE'); ?>
						</th>
						</tr>
					<?php
					$qtc_store_row_styles  = "";
					$qtc_store_row_classes = "info";
					$tprice             = 0;
					$i                  = 1;
					$store_array        = array();
					$orderItemIds = array();
					$totalItemShipCharges = 0;
					$totalItemTaxCharges = 0;
					$totalItemDiscount = 0;
					$discount_detail = '';

					$multivendor_enable = $this->params->get('multivendor');

					foreach ($this->orderitems as $order)
					{
						// IF MUTIVENDER ENDABLE then SHOW STORE TITILE and invoice related icons
						if (! empty($multivendor_enable))
						{
							if (! in_array($order->store_id, $store_array))
							{
								$store_array[] = $order->store_id;
								$storeinfo = $this->comquick2cartHelper->getSoreInfo($order->store_id);
								$streLinkPrarm = "";

								if (JFactory::getApplication()->isAdmin())
								{
									$streLinkPrarm .= '&adminCall=1';
								}
								?>
								<tr class="<?php echo $qtc_store_row_classes;?>">
									<td></td>
									<td colspan="<?php echo ( ($showoptioncol==1) ?"6" : "4" ); ?>">
									<div style='float: left; text-align: left'>
										<strong><?php echo $storeinfo['title'];?></strong>
									</div>
									<div class="invoice-pdf qtcHandPointer" style='float: right; text-align: right'>
										<!--Invoice layout and PDF -->
										<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=invoice&orderid=' .  $orderid  . '&tmpl=component&store_id=' . $order->store_id . $streLinkPrarm); ?>"
										target="_blank" >
											<img title="<?php echo JText::_('COM_QUICK2CART_INVOICE_VIEW_ICON_TITLE');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/eye-icon.png"/>
										</a>
										<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&task=orders.generateInvoicePDF&orderid=' . $orderid  . '&tmpl=component&store_id=' . $order->store_id . $streLinkPrarm); ?>" >
											<img title="<?php echo JText::_('COM_QUICK2CART_INVOICE_PDF_ICON_TITLE');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/pdf_16.png"/>
										</a>
										<a onclick="qtcSendInvoiceEmail('<?php echo JRoute::_('index.php?option=com_quick2cart&task=orders.resendInvoice&orderid=' . $orderid . '&tmpl=component&store_id=' . $order->store_id . $streLinkPrarm); ?>')" >
											<img title="<?php echo JText::_('COM_QUICK2CART_INVOICE_EMAIL_ICON_TITLE');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/email_16.png"/>
										</a>
										<!--Invoice layout and PDF -->
									</div>
									</td>
								</tr>
								<?php
							}
						} ?>

						<tr class="row0">
							<td class="cartitem_num"><?php echo $i++;?></td>
							<td class="cartitem_name">
								<?php
								$product_link = $this->comquick2cartHelper->getProductLink($order->item_id, "detailsLink", 1);

								if (empty($product_link))
								{
									echo $order->order_item_name;
								}
								else
								{ ?>

									<a class="no-print" href="<?php echo $product_link;?>">
										<?php echo $order->order_item_name; ?>
									</a>
									<span class="q2c-display-none print-this ">
										<?php echo $order->order_item_name; ?>
									</span> <?php
								}
 								?>
 								<span>
									<strong>
 								<?php

 									//$prodprice = (float) ($order->product_item_price + $order->product_attributes_price); // Commented By Nitesh

 									// Hack By Nitesh
 									if(!empty($order->product_attributes_price))
 									{ 
 										$prodprice = (float) ($order->product_item_price + $order->product_attributes_price); // Commented By Nitesh
 									}
 									else
 									{ 
										$prodprice = (float) $order->product_item_price; 
									}
									
									echo " <br/ >Price :" . $this->comquick2cartHelper->getFromattedPrice(number_format($prodprice, 2), $order_currency);
 								?>
									</strong>
 								</span>

								<input type="hidden" class="inputbox cart_fields" id="" name="<?php echo 'cartDetail[' . $order->order_item_id . '][order_item_id]'; ?>"  value="<?php echo $order->order_item_id; ?>" size="5">
								<?php
								$orderItemIds[] = $order->order_item_id;

								// DOWNLOAD LINK
								if (! empty($this->orderinfo->status) && $this->orderinfo->status == 'C')
								{
									// check where has any media files
									$medisFiles = $this->productHelper->isMediaForPresent($order->order_item_id);

									if (! empty($medisFiles))
									{
										$myDonloadItemid = $this->comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=downloads');
										$downloadLink = JUri::root() . substr(JRoute::_('index.php?option=com_quick2cart&view=downloads&orderid=' . $this->orderinfo->id . '&guest_email=' . $guest_email . '&Itemid=' . $myDonloadItemid), strlen(JUri::base(true)) + 1);
										?>
										<br class="no-print">
										<a href="<?php echo $downloadLink ;?>" class="no-print">
											<i class="icon-download-alt"></i>
											<?php echo JText::_('QTC_ORDER_PG_DOWN_NOW'); ?>
										</a>
										<?php
									}
								}
								?>

							</td>

							<?php

							if ($showoptioncol == 1)
							{
								?>
								<td>
								<?php
								if (!empty($order->prodAttributeDetails))
								{
									// Seleted product attributes ids
									$product_attributes = explode(',', $order->product_attributes);

									// Show each product attribute
									foreach ($order->prodAttributeDetails as $key=>$attribute)
									{
										?>
										<div class="control-group">
											<label class="control-label ">
												<?php echo $attribute->itemattribute_name; ?>
											</label>

											<!-- Store att type-->
											<input type="hidden" class="" id="" name="<?php echo 'cartDetail[' . $order->order_item_id . '][attrDetail][' . $attribute->itemattribute_id . '][type]'; ?>"  value="<?php echo $attribute->attributeFieldType ?>" >

											<?php

											// For text type attribute
											if (! empty($attribute->attributeFieldType) && $attribute->attributeFieldType == 'Textbox')
											{ ?>
												<div class="controls">
													<?php

														if(!empty($attribute->optionDetails[0]->itemattributeoption_id))
														{
															$itemattributeoption_id = $attribute->optionDetails[0]->itemattributeoption_id;
														}
														else
														{
															$itemattributeoption_id = 'new';
														}

														$TextFieldValue = (!empty($attribute->orderitemattribute_name))?$attribute->orderitemattribute_name:'';
													?>

													<input type="text" name="<?php echo 'cartDetail[' . $order->order_item_id . '][attrDetail][' . $attribute->itemattribute_id . '][value]' ?>"
														class="input input-small <?php echo $itemattributeoption_id.'_Textbox'?>"
														value ="<?php echo $TextFieldValue; ?>"

														<!-- Attribute option id -->
													<input type="hidden" name="<?php echo 'cartDetail[' . $order->order_item_id . '][attrDetail][' . $attribute->itemattribute_id . '][itemattributeoption_id]' ?>" class="input input-small" value ="<?php echo $itemattributeoption_id ; ?>" />

												</div>

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
													$data['product_id'] = $order->item_id;
													$data['currency'] = $order_currency;
													$data['attribute_compulsary'] = $attribute->attribute_compulsary;

													$data['field_name'] = 'cartDetail[' .  $order->order_item_id . '][attrDetail][' . $attribute->itemattribute_id . '][value]';
													$data['attributeDetail'] = $attribute;

													$layout = new JLayoutFile('productpage.attribute_option_display',  null, array('client' => 0, 'component' => 'com_quick2cart'));
													$fieldHtml = $layout->render($data);


													// Generate field html (select box)
													// $fieldHtml = $productHelper->getAttrFieldTypeHtml($data);
													?>
													<div class="controls">
														<?php echo $fieldHtml;?>
													</div>
													<?php


											}
											// else end
											?>
										</div>
									<?php
									}
								}
								?>
								</td>
								<?php
							}
							?>

							<td class="cartitem_qty"><?php //echo $order->product_quantity;?>
								<input class="cart_fields input-mini"  name="<?php echo 'cartDetail[' . $order->order_item_id . '][cart_count]' ?>" type="text" value="<?php echo $order->product_quantity; ?>">
							</td>
							<?php
								$productPrice = ($order->product_quantity * $prodprice);
								//$tprice += $productPrice;

								$ItemShipCharges = !empty($order->item_shipcharges) ? (float) $order->item_shipcharges : 0;
								$ItemTaxCharges = !empty($order->item_tax) ? (float) $order->item_tax : 0;
								$ItemDiscount = !empty($order->discount) ? (float) $order->discount : 0;

								// Item price with tax and ship
								$itemPiceWithTaxShip = $productPrice + $ItemShipCharges + $ItemTaxCharges;
								$totalItemShipCharges += $ItemShipCharges;
								$totalItemTaxCharges += $ItemTaxCharges;
								$totalItemDiscount += $ItemDiscount;


								$tprice = (float)$tprice +  $itemPiceWithTaxShip;

								// If discount and discount detail is present
								if (!empty($order->discount) && !empty($order->discount_detail))
								{
									if (is_string($order->discount_detail) && is_array(json_decode($order->discount_detail, true)))
									{
										$detail = json_decode($order->discount_detail, true);

										if (!empty($detail))
										{
											$discount_detail = $order->discount_detail;
										}
									}
								}

							?>
							<td class="cartitem_price" <?php echo $price_col_style;  ?>>
								<div>

									<strong><?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($productPrice,2),$order_currency); ?></strong>
								</div>

								<?php
								if ($itemLevalTaxship == 1 )
								{ ?>
								<div >
									<b> + </b>
									<?php echo " " . JText::_("COM_QUICK2CART_ITEM_TAX") . " :"; ?>
									<input type="text" name="itemTaxShipDetail[<?php echo $order->order_item_id ?>][tax]" class="input input-mini" id="" value="<?php echo $order->item_tax?>">

								</div>
								<div>
									<b> +</b>
									<?php echo " " . JText::_("COM_QUICK2CART_ITEM_SHIP") . " :"; ?>
									<input type="text" name="itemTaxShipDetail[<?php echo $order->order_item_id ?>][ship]" class="input input-mini" id="" value="<?php echo $order->item_shipcharges?>">
								</div>
								<span>
								<b>
									<?php echo JText::_("COM_QUICK2CART_ITEM_TAX_SHIP_PRICE") ?> :
								</b>

								 <?php
								echo $this->comquick2cartHelper->getFromattedPrice(number_format($productPrice + $order->item_tax + $order->item_shipcharges,2),$order_currency); ?></span>
								<?php
								}
								?>


							</td>

	<!--						<td class="cartitem_tprice" <?php echo $price_col_style;  ?>>

								<span><strong><?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($order->product_final_price,2),$order_currency); ?></strong></span>
							</td>
							-->

						</tr>
					<?php
						//$totalItemShipCharges += !empty($order->item_shipcharges) ? $order->item_shipcharges : 0;

					} 
					?>
					<tr>
						<td colspan="6">&nbsp;</td>
					</tr>
					<!--  sub total -->
					<?php
					$col = 3;
					if ($showoptioncol == 1)
					{
						$col = 4;
					}?>
					<tr>
						<td  colspan="<?php echo $col;?>" class="cartitem_tprice_label rightalign" ><strong><?php echo JText::_('QTC_PRODUCT_TOTAL');?></strong></td>
						<td class="cartitem_tprice" <?php echo $price_col_style;?>><span
							id="cop_discount"><?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($tprice, 2), $order_currency);?></span></td>
					</tr>
				<!-- Promotion discount price -->
				<?php
				if (!empty($totalItemDiscount))
				{
					$disAmt = $totalItemDiscount;
				}
				else
				{
					$disAmt = $this->orderinfo->coupon_discount;
				}

				if (!empty($disAmt))
				{
					?>
					<tr style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">

						<td colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign"  style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice: '';?>">
							<div>
							<strong><?php echo JText::_('COM_QUICK2CART_PROMOTION_DICOUNT');?></strong>
							</div>
								<?php
								if (!empty($discount_detail))
								{
									$dis_detail = json_decode($discount_detail);
									?>

									<?php
									if (!empty($dis_detail->coupon_code))
									{
										?>
										(
										<small><strong><?php echo JText::_('QTC_DISCOUNT_CODE') . " : " . $dis_detail->coupon_code . " "; ?> </strong>
										</small>
										<?php if (!empty($dis_detail->name))
										{ ?>
										<span class="promDicountTitle"><small>
											<?php echo $dis_detail->name ?>
										</small></span>
										<?php
										}
										?>
										)
										<?php
									}
								}
								?>
						</td>
						<td class="cartitem_tprice  "  data-title="<?php echo sprintf(JText::_('QTC_PRODUCT_DISCOUNT'), $coupon_code); ?>" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice: '';?>">
							<span
							id="coupon_discount">
							<?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($disAmt, 2), $order_currency);?>
							</span>
						</td>
					</tr>

						<?php
						$tprice = $tprice - $disAmt;

				}
				?>
				<?php
					// Chnage ship charges according to called view. (Called from vender view then use item level shipping charges else order level)
					$orderTaxAmount = 0;

					// Multivendor is off then display order level tax and ship(Considered: admin has only one store)
					if (!empty($totalItemTaxCharges))
					{
						$orderTaxAmount = $totalItemTaxCharges;
					}
					else
					{
						$orderTaxAmount = $this->orderinfo->order_tax;
					}

					if ($itemLevalTaxship == 0)
					{
						// Show tax % for order level tax
						$orderTaxPer = '';
						if (!empty($this->orderinfo->order_tax_details))
						{
							$orderTaxPerDetail = json_decode($this->orderinfo->order_tax_details);

							if (!empty($orderTaxPerDetail->DetailMsg))
							{
								$orderTaxPer = $orderTaxPerDetail->DetailMsg;
							}
						}?>
						<tr style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">
							<td  colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign"  style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice : '';?>"><strong><?php echo JText::sprintf('QTC_TAX_AMT_PAY', $orderTaxPer);?></strong></td>
							<td class="cartitem_tprice" <?php echo $price_col_style;?>>
							<?php
							if ($itemLevalTaxship == 0 )
							{
							?>
								<div class="input-append ">

									<input type="text" name="OrderTaxShipDetail[tax]" class="input input-small" id=""  value="<?php echo $orderTaxAmount ?>">
									<span class="add-on"><?php echo $order_currency_sym; ?></span>

								</div>
							<?php
							}
							else
							{
							?>
								<span
								id="tax_amt"><?php echo $this->comquick2cartHelper->getFromattedPrice($orderTaxAmount , $order_currency);?></span>
								<?php
							}
							?>
							</td>
						</tr>
						<?php
					}

					$orderShipAmount = 0;

					if (!empty($totalItemShipCharges))
					{
						$orderShipAmount = (float) $totalItemShipCharges;
					}
					else
					{
						$orderShipAmount = (float)$this->orderinfo->order_shipping;
					}

					// For backend, as we are showing tax and ship on item level so don't show row for  item level
					if($itemLevalTaxship == 0)
					{?>
						<tr>
							<td  colspan="<?php echo $col;?>" class="cartitem_tprice_label rightalign" ><strong><?php echo JText::sprintf('QTC_SHIP_AMT_PAY', '');?></strong></td>
							<td class="cartitem_tprice" <?php echo $price_col_style;?>>
								<?php
								if ($itemLevalTaxship == 0 )
								{
								?>
									<div class="input-append ">

										<input type="text" name="OrderTaxShipDetail[ship]" class="input input-small" id=""  value="<?php echo $orderShipAmount ?>">
										<span class="add-on"><?php echo $order_currency_sym; ?></span>
									</div>
								<?php
								}
								else
								{
								?>
									<span
									id="tax_amt"><?php echo $this->comquick2cartHelper->getFromattedPrice($orderShipAmount , $order_currency);?></span>
									<?php
								}
								?>

							</td>
						</tr>
						<?php
					}?>
					<!--  final order  total -->
					<tr>
						<td colspan="<?php echo $col;?>" class="cartitem_tprice_label rightalign"><strong><?php echo JText::_('QTC_ORDER_TOTAL');?></strong></td>
						<td class="cartitem_tprice" <?php echo $price_col_style;?>>
							<strong>
								<span id="final_amt_pay" name="final_amt_pay">
								<?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($this->orderinfo->amount, 2), $order_currency);?>
								</span>
							</strong>



							<button type="button" class="btn btn-success pull-right no-print" onClick='updateOrderItemAttribute("<?php echo $this->orderinfo->id ;?>", "<?php echo JText::_('COM_QUICK2CART_ORDER_UPDATED', true); ?>")' title="<?php echo JText::_('COM_QUICK2CART_UPDATE_ORDER_CART_DESC', true); ?>"><?php echo JText::_('COM_QUICK2CART_UPDATE_ORDER_CART', true); ?></button>


						</td>
					</tr>
				</table>
			</form>
		</div> <!--table-responsive -->
	</div>
	<div id="q2c-ajax-call-fade-content-transparent"></div>
	<div id="q2c-ajax-call-loader-modal">
		<img id="q2c-ajax-loader" src="<?php echo JUri::root() . 'components/com_quick2cart/assets/images/ajax.gif';?>" />
	</div>
	<?php
}?>
<!-- End Cart detail -->

