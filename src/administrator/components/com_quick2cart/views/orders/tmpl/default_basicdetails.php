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
<div class=" qtc_wholeCustInfoDiv">
<!--
	<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">
-->
	<?php
	if (in_array('order_status', $order_blocks))
	{ ?>
	<h4><?php echo JText::_('QTC_ORDER_INFO'); ?></h4>
	<table class="table table-condensed ">
		<tbody>
			<tr>
				<td>
					<table  class="table table-condensed table-bordered">
						<tbody >
							<tr>
								<td><?php echo JText::_('QTC_ORDER_ID');?></td>
								<td><?php echo $this->orderinfo->prefix . $this->orderinfo->id;?></td>
							</tr>
							<tr >
								<td><?php echo JText::_('QTC_ORDER_DATE');?></td>
								<td><?php echo $this->orderinfo->cdate;?></td>
							</tr>
							<?php
							// If not store releated view
							if (empty($this->storeReleatedView))
							{
								?>
								<tr>
									<td><?php echo JText::_('QTC_AMOUNT');?></td>
									<td>
										<span>
											<?php
											$tprice = 0;

											foreach ($this->orderitems as $order)
											{
												$tprice += $order->product_final_price;
											}

											$store_id = JRequest::getVar('store_id');

											if ($store_id == NULL)
											{
												echo $this->comquick2cartHelper->getFromattedPrice($this->orderinfo->amount, $order_currency);
											}
											else
											{
												echo $this->comquick2cartHelper->getFromattedPrice(number_format($tprice, 2), $order_currency);
											}
											// End added by Sneha?>
										</span>
									</td>
								</tr>
								<?php
							}

							if ($this->orderinfo->transaction_id)
							{
								?>
								<tr>
									<td><?php echo JText::_('QTC_ORDER_PAYMENT_TRANSAC');?></td>
									<td><?php echo $this->orderinfo->transaction_id;?></td>
								</tr>
								<?php
							}

							$OrderStatus = '';

							switch ($this->orderinfo->status)
							{
								case 'C':
									$OrderStatus = JText::_('QTC_CONFR');
									break;
								case 'RF':
									$OrderStatus = JText::_('QTC_REFUN');
									break;
								case 'S':
									$OrderStatus = JText::_('QTC_SHIP');
									break;
								case 'E':
									$OrderStatus = JText::_('QTC_ERR');
									break;
								case 'P':
										$OrderStatus = JText::_('QTC_PENDIN');
									break;
								default:
									$OrderStatus = $orders->status;
									break;
							}
							?>
							<tr>
								<td class=""><?php echo JText::_('QTC_ORDER_STATUS');?></td>
								<td><?php echo $OrderStatus?></td>
							</tr>



						</tbody>
					</table>
				</td>
				</td>
				<td>
				<!-- ************* Order Status Info Starts **********  -->
					<table class="table table-condensed table-bordered" >
						<tr >
							<td><?php echo JText::_('QTC_ORDER_USER');?></td>
							<td>
								<?php
								$table   = JUser::getTable();
								$user_id = intval($this->orderinfo->payee_id);

								if ($user_id)
								{
									$creaternm = '';

									if ($table->load($user_id))
									{
										$creaternm = JFactory::getUser($this->orderinfo->payee_id);
									}

									echo (!$creaternm) ? JText::_('QTC_NO_USER') : $creaternm->username;
								}
								else
								{
									echo !empty($billinfo->user_email) ? $billinfo->user_email : '';
								}?>
							</td>
						</tr>
						<tr >
							<td><?php echo JText::_('QTC_ORDER_IP');?></td>
							<td><?php echo $this->orderinfo->ip_address;?></td>
						</tr>
						<?php
						if ($this->orderinfo->processor)
						{
						?>
							<tr>
								<td><?php echo JText::_('QTC_ORDER_PAYMENT');?></td>
								<td><?php echo $this->paidPlgName = $this->comquick2cartHelper->getPluginName($this->orderinfo->processor);?></td>
							</tr>
							<?php
						}
						?>

						<tr>
							<td><?php echo JText::_('QTC_USER_COMMENT');?></td>
							<td class="q2c-max-width-150"><?php echo ($this->orderinfo->customer_note)?$this->orderinfo->customer_note:JText::_('QTC_USER_COMMENT_NO') ; ?></td>
						</tr>
						<tr>
							<td class="q2c-max-width-150"><?php echo JText::_('COM_QUICK2CART_PAYMENT_NOTE');?></td>
							<td class="q2c-max-width-150"><?php echo ($this->orderinfo->payment_note) ? $this->orderinfo->payment_note : JText::_('QTC_USER_COMMENT_NO');?></td>
						</tr>
					</table>
				</td>
			</tr>
		</tbody>
		</table>
		<?php
	} ?>

</div>
