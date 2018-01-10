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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework');
JHtml::_('behavior.modal');

$document = JFactory::getDocument();

// Load css files
$comparams = JComponentHelper::getParams('com_quick2cart');
$currentBSViews = $comparams->get('currentBSViews', "bs3");
$laod_boostrap = $comparams->get('qtcLoadBootstrap', 1);

if ($currentBSViews == "bs3")
{
	// Load Css
	if (!empty($laod_boostrap))
	{
		$document->addStyleSheet(JUri::root(true) . '/media/techjoomla_strapper/bs3/css/bootstrap.min.css');
	}
}
elseif ($currentBSViews == "bs2")
{
	// For bs2 forcefully load
	//$document->addStyleSheet(JUri::root(true) . '/media/techjoomla_strapper/bs3/css/bootstrap.min.css');
}

$document->addStyleSheet(JUri::root(true).'/components/com_quick2cart/assets/css/morris.css');
$document->addStyleSheet(JUri::root(true).'/components/com_quick2cart/assets/css/tjdashboard-sb-admin.css');
// Dashboard CSS
//$document->addStyleSheet(JUri::root(true).'/components/com_quick2cart/assets/css/tjdashboard.css');

$comquick2cartHelper = new comquick2cartHelper;

// Global icon constants.
define('Q2C_DASHBORD_ICON_ORDERS', "fa fa-shopping-cart fa-3x");
define('Q2C_DASHBORD_ICON_ITEMS', "fa fa-barcode fa-3x");
define('Q2C_DASHBORD_ICON_SALES', "fa fa-money fa-3x");
define('Q2C_DASHBORD_ICON_AVG_ORDER', "fa fa-bars fa-3x");
define('Q2C_DASHBORD_ICON_ALL_SALES', "fa fa-money fa-3x");
define('Q2C_DASHBORD_ICON_USERS', "fa fa-users fa-3x");

// CHECK LOGIN STATUS
$user=JFactory::getUser();
if (!$user->id)
{
	?>
	<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
		<div class="well" >
			<div class="alert alert-danger">
				<span ><?php echo JText::_('QTC_LOGIN'); ?> </span>
			</div>
		</div>
	</div>
	<!-- eoc techjoomla-bootstrap -->
	<?php
	return false;
}

// CHECK WHETHER User HAS STORE
if (!$this->store_id)
{
?>
	<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
		<?php
		if($this->params->get('multivendor'))
		{
			$createstore_Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=createstore');
			$createStore_link=JRoute::_('index.php?option=com_quick2cart&view=vendor&layout=createstore&Itemid='.$createstore_Itemid);

			$clickhere='<a href="'.$createStore_link.'">'.JText::_( 'QTC_CLICK_HERE' ).'</a> '.JText::_( 'QTC_TO_CREATE_STORE' );
			$msg = JText::sprintf('NO_STORE_FOUND',$clickhere);
		}
		else
		{
			$msg = JText::sprintf('COM_QUICK2CART_MULTIVENDOR_OFF_CANNT_CREATE_MSG');
		}
		JFactory::getApplication()->enqueueMessage( $msg, 'Notice');
		?>
	</div>
	<!-- eoc techjoomla-bootstrap -->
	<?php
	return false;
}

// Take date a one year back in past.
$backdate = date('Y-m-d', strtotime(date('Y-m-d').' - 365 days'));
$store_id = $this->store_id;
$js = "
	var linechart_imprs;
	var linechart_clicks;
	var linechart_day_str=new Array();

	function refreshViews()
	{
		fromDate = document.getElementById('from').value;
		toDate = document.getElementById('to').value;
		fromDate1 = new Date(fromDate.toString());
		toDate1 = new Date(toDate.toString());
		difference = toDate1 - fromDate1;
		days = Math.round(difference/(1000*60*60*24));
		if (parseInt(days) < 0)
		{
			alert(\"".JText::_('COM_QUICK2CART_DATELESS')."\");
			return;

		}
		techjoomla.jQuery.ajax({
			type: 'GET',
			url: 'index.php?option=com_quick2cart&task=vendor.refreshVendorDashboard&tmpl=component&fromDate='+fromDate+'&toDate='+toDate+'&storeid=2',
			async:false,
			dataType: 'json',
			success: function(data)
			{
				window.location.reload();
			}
		});
	}";

	$document->addScriptDeclaration($js);
?>

<div class="<?php echo Q2C_WRAPPER_CLASS; ?> tj-dashboard">
	<form name="adminForm" id="adminForm" class="form-validate" method="post">

		<div class="qtc_toolbarDiv">
			<?php
			$active = 'cp';
			$view=$comquick2cartHelper->getViewpath('vendor', 'toolbar');
			ob_start();
			include($view);
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
			?>
		</div>

		<legend>
			<?php
			if (!empty($this->store_role_list))
			{
				$storehelp = new storeHelper();
				$index     = $storehelp->array_search2d($this->store_id, $this->store_role_list);

				if (is_numeric($index))
				{
					$store_name = $this->store_role_list[$index]['title'];
				}

				echo JText::sprintf('QTC_STORE_DASHBOARD',$store_name);
			}
			else
			{
				echo JText::_('QTC_STORE_DASHBOARD_DEFAULT');
			}
			?>
		</legend>

		<?php
		//If there is no products in store, then provide such msg.
		if (empty($this->prodcountprodCount))
		{
			$addProd_Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=product');
			$addprodlink    = JRoute::_('index.php?option=com_quick2cart&view=product&current_store=' . $store_id . '&Itemid='.$addProd_Itemid);

			$clickhere = '<a href="' . $addprodlink . '">' . JText::_('QTC_CLICK_HERE') . '</a>' . JText::_('QTC_TO_ADD_PROD');

			JFactory::getApplication()->enqueueMessage(JText::sprintf('NO_PROD_AGAINST_STORE', $clickhere), 'Notice');
		}
		?>
		<!-- TJ Bootstrap3 -->
		<div class="tjBs3">
			<!-- TJ Dashboard -->
			<div class="tjDB">
				<div class="row">
					<?php
						$app = JFactory::getApplication();
						$backdate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 30 days'));
						$backdate = $app->getUserStateFromRequest('from', 'from', $backdate, 'string');
						$currentdate = date('Y-m-d H:i:s');
						$currentdate = $app->getUserStateFromRequest('to', 'to', $currentdate, 'string');
					?>
					<div class="col-lg-4 col-md-5 col-sm-5 col-xs-9">
						<div class="form-group">
							<label label-default class="col-lg-2 col-md-2 col-sm-2 col-xs-3 control-label">
								<?php echo JText::_('QTC_FROM_DATE'); ?>
							</label>
							<div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
								<div class="input-group">
									<?php echo JHtml::_('calendar', $backdate, 'from', 'from', '%Y-%m-%d', array('class'=>'input-small form-control', 'readonly'=>'true')); ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-4 col-md-5 col-sm-5 col-xs-9">
						<div class="form-group">
							<label label-default class="col-lg-2 col-md-2 col-sm-2 col-xs-3 control-label">
								<?php echo JText::_('QTC_TO_DATE'); ?>
							</label>
							<div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
								<div class="input-group">
									<?php echo JHtml::_('calendar', $currentdate, 'to', 'to', '%Y-%m-%d', array('class'=>'input-small form-control', 'readonly'=>'true')); ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-3">
						<button class="btn btn-primary btn-small qtcMarginBotton" onclick="refreshViews()" title="<?php echo JText::_('COM_QUICK2CART_DASHB_GO_TOOLTIP');?>"><?php echo JText::_('COM_QUICK2CART_FILTER_GO');?></button>
					</div>
					<div class="clearfix"></div>
				</div>

				<!--<div class="row">
					<div class="col-sm-12 col-lg-12 col-md-12">
						<div class="pull-right">
							<?php
							$app = JFactory::getApplication();
							$backdate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 30 days'));
							$backdate = $app->getUserStateFromRequest('from', 'from', $backdate, 'string');
							$currentdate = date('Y-m-d H:i:s');
							$currentdate = $app->getUserStateFromRequest('to', 'to', $currentdate, 'string');
							?>
							<div class="form-inline">
								<label class="qtcMarginBotton" >
									<?php //echo JText::_('QTC_FROM_DATE') . "&nbsp;"; ?>
								</label>

								<?php //echo JHtml::_('calendar', $backdate, 'from', 'from', '%Y-%m-%d', array('class'=>'input-small form-control')); ?>

								<label class="qtcMarginBotton">
									<?php //echo JText::_('QTC_TO_DATE') . "&nbsp;"; ?>
								</label>

								<?php //echo JHtml::_('calendar', $currentdate, 'to', 'to', '%Y-%m-%d', array('class'=>'input-small form-control')); ?>

								<button class="btn btn-primary btn-small qtcMarginBotton" onclick="refreshViews()" title="<?php //echo JText::_('COM_QUICK2CART_DASHB_GO_TOOLTIP');?>"><?php //echo JText::_('COM_QUICK2CART_FILTER_GO');?></button>
							</div>
						</div>
						<div class="clearfix">&nbsp;</div>
					</div>
				</div>

				<div class="clearfix">&nbsp;</div>-->

				<!--Periodic-Quick-stats-->
				<?php $perdIncome = $this->getPeriodicIncome;?>
				<!-- Start - stat boxes -->
				<div class="row">
					<div class="col-sm-4 col-lg-4 col-md-12">
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="<?php echo Q2C_DASHBORD_ICON_SALES; ?>"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?php echo !empty($perdIncome['amount']) ? $comquick2cartHelper->getFromattedPrice(number_format($perdIncome['amount'], 2)) : "0"; ?></div>
									</div>
								</div>
							</div>
							<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=storeorder&Itemid=' . $this->orders_itemid, false);?>">
								<div class="panel-footer">
									<span class="pull-left">
										<?php echo JText::_('COM_Q2C_PRD_REVENUE');?>
									</span>
									<span class="pull-right">
										<i class="fa fa-arrow-circle-right"></i>
									</span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-sm-4 col-lg-4 col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3 ">
										<i class="<?php echo Q2C_DASHBORD_ICON_ORDERS; ?>"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?php echo !empty($perdIncome['totorders']) ? $perdIncome['totorders'] : "0"; ?> </div>
									</div>
								</div>
							</div>
							<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=storeorder&Itemid=' . $this->orders_itemid, false);?>">
								<div class="panel-footer">
									<span class="pull-left">
										<?php echo JText::_('COM_Q2C_PRD_TOTORDER');?>
									</span>
									<span class="pull-right">
										<i class="fa fa-arrow-circle-right"></i>
									</span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-sm-4 col-lg-4 col-md-6">
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="<?php echo Q2C_DASHBORD_ICON_ITEMS; ?>"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?php echo !empty($perdIncome['qty']) ? $perdIncome['qty'] : "0"; ?></div>
									</div>
								</div>
							</div>
							<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=storeorder&Itemid=' . $this->orders_itemid, false);?>">
								<div class="panel-footer">
									<span class="pull-left">
										<?php echo JText::_('COM_Q2C_PRD_QTY');?>
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

				<?php
				$chartsDataFlag = 0;

				if ($this->getPeriodicIncomeGrapthData)
				{
					// Get formatted data for charts.
					$incomedata = $comquick2cartHelper->getLineChartFormattedData($this->getPeriodicIncomeGrapthData);
					$chartsDataFlag = 1;
				}
				?>

				<!--Periodic-Graphs-->
				<div class="row">
					<div class="col-sm-12 col-lg-12 col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-area-chart fa-fw"></i>
								<?php echo JText::_('COM_QUICK2CART_STORE_STATS'); ?>
							</div>
							<div class="panel-body">
								<div id="tabbedGraphs" class="">
									<div class="row">
										<div class="col-sm-12 col-lg-12 col-md-12">
											<ul class="nav nav-tabs">
												<li class="active">
													<a href="#overview_1" data-toggle="tab"><?php echo JText::_('COM_QUICK2CART_STORE_SALES_AMOUNT'); ?></a>
												</li>

												<?php if ($chartsDataFlag): ?>
													<li class="" onclick="javascript:drawOrdersChart();">
												<?php else: ?>
													<li class="">
												<?php endif;?>
													<a href="#overview_3" data-toggle="tab"><?php echo JText::_('COM_QUICK2CART_STORE_ORDERS_PLACED'); ?></a>
												</li>
											</ul>

											<div class="tab-content">
												<div class="tab-pane active" id="overview_1">
													<div class="row">
														<div class="col-sm-12 col-lg-12 col-md-12">
															<?php if ($chartsDataFlag): ?>
																<div id="q2c_chart_amount" style="height: 250px;"></div>
															<?php else: ?>
																<div>&nbsp;</div>
																<div class="alert alert-info">
																	<?php echo JText::_("COM_Q2C_NO_PERIODIC_INCOME");?>
																</div>
															<?php endif;?>
														</div>
													</div>
												</div>

												<div class="tab-pane" id="overview_3">
													<div class="row">
														<div class="col-sm-12 col-lg-12 col-md-12">
															<?php if ($chartsDataFlag): ?>
																<div id="q2c_chart_orders" style="height: 250px;"></div>
															<?php else: ?>
																<div>&nbsp;</div>
																<div class="alert alert-info">
																	<?php echo JText::_("COM_Q2C_NO_PERIODIC_INCOME");?>
																</div>
															<?php endif;?>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- /.panel-body -->
						</div>
						<!-- /.panel -->
					</div>
					<!-- /.col-12 -->
				</div>
				<!-- /.row -->

				<!--Global-Quick-stats-->
				<div class="row">
					<div class="col-sm-4 col-lg-4 col-md-12">
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="<?php echo Q2C_DASHBORD_ICON_ALL_SALES; ?>"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?php echo $comquick2cartHelper->getFromattedPrice(number_format($this->totalSales, 2)); ?></div>
									</div>
								</div>
							</div>
							<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=storeorder&Itemid=' . $this->orders_itemid, false);?>">
								<div class="panel-footer">
									<span class="pull-left">
										<?php echo JText::_('COM_Q2C_TOTAL_SALE');?>
									</span>
									<span class="pull-right">
										<i class="fa fa-arrow-circle-right"></i>
									</span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<?php
					if (!empty($this->totalOrdersCount)):
						$avg = ($this->totalSales) / $this->totalOrdersCount;
					else:
						$avg = 0;
					endif;
					?>
					<div class="col-sm-4 col-lg-4 col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3 ">
										<i class="<?php echo Q2C_DASHBORD_ICON_AVG_ORDER; ?>"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?php echo $comquick2cartHelper->getFromattedPrice(number_format($avg, 2)); ?></div>
									</div>
								</div>
							</div>
							<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=storeorder&Itemid=' . $this->orders_itemid, false);?>">
								<div class="panel-footer">
									<span class="pull-left">
										<?php echo JText::_('COM_Q2C_AVG_ORDERS');?>
									</span>
									<span class="pull-right">
										<i class="fa fa-arrow-circle-right"></i>
									</span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-sm-4 col-lg-4 col-md-6">
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="<?php echo Q2C_DASHBORD_ICON_USERS; ?>"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?php echo $this->storeCustomersCount; ?></div>
									</div>
								</div>
							</div>
							<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=mycustomer&Itemid=' . $this->store_customers_itemid, false);?>">
								<div class="panel-footer">
									<span class="pull-left">
										<?php echo JText::_('COM_QUICK2CART_CUSTOMERS');?>
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
				<!--End Global-Quick-stats-->

				<!--Periodic-Graphs-->
				<div class="row">
					<div class="col-sm-12 col-lg-12 col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<div class="pull-left">
									<i class="fa fa-list fa-fw"></i>
									<span><?php echo JText::_('COM_QUICK2CART_QUICK_REPORTS'); ?></span>
								</div>
								<div class="pull-right">
									<!--
									<a href="#stripedTable" data-toggle="collapse">
										<i class="fa fa-caret-down fa-2x"></i>
									</a>
									-->
								</div>
								<div class="clearfix"></div>
							</div>
							<div id="stripedTable" class="panel-collapse collapse in">
								<div class="panel-body">
									<div class="row">
										<div class="col-sm-12 col-lg-12 col-md-12">
											<ul class="nav nav-tabs">
												<li class="active">
													<a href="#report_4" data-toggle="tab">
														<?php echo JText::_('COM_Q2C_LAST_5_ORDERS'); ?>
													</a>
												</li>
												<li class="">
													<a href="#report_1" data-toggle="tab">
														<?php echo JText::_('COM_QUICK2CART_TOP_SELLER_PRODUCTS'); ?>
													</a>
												</li>
											</ul>
											<div class="tab-content">
												<div class="tab-pane active" id="report_4">
													<?php if (!empty($this->last5orders)) : ?>
														<div>&nbsp;</div>
														<table class="table table-striped table-hover table-bordered">
															<thead>
																<tr>
																	<th><?php echo JText::_('COM_QUICK2CART_DASHB_ID'); ?></th>
																	<th><?php echo JText::_('COM_QUICK2CART_DASHB_NAME'); ?></th>
																	<th class="hidden-xs"><?php echo JText::_('COM_QUICK2CART_DASHB_DATE'); ?></th>
																	<th class=""><?php echo JText::_('COM_QUICK2CART_DASHB_AMOUNT'); ?></th>
																	<th class=""><?php echo JText::_('COM_QUICK2CART_DASHB_STATUS'); ?></th>
																</tr>
															</thead>
															<tbody>
																<?php
																foreach($this->last5orders as $ord)
																{
																	$order_currency = $comquick2cartHelper->getCurrencySymbol($ord['currency']);
																	$this->store_orders_itemid    = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=orders&layout=storeorder');
																	?>
																	<tr>
																		<td>
																			<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=order&orderid=' . $ord['id'] . '&store_id=' . $this->store_id . '&calledStoreview=1&Itemid=' . $this->store_orders_itemid, false); ?>">
																			<?php echo JHtml::tooltip(JText::sprintf('QTC_TOOLTIP_VIEW_ORDER_MSG', $ord['prefix'] . $ord['id']), JText::_('QTC_TOOLTIP_VIEW_ORDER'), '', $ord['prefix'] . $ord['id']) ;?>
																			</a>
																		</td>
																		<td><?php echo $ord['name'];?></td>
																		<td class="hidden-xs">
																			<?php
																			echo JFactory::getDate($ord['cdate'])->Format(JText::_('COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT'));
																			echo '<br/>';
																			echo JFactory::getDate($ord['cdate'])->Format(JText::_('COM_QUICK2CART_TIME_FORMAT_SHOW_AMPM'));
																			?>
																		</td>
																		<td class=""><?php echo $comquick2cartHelper->getFromattedPrice(number_format($ord['price'],2),$order_currency)?></td>
																		<td class="">
																			<?php
																			switch($ord['status'])
																			{
																				case 'C':
																				$labelClass    = 'label-success';
																				$ord['status'] = JText::_('QTC_CONFR');
																				break;

																				default:
																				case 'P':
																				$labelClass    = 'label-warning';
																				$ord['status'] = JText::_('QTC_PENDIN');
																				break;

																				case 'RF':
																				$labelClass    = 'label-danger';
																				$ord['status'] = JText::_('QTC_REFUN');
																				break;

																				case 'S' :
																				$labelClass    = 'label-success';
																				$ord['status'] = JText::_('QTC_SHIP');
																				break;

																				case 'E' :
																				$labelClass    = 'label-danger';
																				$ord['status'] = JText::_('QTC_ERR');
																				break;
																			}
																			?>
																			<span class="label label-sm <?php echo $labelClass;?> ">
																				<?php echo $ord['status'];?>
																			</span>
																		</td>
																	</tr>
																	<?php
																}
																?>
															</tbody>
														</table>
													<?php else: ?>
														<div>&nbsp;</div>
														<div class="alert alert-info">
															<?php echo JText::_("NO_STORE_PREVIOUS_ORDERS");?>
														</div>
													<?php endif; ?>
												</div>

												<div class="tab-pane" id="report_1">
													<?php if (!empty($this->topSellerProducts)) : ?>
														<div>&nbsp;</div>
														<table class="table table-striped table-hover table-bordered">
															<thead>
																<tr>
																	<th><?php echo JText::_('QTC_PRODUCT_NAM'); ?></th>
																	<th class="center"><?php echo JText::_('COM_QUICK2CART_DASHB_QUANTITY_SOLD'); ?></th>
																</tr>
															</thead>
															<tbody>
																<?php
																foreach($this->topSellerProducts as $product)
																{
																	$p_link ='index.php?option=com_quick2cart&view=productpage&layout=default&item_id='.$product['item_id'] . '&Itemid=' . $this->catpage_Itemid;
																	$product_link = JRoute::_($p_link, false);

																	?>
																	<tr>
																		<td>
																			<a href="<?php echo $product_link; ?>">
																				<?php echo $product['name']; ?>
																			</a>
																		</td>
																		<td class="center"><?php echo $product['qty']; ?></td>
																	</tr>
																	<?php
																}
																?>
															</tbody>
														</table>
													<?php else: ?>
														<div>&nbsp;</div>
														<div class="alert alert-info">
															<?php echo JText::_("NO_STORE_PREVIOUS_ORDERS");?>
														</div>
													<?php endif; ?>
												</div>
											</div>
											<!-- /.tab-content -->
										</div>
										<!-- /.col-12 -->
									</div>
									<!-- /.row -->
								</div>
								<!-- /.panel-body -->
							</div>
							<!-- /#stripedTable -->
						</div>
						<!-- /.panel -->
					</div>
					<!-- /.col-12 -->
				</div>
				<!-- /.row -->
			</div>
			<!-- /.tjDB TJ Dashboard -->
		</div>
		<!-- /.tjBs3TJ TJ Bootstrap3 -->

		<!--Store-info-->
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<?php if (!empty($this->storeDetailInfo)) : ?>
					<div>
						<?php
						$this->editstoreBtn  = 1;
						$view = $comquick2cartHelper->getViewpath('vendor', 'storeinfo');
						ob_start();
						include($view);
						$html = ob_get_contents();
						ob_end_clean();
						echo $html;
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="vendor" />
		<input type="hidden" name="layout" value="cp" />

	</form>
</div>
<!-- end of techjoomla-bootstrap-->

<script type="text/javascript">
	<?php if ($chartsDataFlag): ?>
		Morris.Area({
			element: 'q2c_chart_amount',
			data: <?php echo $incomedata[0];?>,
			xkey: 'period',
			ykeys: ['amount'],
			labels: ['<?php echo JText::_('COM_QUICK2CART_STORE_SALES_AMOUNT'); ?>'],
			lineWidth: 2,
			hideHover: 'auto',
			lineColors: ["#30a1ec"]
		});

		function drawOrdersChart()
		{
			setTimeout(function(){
				techjoomla.jQuery('#q2c_chart_orders').html('');

				Morris.Area({
					element: 'q2c_chart_orders',
					data: <?php echo $incomedata[1]; ?>,
					xkey: 'period',
					ykeys: ['orders'],
					labels: ['<?php echo JText::_('COM_QUICK2CART_STORE_ORDERS_PLACED'); ?>'],
					lineWidth: 2,
					hideHover: 'auto',
					lineColors: ["#8ac368"]
				});
			}, 300);
		}
	<?php endif; ?>
</script>
