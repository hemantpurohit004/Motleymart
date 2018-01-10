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
<div class=" qtc_wholeCustInfoDiv qtcPadding" >
	<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">
	<?php
	if (in_array('order_status', $order_blocks))
	{ ?>
	<h4><?php echo JText::_('QTC_ORDER_INFO'); ?></h4>
	<div class="row-fluid">
		<div class="span6" style="<?php echo  !empty($orders_email) ? "width: 50%; float: left;" : '';?>">
			<table  class="table table-condensed table-bordered qtc-table  " >
				<tbody >
					<!--
					<tr>
						<td><?php echo JText::_('QTC_ORDER_ID');?></td>
						<td><?php echo $this->orderinfo->prefix . $this->orderinfo->id;?></td>
					</tr> -->
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
					}?>
						<tr>
							<td><?php echo JText::_('QTC_ORDER_STATUS');?></td>
							<td>
								<?php
								$whichever = '';

								switch ($this->orderinfo->status)
								{
									case 'C':
										$whichever = JText::_('QTC_CONFR');
										break;
									case 'RF':
										$whichever = JText::_('QTC_REFUN');
										break;
									case 'S':
										$whichever = JText::_('QTC_SHIP');
										break;
									case 'E':
										$whichever = JText::_('QTC_ERR');
										break;
									case 'P':
										if ($orders_site)
										{
											$whichever = JText::_('QTC_PENDIN');
										}
										break;
									default:
										$whichever = $orders->status;
										break;
								}

								if (!($orders_site))
								{
									echo JHtml::_('select.genericlist', $this->pstatus, "pstatus", 'class="pad_status"  onChange="selectstatusorder(' . $this->orderinfo->id . ',this);"', "value", "text", $this->orderinfo->status);
								}
								else
								{
									echo $whichever;
								}?>
							</td>
						</tr>

							<?php
							if (!$orders_site)
							{
								// Backend order list view : show notify and comment checkbox
								?>
								<tr>
									<td><?php echo JText::_('QTC_NOTIFY');?></td>
									<td>
										<input type="checkbox" id="notify_chk" name="notify_chk|<?php echo $this->orderinfo->id;?>" checked />
									</td>
								</tr>

								<tr>
									<td><?php echo JText::_('QTC_COMMENT');?></td>
									<td><textarea id="" name="comment" rows="3"  value=""></textarea></td>
								</tr>
								<?php
							}
							?>
				</tbody>
			</table>

		</div>
		<div class="span6"  style="<?php echo  !empty($orders_email) ? "width: 50%; float: right;" : '';?>">
				<!-- ************* Order Status Info Starts **********  -->
					<table class="table table-condensed table-bordered  " >
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
								}
								?>
							</td>
						</tr>
<!--
						<tr >
							<td><?php echo JText::_('QTC_ORDER_IP');?></td>
							<td><?php echo $this->orderinfo->ip_address;?></td>
						</tr>
-->


						<?php
						if ($this->orderinfo->processor)
						{
						?>
							<tr>
								<td><?php echo JText::_('QTC_ORDER_PAYMENT');?></td>
								<td><?php echo !empty($this->paidPlgName) ? $this->paidPlgName : $this->orderinfo->processor;?></td>
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
						}?>
						<tr>
							<td><?php echo JText::_('QTC_USER_COMMENT');?></td>
							<td><?php echo ($this->orderinfo->customer_note) ? $this->orderinfo->customer_note : JText::_('QTC_USER_COMMENT_NO');?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_QUICK2CART_PAYMENT_NOTE');?></td>
							<td class="q2c-max-width-150"><?php echo ($this->orderinfo->payment_note) ? $this->orderinfo->payment_note : JText::_('QTC_USER_COMMENT_NO');?></td>
						</tr>
					</table>
		</div>
	</div>
		<?php
	} ?>

		<!-- For pending order, show the payment list -->

		<?php
		$url = JUri::root() . "index.php?option=com_quick2cart&tmpl=component&task=payment.gethtml&order=" . $this->orderinfo->id . "&" . JSession::getFormToken() . "=1&processor=";

				$ajax =
<<<EOT
techjoomla.jQuery(document).ready(function(){
techjoomla.jQuery("input[name='gateways']").change(function(){
var url1 = '{$url}'+techjoomla.jQuery("input[name='gateways']:checked").val();
techjoomla.jQuery('#html-container').empty().html('Loading...');
techjoomla.jQuery.ajax({
url: url1,
type: 'GET',
dataType: 'html',
success: function(response)
{
	techjoomla.jQuery('#html-container').removeClass('ajax-loading').html( response );
}
});
});
});
EOT;
		$document->addScriptDeclaration($ajax);

		if ($orders_site && !($orders_email) && ($this->orderinfo->status == 'P'))
		{
			$jinput          = JFactory::getApplication()->input;
			$paybuttonstatus = $jinput->get('paybuttonstatus');

			// Means called from my orders list
			$getways_display =  "display:none" ;

			// Means called from my orders list
			$complete_payment_btn = empty($paybuttonstatus) ? "display:none" : "display:block";

			$qtc_processor = $jinput->get('processor');

			// IF in url processor="payment getway name
			// "is present then dont show
			if (!isset($qtc_processor))
			{
				if ($vendor_order_view == 0)
				{
				?>
					<tr style="<?php echo $complete_payment_btn;?>">
						<td colspan='2'>
							<button type="button" name="qtc_show_getways"
								id="qtc_show_getways"
								class="btn btn-success btn-medium validate"
								onclick="qtc_showpaymentgetways();" style="<?php echo $complete_payment_btn;?>">
									<?php echo JText::_('QTC_COMPLETE_UR_ORDER');?>
							</button>
						</td>
					</tr>
					<?php
				}
				// end of $vendor_order_view if
			}
			// end of$qtc_processor if
			?>
			<div id="qtc_paymentmethods" style="<?php echo $getways_display;?>;">
				<div class="control-group">
					<label for="" class="control-label">
						<strong><?php echo JText::_('PAY_METHODS');?></strong>
					</label>
					<div class="controls">
						<?php
						$gateways = $this->gateways;

						if (empty($this->gateways))
						{
							echo JText::_('NO_PAYMENT_GATEWAY');
						}
						else
						{
							$pg_list = JHtml::_('select.radiolist', $gateways, 'gateways', 'class="inputbox" autocomplete="off" ', 'id', 'name', '', false);
							echo $pg_list;
						}?>
					</div>
				</div>
			</div>
			<!-- End of qtc_paymentmethods-->
				<?php
		}
		?>
		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" id='hidid' name="id" value="" />
		<input type="hidden" id='hidstat' name="status" value="" />
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="view" value="orders" />
		<input type="hidden" 	name="controller" value="orders" />
	</form>


		<!--PAYMENT HIDDEN DATA WILL COME HERE -->
		<div id="html-container" name=""></div>
</div>
