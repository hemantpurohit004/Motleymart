<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true) . '/media/techjoomla_strapper/bs3/css/bootstrap.min.css');
$document->addStyleSheet(JUri::root(true).'/components/com_quick2cart/assets/css/morris.css');
$document->addStyleSheet(JUri::root(true).'/components/com_quick2cart/assets/css/tjdashboard-sb-admin.css');

$session = JFactory::getSession();
$session->set('tj_from_date', '');
$session->set('tj_end_date', '');
$session->set('statsforpie', '');
$session->set('ignorecnt', '');
$session->set('statsfor_line_day_str_final', '');
$session->set('statsfor_line_imprs', '');
$session->set('statsfor_line_clicks', '');
$session->set('periodicorderscount', '');

$mntnm_cnt = 1;
$i = 0;
$kk = 0;
$ignorecnt = array();
$curdate = '';
$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));

$comquick2cartHelper = new comquick2cartHelper;
$dash_currency = $comquick2cartHelper->getCurrencySymbol();

$model = $this->getModel('dashboard');

foreach ($this->AllMonthName as $AllMonthName)
{
	$AllMonthName_final[$i] = $AllMonthName['month'];
	$curr_MON = $AllMonthName['month'];
	$month_amt_val[$curr_MON] = 0;
	$i++;
}

$barChartData = 0;

foreach ($this->MonthIncome as $MonthIncome)
{
	$month_year = '';
	$month_year = $MonthIncome->YEARNM;
	$month_name = $MonthIncome->MONTHSNAME;

	$month_int = (int)$month_name;
	$timestamp = mktime(0, 0, 0, $month_int);
	$curr_month = date("F", $timestamp);

	foreach ($this->AllMonthName as $AllMonthName)
	{
		if (($curr_month == $AllMonthName['month']) and ($MonthIncome->amount) and ($month_year==$AllMonthName['year']))
		{
			$month_amt_val[$curr_month] = str_replace(",", '', $MonthIncome->amount);
		}

		if ($barChartData==0)
		{
			if ($MonthIncome->amount)
			{
				$barChartData = 1;
			}
		}
	}
}

$month_amt_str  = implode(",", $month_amt_val);
$month_name_str = implode("','", $AllMonthName_final);
$month_name_str = "'" . $month_name_str . "'";
$month_array_name = array();

$js = "
	function refreshViews()
	{
		var dash_currency = \"" . $dash_currency . "\";
		fromDate = document.getElementById('from').value;
		toDate = document.getElementById('to').value;
		fromDate1 = new Date(fromDate.toString());
		toDate1 = new Date(toDate.toString());
		difference = toDate1 - fromDate1;
		days = Math.round(difference/(1000*60*60*24));

		if (parseInt(days) < 0)
		{
			alert(\"".JText::_('DATELESS')."\");

			return;
		}

		/*Set Session Variables*/
		var info = {};
		techjoomla.jQuery.ajax({
			type: 'GET',
			url: 'index.php?option=com_quick2cart&task=dashboard.SetsessionForGraph&fromDate='+fromDate+'&toDate='+toDate,
			dataType: 'json',
			async:false,
			success: function(data) {
			}
		});

		/*Get periodic data and redraw chart*/
		techjoomla.jQuery.ajax({
			type: 'GET',
			url: 'index.php?option=com_quick2cart&task=dashboard.makechart',
			dataType: 'json',
			success: function(data)
			{
				techjoomla.jQuery('#bar_chart_graph').html('' + data.barchart);
				/*Reset hidden field values*/
				document.getElementById('pending_orders').value=data.pending_orders;
				document.getElementById('confirmed_orders').value=data.confirmed_orders;
				document.getElementById('shiped_orders').value=data.shiped_orders;
				document.getElementById('refund_orders').value=data.refund_orders;
				/*Redraw charts*/
				document.getElementById('periodic_orders').innerHTML = dash_currency + ' ' + data.periodicorderscount;
				drawPeriodicOrdersChart();
			}
		});
	}";

$document->addScriptDeclaration($js);
?>

<?php
		$versionHTML = '<span class="label label-info">' .
		JText::_('COM_QUICK2CART_HAVE_INSTALLED_VER') . ': ' . $this->version .'</span>';

			if ($this->latestVersion)
			{
				if ($this->latestVersion->version > $this->version)
				{
					$versionHTML = '<div class="alert alert-error">' .'<i class="icon-puzzle install"></i>' .JText::_('COM_QUICK2CART_HAVE_INSTALLED_VER') . ': ' . $this->version .'<br/>' .'<i class="icon icon-info"></i>' .JText::_("COM_QUICK2CART_NEW_VER_AVAIL") . ': ' . '<span class="qtc_latest_version_number">' . $this->latestVersion->version . '</span><br/>' . '<i class="icon icon-warning"></i>' . '<span class="small">' . JText::_("COM_QUICK2CART_LIVE_UPDATE_BACKUP_WARNING") . '</span>' . '</div><div><a href="index.php?option=com_installer&view=update" class="qtc-btn-wrapper btn btn-small btn-primary">' . JText::sprintf('COM_QUICK2CART_LIVE_UPDATE_TEXT', $this->latestVersion->version) . '</a><a href="' . $this->latestVersion->infourl . '/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=quick2cart&utm_content=updatedetailslink&utm_campaign=quick2cart_ci' . '" target="_blank" class="qtc-btn-wrapper btn btn-small btn-info">' . JText::_('COM_QUICK2CART_LIVE_UPDATE_KNOW_MORE') . '</a></div>';
				}
			}
?>
<div class="<?php echo Q2C_WRAPPER_CLASS;?> q2c-admin-dashboard">
	<?php
		if(JVERSION >= '3.0'):
			 if (!empty( $this->sidebar)) : ?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar; ?>
				</div>
				<div id="j-main-container" class="span10">
			<?php else : ?>
				<div id="j-main-container">
			<?php endif;
		endif;
		?>
	<form name="adminForm" id="adminForm" class="form-validate" method="post">
		<!-- TJ Bootstrap3 -->
		<div class="tjBs3">
			<!-- TJ Dashboard -->
			<div class="tjDB">
				<div id="wrapper">
					<div id="11111page-wrapper">
						<!-- /.row -->
						<div class="clearfix">&nbsp;</div>

						<?php
						if (!$this->allincome)
						{
							$this->allincome=0;
						}

						$this->allincome = $comquick2cartHelper->getFromattedPrice($this->allincome);
						?>

						<!-- Start - stat boxes -->
						<div class="row">
							<div class="col-lg-3 col-md-6">
								<div class="panel panel-green">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-3 ">
												<i class="fa fa-money fa-4x"></i>
											</div>
											<div class="col-xs-9 text-right">
												<div class="huge"><?php echo $this->allincome;?> </div>
												<div><?php echo JText::_('ALL_TIME_INCOME');?></div>
											</div>
										</div>
									</div>
									<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders', false);?>">
										<div class="panel-footer">
											<span class="pull-left">
												<?php echo JText::_('COM_QUICK2CART_VIEW_DETAILS');?>
											</span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>
							</div>
							<div class="col-lg-3 col-md-6">
								<div class="panel panel-primary">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-3">
												<i class="fa fa-barcode fa-4x"></i>
											</div>
											<div class="col-xs-9 text-right">
												<div class="huge"><?php echo $this->productsCount;?> </div>
												<div><?php echo JText::_('COM_QUICK2CART_TOTAL_PRODUCTS');?></div>
											</div>
										</div>
									</div>
									<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=products', false);?>">
										<div class="panel-footer">
											<span class="pull-left">
												<?php echo JText::_('COM_QUICK2CART_VIEW_DETAILS');?>
											</span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>
							</div>
							<div class="col-lg-3 col-md-6">
								<div class="panel panel-yellow">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-3">
												<i class="fa fa-shopping-cart fa-4x"></i>
											</div>
											<div class="col-xs-9 text-right">
												<div class="huge"><?php echo $this->ordersCount; ?></div>
												<div><?php echo JText::_('COM_QUICK2CART_TOTAL_ORDERS');?></div>
											</div>
										</div>
									</div>
									<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders', false);?>">
										<div class="panel-footer">
											<span class="pull-left">
												<?php echo JText::_('COM_QUICK2CART_VIEW_DETAILS');?>
											</span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>
							</div>
							<div class="col-lg-3 col-md-6">
								<div class="panel panel-red">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-3">
												<i class="fa fa-bookmark fa-4x"></i>
											</div>
											<div class="col-xs-9 text-right">
												<div class="huge"><?php echo $this->storesCount; ?></div>
												<div><?php echo JText::_('COM_QUICK2CART_TOTAL_STORES');?></div>
											</div>
										</div>
									</div>
									<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=stores', false);?>">
										<div class="panel-footer">
											<span class="pull-left">
												<?php echo JText::_('COM_QUICK2CART_VIEW_DETAILS');?>
											</span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>
							</div>
						</div>
						<!-- End - stat boxes -->

						<div class="row">
							<div class="col-lg-8">
								<!-- Start - Bar Chart for Monthly Income for past 12 months -->
								<div class="panel panel-default">
									<div class="panel-heading">
										<i class="fa fa-bar-chart-o fa-fw"></i>
										<?php echo JText::_('MONTHLY_INCOME_MONTH');?>
									</div>
									<div class="panel-body">
										<div id="graph-monthly-sales"></div>
										<hr class="hr hr-condensed"/>
										<div class="center">
											<?php echo JText::_('BAR_CHART_HAXIS_TITLE');?>
										</div>
									</div>
								</div>
								<!-- End - Bar Chart for Monthly Income for past 12 months -->
								
								<div class="row">
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-heading">
												<i class="fa fa-pie-chart fa-fw"></i>
												<?php echo JText::_('PERIODIC_ORDERS');?>
											</div>
											<div class="panel-body">
												<!-- CALENDER ND REFRESH BTN  -->
												<div class="clearfix row">
													<div class="col-sm-12 col-lg-12 col-md-12">
														<div class="pull-right">
															<div class="form-group">
																<label label-default class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label"><?php echo JText::_('FROM_DATE');?>
																</label>
																<div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
																	<div class="input-group">
																		<?php echo JHtml::_('calendar', $backdate, 'fromDate', 'from', '%Y-%m-%d', array('class' => 'inputbox input-xs', 'style' => 'min-height:35px!important; max-width:90px!important;')); ?>
																	</div>
																</div>
															</div>

															<div class="form-group">
																<label label-default class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label"><?php echo JText::_('TO_DATE');?></label>
																<div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
																	<div class="input-group">
																		<?php echo JHtml::_('calendar', date('Y-m-d'), 'toDate', 'to', '%Y-%m-%d', array('class' =>'inputbox input-xs', 'style' => 'min-height:35px!important; max-width:90px!important;')); ?>
																	</div>
																</div>
															</div>

															<div class="form-group">
																<div class="pull-right col-lg-4 col-md-4 col-sm-4 col-xs-4">
																	<div class="input-group">
																		<input id="btnRefresh" class="pull-right btn btn-micro btn-primary" type="button" value="<?php echo JText::_('COM_QUICK2CART_GO'); ?>" style="font-weight: bold;" onclick="refreshViews();" title="<?php echo JText::_('COM_QUICK2CART_GO_TOOLTIP');?>"/>
																	</div>
																</div>
															</div>

														</div>
													</div>
													<!--<div class="pull-left col-sm-6">
														<label for="from"><?php //echo JText::_('FROM_DATE');?></label>
														<?php //echo JHtml::_('calendar', $backdate, 'fromDate', 'from', '%Y-%m-%d', array('class' => 'inputbox input-xs', 'style' => 'min-height:35px!important; max-width:90px!important;')); ?>
													</div>
													<div class="pull-right col-sm-6">
														<label for="to"><?php //echo JText::_('TO_DATE');?></label>
														<?php //echo JHtml::_('calendar', date('Y-m-d'), 'toDate', 'to', '%Y-%m-%d', array('class' =>'inputbox input-xs', 'style' => 'min-height:35px!important; max-width:90px!important;')); ?>
														<input id="btnRefresh" class="pull-right btn btn-micro btn-primary" type="button" value="<?php //echo JText::_('COM_QUICK2CART_GO'); ?>" style="font-weight: bold;" onclick="refreshViews();" title="<?php //echo JText::_('COM_QUICK2CART_GO_TOOLTIP');?>"/>
													</div>-->
													<div class="clearifx"></div>
												</div>
												<!--END::CALENDER ND REFRESH BTN  -->

												<div class="clearifx">&nbsp;</div>

												<?php
												if (!$this->tot_periodicorderscount)
												{
													$this->tot_periodicorderscount=0;
												}
												?>

												<div class="list-group">
													<span class="list-group-item">
														<i class="fa fa-money fa-fw"></i> <?php echo JText::_('PERIODIC_INCOME');?>
														<span class="pull-right text-muted small">
															<strong id="periodic_orders">
																<?php echo $comquick2cartHelper->getFromattedPrice($this->tot_periodicorderscount);?>
															</strong>
														</span>
													</span>
												</div>

												<!-- Periodic orders - graph start -->
												<div id="graph-periodic-orders"></div>
												<hr class="hr hr-condensed"/>
												<div class="center">
													<strong class="">
														<?php echo JText::_('PERIODIC_ORDERS');?>
													</strong>
												</div>
												<!-- Periodic orders - graph end -->
											</div>
											<!-- /.panel-body -->
										</div>
									</div>
									<!-- /.col-lg-6 -->

									<div class="col-lg-6">
										<!-- Start - not shipped orders -->
										<div class="panel panel-default">
											<div class="panel-heading">
												<i class="fa fa-list fa-fw"></i>
												<?php echo JText::_('COM_Q2C_NOTSHIPPED_ORDERS');?>
											</div>
											<div class="panel-body">
												<?php
												if(!empty($this->notShippedDetails))
												{
													?>
													<table class="table table-striped table-hover" >
														<thead>
															<th><?php echo JText::_('QTC_ORDER_ID')?></th>
															<th><?php echo JText::_('QTC_NAME')?></th>
															<th><?php echo JText::_('QTC_AMOUNT')?></th>
														</thead>
														<tbody>
															<?php
															foreach($this->notShippedDetails as $ord)
															{
																?>
																<tr>
																	<td>
																		<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=order&orderid='.$ord['id'], false); ?>"
																			title="<?php echo JText::_('QTC_TOOLTIP_VIEW_ORDER_MSG');?>">
																			<?php echo $ord['prefix'] . $ord['id'];?>
																		</a>
																	</td>
																	<td><?php echo $ord['name'];?></td>
																	<td><?php echo $comquick2cartHelper->getFromattedPrice($ord['amount'])?></td>
																</tr>
																<?php
															}
															?>
														</tbody>
													</table>

													<a title="<?php echo JText::_('QTC_STORE_SHOW_ALL');?>"
														class="btn btn-primary btn-small pull-right"
														href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders');?>"
														target="_blank" >
															<?php echo JText::_('QTC_STORE_SHOW_ALL'); ?>
													</a>
													<?php
												}
												else
												{
													?>
													<div class="center">
														<?php echo JText::_("NO_STORE_PREVIOUS_ORDERS");?>
													</div>
													<?php
												}
												?>
											</div>
										</div>
										<!-- End - not shipped orders -->

										<?php
										if(!empty($this->multivendor_enable))
										{
											?>
												<!-- Start - not shipped orders -->
												<div class="panel panel-default">
													<div class="panel-heading">
														<i class="fa fa-list fa-fw"></i>
														<?php echo JText::_('COM_PENDING_PAYOUTS');?>
													</div>
													<div class="panel-body">
														<?php
														if(!empty($this->getpendingPayouts))
														{
															?>
															<table class="table table-striped table-hover">
																<thead>
																	<th><?php echo JText::_('QTC_NAME')?></th>
																	<th><?php echo JText::_('QTC_REMAINING_AMOUNT')?></th>
																</thead>
																<tbody>
																	<?php
																	$count = 1;

																	foreach ($this->getpendingPayouts as $pay)
																	{
																		?>
																		<tr>
																			<?php
																			$uname = $comquick2cartHelper->getUserName($pay->user_id);
																			$amt = (float)$pay->total_amount - $pay->fee;
																			?>
																			<td><?php echo $uname;?></td>
																			<td><?php echo $comquick2cartHelper->getFromattedPrice($amt); ?></td>
																		</tr>
																		<?php
																		if ($count >= 5)
																		{
																			break;
																		}
																	}
																	?>
																</tbody>
															</table>

															<a title="<?php echo JText::_('QTC_STORE_SHOW_ALL');?>"
																class="btn btn-primary btn-small pull-right"
																href="<?php echo JRoute::_('index.php?option=com_quick2cart&task=payout.add');?>"
																target="_blank" >
																	<?php echo JText::_('QTC_STORE_SHOW_ALL'); ?>
															</a>
															<?php
														}
														else
														{
															?>
															<div class="center">
																<?php echo JText::_("COM_QUICK2CART_DASHBORD_NO_PENDING_PAYOUTS");?>
															</div>
															<?php
														}
														?>
													</div>
												</div>
												<!-- End - not shipped orders -->
											<?php
										}
										?>
									</div>
									<!-- /.col-lg-6 -->
								</div>
								<!-- /.row -->
							</div>
							<!-- Removed By Nitesh> -->
							
							<!-- /.col-lg-4 -->
						</div>
						<!-- /.row -->

					</div>
					<!-- /#page-wrapper -->
				</div>
			</div>
			<!-- /.tjDB TJ Dashboard -->
		</div>
		<!-- /.tjBs3TJ TJ Bootstrap3 -->

		<?php
		// Get data for periodic orders chart
		$statsforpie = $this->statsforpie;
		$currentmonth='';
		$pending_orders=$confirmed_orders=$shiped_orders=$refund_orders=0;

		if(empty($statsforpie[0][0]) && empty($statsforpie[1][0]) && empty($statsforpie[2][0]))
		{
			$barchart=JText::_('NO_STATS');
			$emptylinechart=1;
		}
		else
		{
			if(!empty($statsforpie[0]))
			{
				 $pending_orders= $statsforpie[0][0]->orders;
			}

			if(!empty($statsforpie[1]))
			{
				$confirmed_orders = $statsforpie[1][0]->orders;
			}

			if(!empty($statsforpie[2]))
			{
				$refund_orders = $statsforpie[2][0]->orders;
			}

			if(!empty($statsforpie[3]))
			{
				$shiped_orders = $statsforpie[3][0]->orders;
			}
		}

		$emptypiechart=0;

		if(!$pending_orders and !$confirmed_orders and !$refund_orders and !$shiped_orders)
		{
			$emptypiechart=1;
		}
		?>

		<input type="hidden" name="pending_orders" id="pending_orders" value="<?php if($pending_orders) echo $pending_orders; else echo '0'; ?>">
		<input type="hidden" name="confirmed_orders" id="confirmed_orders" value="<?php if($confirmed_orders) echo $confirmed_orders; else echo '0';  ?>">
		<input type="hidden" name="shiped_orders" id="shiped_orders" value="<?php if($shiped_orders) echo $shiped_orders; else echo '0';  ?>">
		<input type="hidden" name="refund_orders" id="refund_orders" value="<?php if($refund_orders) echo $refund_orders; else echo '0'; ?>">
	</form>
</div>

<script type="text/javascript">
	techjoomla.jQuery(document).ready(function()
	{
		document.getElementById("pending_orders").value=<?php if($pending_orders) echo $pending_orders; else echo '0'; ?>;
		document.getElementById("confirmed_orders").value=<?php if($confirmed_orders) echo $confirmed_orders; else echo '0'; ?>;
		document.getElementById("shiped_orders").value=<?php if($shiped_orders) echo $shiped_orders; else echo '0'; ?>;
		document.getElementById("refund_orders").value=<?php  if($refund_orders) echo $refund_orders; else echo '0'; ?>;

		drawPeriodicOrdersChart();
	});

	<?php if ($barChartData): ?>
		drawBarChart();

		function drawBarChart()
		{
			Morris.Bar({
				element: 'graph-monthly-sales',
				data:
					<?php
						$dataArray = "[";

						for ($i = 0; $i < count($AllMonthName_final); $i++)
						{
							$dataArray .= "{period : '" . $AllMonthName_final[$i] . "', salesTotal : " . $month_amt_val[$AllMonthName_final[$i]] . "},";
						}
						$dataArray .= "]";
						echo $dataArray;
					?>,
				xkey: 'period',
				ykeys: ['salesTotal'],
				labels: ['<?php echo JText::_('BAR_CHART_VAXIS_TITLE') . ' ('.$dash_currency.')';?>'],
				barColors: ['#428bca'],
				barRatio: 0.4,
				xLabelAngle: 35,
				hideHover: 'auto',
				resize:true
			});
		}
	<?php else: ?>
			techjoomla.jQuery('#graph-monthly-sales').html("<div class='center'><?php echo JText::_("NO_STORE_PREVIOUS_ORDERS");?></div>");
	<?php endif; ?>

	function drawPeriodicOrdersChart()
	{
		techjoomla.jQuery('#graph-periodic-orders').html('');

		var pending_orders = document.getElementById('pending_orders').value;
		var confirmed_orders = document.getElementById('confirmed_orders').value;
		var shiped_orders = document.getElementById('shiped_orders').value;
		var refund_orders = document.getElementById('refund_orders').value;

		if (pending_orders > 0  || confirmed_orders > 0 || shiped_orders > 0 || refund_orders > 0)
		{
			Morris.Donut({
				element: 'graph-periodic-orders',
				data: [{
					label: "<?php echo JText::_("PENDING_ORDS");?>",
					value: pending_orders
				}, {
					label: "<?php echo JText::_("CONFIRM_ORDS");?>",
					value: confirmed_orders
				}, {
					label: "<?php echo JText::_("SHIPPED_ORDS");?>",
					value: shiped_orders
				 }, {
					label: "<?php echo JText::_("REFUND_ORDS");?>",
					value: refund_orders
				}],
				colors: ["#f0ad4e", "#5cb85c", "#428bca", "#d9534f"],
				resize: true
			});
		}
		else
		{
			techjoomla.jQuery('#graph-periodic-orders').html("<div class='center'><?php echo JText::_("NO_STORE_PREVIOUS_ORDERS");?></div>");
		}
	}
</script>
