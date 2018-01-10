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
<div id	="no-more-tables">
<form action="" name="adminForm" id="adminForm" class="form-horizontal form-validate qtcPadding" method="post">
		<!-- Tab Header-->
		<?php
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			echo JHtml::_('bootstrap.startTabSet', 'orderInfo', array('active' => 'order_status_info'));
		}
		else
		{ ?>
			<ul id="myTab" class="nav nav-tabs">
				<li class="active">
					<a href="#order_status_info" data-toggle="tab"><?php echo JText::_('QTC_ORDER_STAT_INFO');?></a>
				</li>
				<li>
					<a href="#order_history" data-toggle="tab"><?php echo JText::_('COM_QUICK2CART_ORDER_HISTORY');?></a>
				</li>
			</ul>
			<?php
		}

		/* Tab content */
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			 echo JHtml::_('bootstrap.addTab', 'orderInfo', 'order_status_info', JText::_('QTC_ORDER_STAT_INFO', true));
		}
		else
		{ ?>

			<div id="myTabContent" class="tab-content">
			<!--tab1-->
				<div class="tab-pane fade in active" id="order_status_info">
			<?php
		}
		// hidden order items


		?>
		<div class="clearfix">&nbsp;</div>
		<input class="" id="" name="orderItemsStr" type="hidden" value="<?php echo implode('||', $orderItemIds); ?>" >

				<div class="col-xs-12">
					<div class="">
						<table class="table table-condensed " id="complete-order" name="complete-order">

							<tr>
								<td class=" hidden-xs"><?php echo JText::_('QTC_ORDER_STATUS');?></td>
								<td data-title="<?php echo JText::_('QTC_ORDER_STATUS');?>">
									<?php
									//if ($orders_site)
									{
										echo JHtml::_('select.genericlist', $this->vendorstatus, "status",  'class="pad_status"  ', "value", "text", $this->orderinfo->status);
									}

									?>
								</td>
							</tr>

							<tr>
								<td class=" hidden-xs"><?php echo JText::_('QTC_NOTIFY');?></td>
								<td class="" data-title="<?php echo JText::_('QTC_NOTIFY');?>">
									<div>
										<input type="checkbox" id="notify_chk" name="notify_chk" checked />
									</div>
								</td>
							</tr>

							<tr>
								<td class=" hidden-xs"><?php echo JText::_('QTC_COMMENT');?></td>
								<td data-title="<?php echo JText::_('QTC_COMMENT');?>">
									<textarea id="" name="order_note" rows="3"  value=""></textarea>
								</td>
							</tr>
							<tr>
								<td class=" hidden-xs"></td>
								<td data-title="<?php echo JText::_('COM_QUICK2CART_UPDAE_ORDER_STATUS');?>">

									<button  class="btn btn-default btn-success " title="<?php echo JText::_('COM_QUICK2CART_UPDAE_ORDER_STATUS');?>" ><?php echo JText::_('COM_QUICK2CART_UPDAE_ORDER_STATUS'); ?></button>
								</td>
							</tr>
					</table>
					<hr/>
					</div>
					<input type="hidden" name="option" value="com_quick2cart" />
					<input type="hidden" name="task" id="task" value="orders.updateStoreItemStatus" />
					<input type="hidden" name="view" value="orders" />

				</div>

		<?php
		if (JVERSION>=3.0)
		{
			 echo JHtml::_('bootstrap.endTab');
		}
		else
		{ ?>
			</div>
			<?php
		}

		// Order history tab started
		if (JVERSION>=3.0)
		{
			echo JHtml::_('bootstrap.addTab', 'orderInfo', 'order_history', JText::_('COM_QUICK2CART_ORDER_HISTORY', false));
		}
		else
		{ ?>
			<div class="tab-pane fade in" id="order_history">
			<?php
		} ?>

				<div class="clearfix">&nbsp;</div>
				<!--- Other info tab ----->
				<div class="col-xs-12">
					<?php
					if (!empty($this->orderHistory))
					{
					?>
					<div class="table-responsive">
						<table class="table table-condensed table-striped table-bordered">
							<thead>
								<th class="q2c_width_15">
									<?php echo JText::_("QTC_PRODUCT_NAM"); ?>
								</th>
								<th class="q2c_width_15">
									<?php echo JText::_("COM_QUICK2CART_CDATE"); ?>
								</th>
								<th class="q2c_width_15">
									<?php echo JText::_("COM_QUICK2CART_CUSTOMER_NOTIFIED"); ?>
								</th>
								<th class="q2c_width_15">
									<?php echo JText::_("QTC_PROD_STATUS"); ?>
								</th>
								<th class="q2c_width_15">
									<?php echo JText::_("COM_QUICK2CART_ORDER_NOTE"); ?>
								</th>
							</thead>
							<tbody>
							<?php
							$oldItem_id = "";
								foreach($this->orderHistory as $row)
								{

									?>
									<tr>
										<td class="q2c_width_15" data-title="<?php echo JText::_('QTC_PRODUCT_NAM');?>">
											<?php
												if ($oldItem_id !== $row->name)
												{
													echo $row->name;
													$oldItem_id = $row->name;

												}
											?>
										</td>
										<td class="q2c_width_15" data-title="<?php echo JText::_('COM_QUICK2CART_CDATE');?>">
											<?php
												echo JFactory::getDate($row->mdate)->Format(JText::_('COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT'));
												echo '  ';
												echo JFactory::getDate($row->mdate)->Format(JText::_('COM_QUICK2CART_TIME_FORMAT_SHOW_AMPM'));
											?>
										</td>

										<td class="q2c_width_15" data-title="<?php echo JText::_('COM_QUICK2CART_CUSTOMER_NOTIFIED');?>">
											<?php
												if($row->customer_notified == 0)
												{
													echo '<i class="' . QTC_ICON_REMOVE . '"></i>';
												}
												else
												{
													echo '<i class="' . QTC_ICON_CHECKMARK . '"></i>';
												}
											?>
										</td>

										<td class="q2c_width_15" data-title="<?php echo JText::_('QTC_PROD_STATUS');?>">
											<?php
												switch($row->order_item_status)
												{
													case 'C':
														$status = JText::_('QTC_CONFR');
													break;

													case 'RF':
														$status = JText::_('QTC_REFUN') ;
													break;

													case 'S':
														$status = JText::_('QTC_SHIP') ;
													break;

													case 'E':
														$status = JText::_('QTC_ERR') ;
													break;

													case 'P':
														$status = JText::_('QTC_PENDIN') ;
													break;

													default:
													$status = !empty($orders->order_item_status) ? $orders->order_item_status : '';
													break;
												}

												echo $status;
												?>
										</td>

										<td class="" data-title="<?php echo JText::_('QTC_PROD_STATUS');?>">
											<?php echo $row->note;?>
										</td>
									</tr>
									<?php
								}
							?>
							</tbody>
						</table>
					</div>
					<?php
					}
					else
					{
						?>
						<div class="alert alert-info">
							<p><?php echo JText::_('COM_QUICK2CART_NO_HISTORY'); ?></p>
						</div>
						<?php
					}
					?>
				</div>


		<?php
		if (JVERSION>=3.0)
		{
			echo JHtml::_('bootstrap.endTab');

		} else
		{ ?>
			</div>
			<?php
		} ?>

	<?php
	if (JVERSION>=3.0)
	{
	 echo JHtml::_('bootstrap.endTabSet');
	}
	else
	{ ?>
		</div>
		<?php
	} ?>
</form>
</div>
