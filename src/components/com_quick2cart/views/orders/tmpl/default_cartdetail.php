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
$this->productHelper = new productHelper;
// Addded for invoice layout and PDF, Added by Vijay
$params             = JComponentHelper::getParams('com_quick2cart');
$multivendor_enable = $params->get('multivendor');
$jinput = JFactory::getApplication()->input;
$orderid =  $jinput->get('orderid');
$view = $jinput->get('view');
$layout = $jinput->get('layout');
$email = $jinput->get('email','', 'RAW');
$calledStoreview = $jinput->get('calledStoreview');
?>
<!-- Start Cart detail -->
<?php
if (in_array('cart', $order_blocks))
{ ?>
	<div class="qtcPadding1111">
		<?php
		if ($orders_email)
		{ ?>
			<h4 <?php echo $emailstyle;?>><?php echo JText::_('QTC_ORDER_DETAILS');?></h4>
			<?php
		}
		elseif ($orders_site)
		{ ?>
			<!--<div class="fieldset_block">  -->
			<h4><?php echo JText::_('QTC_ORDER_DETAILS');?></h4>
			<?php
		}

		//$price_col_style = "style=\"" . (!empty($orders_email) ? 'text-align: right;' : '') . "\"";
		$showoptioncol = 0;

		foreach ($this->orderitems as $citem)
		{
			if (!empty($citem->product_attribute_names))
			{
				$showoptioncol = 1; // atleast one found then show
				break;
			}
		}

		$emailStyle_Table = "border-top: 1px solid #ccc;border-left: 1px solid #ccc;border-collapse: collapse;
    border-spacing:0px;";
		$emailStyle_tr = "";
		$emailStyle_th = "border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;padding-right:5px;";
		$emailStyle_td = "border-bottom: 1px solid #ccc; border-right: 1px solid #ccc;padding-right:5px;";

		// Price and total price columnr
		$emailStyle_priceNdTotPrice = "text-align:right;";

		//$emailStyle_td = "border-top: 1px solid #ccc;border-left: 1px solid #ccc;padding:5px;";

		?>
		<div class="table-responsive" id='no-more-tables'>
			<table width="100%" class="table table-condensed table-bordered qtc-table" style="<?php echo ($orders_email) ? $emailStyle_Table : '';?>" cellspacing="0">
				<!-- 	<tr> <td><h4 <?php echo $emailstyle;?> ><?php echo JText::_('QTC_ORDER_DETAILS');?></h4></td> </tr> -->
				<thead>
				<tr class="hidden-xs hidden-sm" style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">
					<th class="cartitem_num" width="5%" align="left" style="<?php echo ($orders_email) ? $emailStyle_th : '';?>"> <?php echo JText::_('QTC_NO');?></th>
					<!--			<th><?php //echo JHtml::_( 'grid.sort', JText::_('GETWAY'),'processor', $this->lists['order_Dir'], $this->lists['order']);?></th> -->
					<th class="cartitem_name" align="left" style="<?php echo ($orders_email) ? $emailStyle_th : '';?>" ><?php echo JText::_('QTC_PRODUCT_NAM');?></th>
					<?php

					if ($showoptioncol == 1)
					{ ?>
					<th class="cartitem_opt" align="left" style="<?php echo ($orders_email) ? $emailStyle_th : '';?>" ><?php echo JText::_('QTC_PRODUCT_OPTS');?></th>
					<?php
					}?>
					<th class="cartitem_qty rightalign" width="5%"  style="<?php echo ($orders_email) ? $emailStyle_th : '';?>" ><?php echo JText::_('QTC_PRODUCT_QTY');?></th>
					<th class="cartitem_price rightalign" width="15%" style="<?php echo ($orders_email) ? $emailStyle_th. $emailStyle_priceNdTotPrice: '';?>"
						><?php echo JText::_('QTC_PRODUCT_PRICE');?></th>
					<th class="cartitem_tprice rightalign"  width="15%"
						 style="<?php echo ($orders_email) ? $emailStyle_th . $emailStyle_priceNdTotPrice : '';?>"><?php echo JText::_('QTC_PRODUCT_TPRICE');?></th>
				</tr>
				</thead>
				<?php
				$qtc_store_row_styles  = "";
				$qtc_store_row_classes = "info";
				if ($orders_email)
				{
					// here using INLINE STYLING FOR email instead of class "info"
					$qtc_store_row_style   = " background-color: #D9EDF7;";
					$qtc_store_row_classes = "";
				}
				if (version_compare(JVERSION, '3.0', 'lt'))
				{
					$qtc_icon_info = " icon-info-sign ";
				}
				else
				// for joomla3.0
				{
					$qtc_icon_info = " icon-wand ";
				}

				$tprice             = 0;
				$i                  = 1;
				$store_array        = array();
				$orderItemIds = array();
				$totalItemShipCharges = 0;
				$totalItemTaxCharges = 0;
				$totalItemDiscount = 0;
				$discount_detail = '';

				foreach ($this->orderitems as $order)
				{
					// IF MUTIVENDER ENDABLE then SHOW STORE TITILE
					if (!empty($multivendor_enable))
					{
						if (!in_array($order->store_id, $store_array))
						{
							$store_array[] = $order->store_id;
							$storeinfo     = $this->comquick2cartHelper->getSoreInfo($order->store_id);?>
							<tr class="<?php echo $qtc_store_row_classes;?>" style="<?php echo !empty($qtc_store_row_style) ? $qtc_store_row_style : '';?>">
								<td  class="hidden-xs" style="<?php echo ($orders_email) ? $emailStyle_td : '';?>"></td>
								<td colspan="<?php echo (($showoptioncol == 1) ? "5" : "4");?>" style="<?php echo ($orders_email) ? $emailStyle_td . "padding-left:5px;": '';?>">
									<strong><?php echo $storeinfo['title'];?></strong>
									<?php
									// Dont show icon in order email
									if (empty($orders_email) && ($view != "cartcheckout"))
									{
										$streLinkPrarm = "";

										if (!empty($this->storeReleatedView))
										{
											$streLinkPrarm = "&calledStoreview=1";
										}

										if (!empty($email))
										{
											$streLinkPrarm .= "&email=" . $email;
										}
									?>
									<!-- Invoice layout and PDF -->
									<div class="invoice-pdf qtcHandPointer" style="float:right;">
										<a href="<?php echo JURI::root().substr(JRoute::_('index.php?option=com_quick2cart&view=orders&layout=invoice&orderid=' .  $this->orderinfo->id. '&tmpl=component&store_id=' . $order->store_id . $streLinkPrarm . '&Itemid=' . $myorderItemid),strlen(JURI::base(true))+1); ?>"
										target="_blank">
											<img class="invoice-pdf-img" title="<?php echo JText::_('COM_QUICK2CART_INVOICE_VIEW_ICON_TITLE');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/eye-icon.png"/>
										</a>
										<a href="<?php echo JURI::root().substr(JRoute::_('index.php?option=com_quick2cart&task=orders.generateInvoicePDF&orderid=' . $this->orderinfo->id . '&tmpl=component&store_id=' . $order->store_id . $streLinkPrarm . '&Itemid=' . $myorderItemid),strlen(JURI::base(true))+1); ?>" >
											<img class="invoice-pdf-img" title="<?php echo JText::_('COM_QUICK2CART_INVOICE_PDF_ICON_TITLE');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/pdf_16.png"/>
										</a>
										<a onclick="qtcSendInvoiceEmail('<?php echo JURI::root().substr(JRoute::_('index.php?option=com_quick2cart&task=orders.resendInvoice&orderid=' .$this->orderinfo->id . '&tmpl=component&store_id=' . $order->store_id . $streLinkPrarm . '&Itemid=' . $myorderItemid),strlen(JURI::base(true))+1); ?>')" >
											<img class="invoice-pdf-img" title="<?php echo JText::_('COM_QUICK2CART_INVOICE_EMAIL_ICON_TITLE');?>" src="<?php echo JURI::root();?>components/com_quick2cart/assets/images/email_16.png"/>
										</a>
									</div>

									<?php
									}
									?>
									<!-- Invoice layout and PDF ends here-->
								</td>
							</tr>
						<?php
						}
					}?>
					<tr class="row0" style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">
						<td class="cartitem_num" data-title="<?php echo JText::_('QTC_NO');?>" style="<?php echo ($orders_email) ? $emailStyle_td : '';?>"><?php echo $i++;?></td>
						<td class="cartitem_name" data-title="<?php echo JText::_('QTC_PRODUCT_NAM');?>" style="<?php echo ($orders_email) ? $emailStyle_td : '';?>">
							<?php
							$product_link = $this->comquick2cartHelper->getProductLink($order->item_id, 'detailsLink', 1);

							if (empty($product_link))
							{
								echo $order->order_item_name;
							}
							else
							{ ?>
								<a href="<?php	echo $product_link;?>"><?php echo $order->order_item_name;?></a>
							<?php
							}

							// Show sku
							$sku_item_id = !empty($order->variant_item_id) ? $order->variant_item_id : $order->item_id;
							$sku = $this->productHelper->getSku($sku_item_id);

							if (!empty($sku))
							{
								?>
								<span title="<?php echo JText::_('QTC_PROD_SKU_TOOLTIP'); ?>"> ( <?php echo JText::_('QTC_PROD_SKU');?> : <?php echo $sku; ?> )</span>
								<?php
							}

							$orderItemIds[] = $order->order_item_id;

							// Don't show download link in emails or invoice
							if (!empty($this->orderinfo->status) && $this->orderinfo->status == 'C' && empty($orders_email))
							{

								// Check where has any media files
								$medisFiles = $this->productHelper->isMediaForPresent($order->order_item_id);

								if (!empty($medisFiles))
								{
									$myDonloadItemid = $this->comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=downloads');
									$downloadLink    = JUri::root() . substr(JRoute::_('index.php?option=com_quick2cart&view=downloads&orderid=' . $this->orderinfo->id . '&guest_email=' . $guest_email . '&Itemid=' . $myDonloadItemid), strlen(JUri::base(true)) + 1);?>
									<br>
									<a href="<?php echo $downloadLink;?>">
										<i class="icon-download-alt"></i><?php echo JText::_('QTC_ORDER_PG_DOWN_NOW');?>
									</a>
								<?php
								}
							}

							// Showing shipping method name
							if (!empty($order->item_shipDetail))
							{
								$item_shipDetail = json_decode($order->item_shipDetail,true);

								if (!empty($item_shipDetail['name']))
								{
									?>
										<span>
											<strong>
												<br />
												<?php echo JText::_('COM_QUICK2CART_ORDER_SHIP_METH') . ": " ?> </strong>

											<?php echo $item_shipDetail['name']; ?>
										</span>
									<?php
								}
							}
							?>

						</td>
							<?php

						if ($showoptioncol == 1)
						{ ?>
							<td class="cartitem_opt" data-title="<?php echo JText::_('QTC_PRODUCT_OPTS');?>" style="<?php echo ($orders_email) ? $emailStyle_td : '';?>">
							<?php
								if (($order->product_attribute_names))
								{
									echo nl2br(str_replace(",", "\n", $order->product_attribute_names));
								}?>
							</td>
							<?php
						}
						?>

						<td class="cartitem_qty rightalign" data-title="<?php echo JText::_('QTC_PRODUCT_QTY');?>" style="<?php echo ($orders_email) ? $emailStyle_td : '';?>"><?php echo $order->product_quantity;?></td>
						<td class="cartitem_price rightalign"  data-title="<?php echo JText::_('QTC_PRODUCT_PRICE');?>" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice : '';?>">
							<span><?php
								// Commented By Nitesh
								// $prodprice = (float) ($order->product_item_price + $order->product_attributes_price);
								// Hack By Nitesh
								$prodprice = (float) ($order->product_attributes_price);
								echo $this->comquick2cartHelper->getFromattedPrice(number_format($prodprice, 2), $order_currency);?>
							</span>
						</td>
						<?php
							$productPrice = ($order->product_quantity * $prodprice);
							$tprice += $productPrice;
							?>
						<td class="cartitem_tprice rightalign"  data-title="<?php echo JText::_('QTC_PRODUCT_TPRICE');?>" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice : '';?>">
							<span><?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($productPrice, 2), $order_currency);?></span>
						</td>
					</tr>
					<?php

					$totalItemShipCharges += !empty($order->item_shipcharges) ? (float) $order->item_shipcharges : 0;
					$totalItemTaxCharges += !empty($order->item_tax) ? (float) $order->item_tax : 0;
					$totalItemDiscount += !empty($order->discount) ? (float) $order->discount : 0;

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
				}
				?>

				<!-- Finished the displaying item list-->
				<?php
				$col = 4;
				if ($showoptioncol == 1)
				{
					$col = 5;
				}?>
				<tr style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">
					<td colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign" align="left" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice: '';?>"><strong><?php echo JText::_('QTC_PRODUCT_TOTAL');?></strong></td>
					<td class="cartitem_tprice rightalign"  data-title="<?php echo JText::_('QTC_PRODUCT_TOTAL');?>" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice: '';?>">
						<span id="cop_discount">
							<?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($tprice, 2), $order_currency);?>
						</span>
					</td>
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

				$disAmt = (float)$disAmt;


				if (!empty($disAmt))
				{
					?>
					<tr style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">
						<td colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign" align="left" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice: '';?>">
							<div>
							<strong><?php echo JText::_('COM_QUICK2CART_PROMOTION_DICOUNT');?></strong>
							</div>
								<?php
								// Currently used for item level promotion
								if (!empty($discount_detail) )
								{
									$dis_detail = json_decode($discount_detail);

									if (!empty($dis_detail->name))
									{
									?>
										(
										<?php
										if (!empty($dis_detail->coupon_code))
										{
											?>
											<small><strong><?php echo JText::_('QTC_DISCOUNT_CODE') . " : " . $dis_detail->coupon_code . " "; ?> </strong>
											</small>
											<?php
										}
										?>

										<span class="promDicountTitle">
											<small>
												<?php echo $dis_detail->name ?>
											</small>
										</span>
										)
										<?php
									}
								}
								?>
						</td>
						<td class="cartitem_tprice rightalign "  data-title="<?php echo sprintf(JText::_('QTC_PRODUCT_DISCOUNT'), $coupon_code); ?>" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice: '';?>">
							<span
							id="coupon_discount">
							<?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($disAmt, 2), $order_currency);?>
							</span>
						</td>
					</tr>

					<!-- total amt after Discount row-->
					<tr class="dis_tr" style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">
						<td colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign" align="left" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice : '';?>"><strong><?php echo JText::_('QTC_NET_AMT_PAY');?></strong></td>
						<td class="cartitem_tprice rightalign"  data-title="<?php echo  JText::_('QTC_NET_AMT_PAY');?>" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice: '';?>">
							<span
								id="total_dis_cop">
							<?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($tprice- $disAmt, 2), $order_currency);?></span>
						</td>
					</tr>
						<?php
						$tprice = $tprice - $disAmt;

				}
				?>
				<?php
				// Show commission on view: store order detail view
				$totalCommissionApplied = 0;

				if ($calledStoreview == 1 && $layout !="invoice")
				{
					$storeHelper            = new storeHelper();
					$commission             = $params->get('commission');
					$totalCommissionApplied    = $storeHelper->totalCommissionApplied($tprice);
					$commission_cutNetPrice = (float) $tprice - $totalCommissionApplied;?>
					<!-- Commission price -->
					<tr style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">

						<td colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign" style="<?php echo ($orders_email) ? $emailStyle_td  . $emailStyle_priceNdTotPrice: '';?>" align="left"><strong><?php echo sprintf(JText::_('QTC_COMMISSION_CUT_SUB_TOT'), '(' . $commission . '%)');?> 	</strong></td>
						<td class="cartitem_tprice rightalign" style="<?php echo ($orders_email) ? $emailStyle_td  . $emailStyle_priceNdTotPrice : '';?>" data-title="<?php echo JText::_('QTC_COMMISSION_CUT_SUB_TOT');?>">
							<span
								id="cop_discount"><?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($totalCommissionApplied, 2), $order_currency);?>
							</span>
						</td>
					</tr>
					<!-- CommissionCut net total -->
					<!--
					<tr style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">
						<td colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign" style="<?php echo ($orders_email) ? $emailStyle_td  . $emailStyle_priceNdTotPrice: '';?>" align="left"><strong><?php echo sprintf(JText::_('QTC_COMMISSION_CUT_NET_TOT'), '(' . $commission . '%)');?> 	</strong></td>
						<td class="cartitem_tprice rightalign" style="<?php echo ($orders_email) ? $emailStyle_td  . $emailStyle_priceNdTotPrice : '';?>"  data-title="<?php echo JText::_('QTC_COMMISSION_CUT_NET_TOT');?>" >
							<span id="cop_discount">
								<?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($commission_cutNetPrice, 2), $order_currency);?>
							</span>
						</td>
					</tr>
					-->
					<?php
				} // END OF SHOW Comission cut & Value recd)
				?>
				<?php
				// / if currently vendor specific code (CALLED FROM ::1.sending email to
				// vendor OR 2.viewing vendor it own order details)is NOT running CODE OR
				// vendor specific

				//if (!isset($this->vendor_email))
				{
					// Item level cooupons are considered in product final price
					// For order level coupon
					//if ($vendor_order_view == 0)
					{

						// Chnage ship charges according to called view. (Called from vender view then use item level shipping charges else order level)
						$orderTaxAmount = 0;

						// Multivendor is off then display order level tax and ship(Considered: admin has only one store)
						if (!empty($totalItemTaxCharges))
						{
							$orderTaxAmount = (float) $totalItemTaxCharges;
						}
						else
						{
							// NOt called fom store view then only show order level tax or ship
							if (empty($calledStoreview))
							{
								$orderTaxAmount = (float) $this->orderinfo->order_tax;
							}
						}

						if (!empty($orderTaxAmount))
						{
							//~ tax data
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
									<td colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign" align="left" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice : '';?>"><strong><?php echo JText::sprintf('QTC_TAX_AMT_PAY', $orderTaxPer);?></strong></td>
									<td class="cartitem_tprice rightalign"  data-title="<?php echo JText::sprintf('QTC_TAX_AMT_PAY', $orderTaxPer);?>" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice : '';?>">
									<span
										id="tax_amt"><?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($orderTaxAmount, 2), $order_currency);?></span></td>
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
							// NOt called fom store view then only show order level tax or ship
							if (empty($calledStoreview))
							{
								$orderShipAmount = (float)$this->orderinfo->order_shipping;
							}
						}

						if(!empty($orderShipAmount))
						{
							?>
							<tr style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">
								<td colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign" align="left" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice : '';?>"><strong><?php echo JText::sprintf('QTC_SHIP_AMT_PAY', '');?></strong></td>
								<td class="cartitem_tprice rightalign"  data-title="<?php echo JText::sprintf('QTC_SHIP_AMT_PAY', '');?>" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice : '';?>">
									<span	id="ship_amt">
										<?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($orderShipAmount, 2), $order_currency);?>
									</span>
								</td>
							</tr>
							<?php
						}
						?>
						<!--  final order  total -->
						<tr style="<?php echo ($orders_email) ? $emailStyle_tr : '';?>">
							<td colspan="<?php echo $col;?>" class="cartitem_tprice_label hidden-xs hidden-xs rightalign" align="left" style="<?php echo ($orders_email) ? $emailStyle_td . $emailStyle_priceNdTotPrice : '';?>"><strong><?php echo JText::_('QTC_ORDER_TOTAL');?></strong></td>
							<td class="cartitem_tprice rightalign"  data-title="<?php echo JText::_('QTC_ORDER_TOTAL');?>" style="<?php echo ($orders_email) ? $emailStyle_td  . $emailStyle_priceNdTotPrice : '';?>">
								<strong>
									<span id="final_amt_pay" name="final_amt_pay">
									<?php echo $this->comquick2cartHelper->getFromattedPrice(number_format($tprice - $totalCommissionApplied + $orderTaxAmount + $orderShipAmount, 2), $order_currency);?>
									</span>
								</strong>
							</td>
						</tr>
						<?php
					} // end of if vendor_order_view==0
				} // end of$this->vendor_email=1;?>
			</table>
		</div> <!--table-responsive -->
	</div>
	<?php
	if (!$orders_email)
	{
	?>
	<div id="q2c-ajax-call-fade-content-transparent"></div>
	<div id="q2c-ajax-call-loader-modal">
		<img id="q2c-ajax-loader" src="<?php echo JUri::root() . 'components/com_quick2cart/assets/images/ajax.gif';?>" />
	</div>
	<?php
	}
}?>
<!-- End Cart detail -->
