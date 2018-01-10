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
$params = JComponentHelper::getParams('com_quick2cart');
$db = JFactory::getDBO();
$user = JFactory::getUser();
$result=$this->user_info;
// check empty of result
$Itemid=( isset($this->Itemid) )?$this->Itemid:0;
$vendor_order_view=(!empty($this->store_id))?1:0;
$document = JFactory::getDocument();
$totalamount=0;
?>

<style type="text/css">
.pagination a{
	text-decoration:none;
}
</style>

<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >
	<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">
		<?php
		$helperobj = new comquick2cartHelper;
		$active = 'storecustomers';
		$order_currency = $helperobj->getCurrencySession();
		$view = $helperobj->getViewpath('vendor','toolbar');
		ob_start();
		include ($view);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		?>

		<?php
		$orders_site = (isset($this->orders_site)) ? $this->orders_site : 0;

		if ($orders_site)
		{
			?>
			<legend>
				<?php
				if (!empty($this->store_role_list))
				{
					$storehelp = new storeHelper();
					$index = $storehelp->array_search2d($this->store_id, $this->store_role_list);

					if (is_numeric($index))
					{
						$store_name = $this->store_role_list[$index]['title'];
					}

					echo JText::sprintf('QTC_STORE_CUSTOMER', $store_name);
				}
				else
				{
					echo JText::_('QTC_STORE_CUSTOMER');
				}
				?>
			</legend>
			<?php
		}
		?>

		<?php
		// ***STEP 1: check for user login or not
		if (!$user->id)
		{
			?>
				<div class="well" >
					<div class="alert alert-danger">
						<span><?php echo JText::_('QTC_LOGIN'); ?></span>
					</div>
				</div>
			</div>

			<?php
			return false;
		}
		?>

		<?php
		// ***STEP 2:CHECK whether user info is present or not .it will happen when store id is 0 or user info not found
		/*if (empty($this->user_info))
		{
			?>
				<div class="well" >
					<div class="alert alert-danger">
						<span><?php echo JText::_('QTC_STORE_INFO_NOT_FOUND');?></span>
					</div>
				</div>
			</div>

			<?php
			return false;
		}*/

		// ***step 3:: CHECK where user is autorized or not. Dont allow to display is user info if not autorize
		if (empty($this->store_authorize))
		{
			?>
				<div class="well" >
					<div class="alert alert-danger">
						<span><?php echo JText::_('QTC_NOT_AUTHORIZED_USER');?></span>
					</div>
				</div>
			</div>

			<?php
			return false;
		}
		?>

		<div class="clearfix">&nbsp; </div>

		<div id="qtc-filter-bar" class="qtc-btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search"
				placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_CUSTOMERS'); ?>"
				value="<?php echo $this->escape($this->lists['filter_search']); ?>"
				class="qtc-hasTooltip input-medium"
				title="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_CUSTOMERS'); ?>" />
			</div>

			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-default qtc-hasTooltip"
				title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
					<i class="<?php echo QTC_ICON_SEARCH; ?>"></i>
				</button>
				<button type="button" class="btn btn-default qtc-hasTooltip"
				title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
				onclick="document.id('filter_search').value='';this.form.submit();">
					<i class="<?php echo QTC_ICON_REMOVE; ?>"></i>
				</button>
			</div>

			<?php if (JVERSION >= '3.0') : ?>
				<div class=" pull-right hidden-xs ">
<!--
					<label for="limit" class="element-invisible">
						<?php //echo JText::_('COM_QUICK2CART_SEARCH_SEARCHLIMIT_DESC'); ?>
					</label>
-->
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="clearfix"> &nbsp;</div><br/>
			<div class="">
			<?php
			if (empty($result)) : ?>
				<div class="alert alert-warning">
					<?php echo JText::_('COM_QUICK2CART_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php
			else : ?>
				<table class="table table-striped table-bordered table-responsive" id="productList">
					<thead>
						<tr>
							<?php
							if (!$orders_site)
							{
								?>
								<th width="2%" align="center" class="title">
									<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($result)+1; ?>);" />
								</th>
							<?php
							}
							?>

							<th width="25%">
								<?php echo JHtml::_('grid.sort', 'QTC_CUST_NAME','firstname', $this->lists['order_Dir'], $this->lists['order']); ?>
							</th>

							<th width="23%">
								<?php echo JHtml::_('grid.sort', 'QTC_CUST_EMAIL', 'user_email', $this->lists['order_Dir'], $this->lists['order']); ?>
							</th>

							<th width="20%">
								<?php echo JHtml::_('grid.sort', 'QTC_CUST_MOB_NO', 'phone', $this->lists['order_Dir'], $this->lists['order']); ?>
							</th>

							<th width="20%">
								<?php echo JHtml::_('grid.sort', 'QTC_CUST_CITY', 'city', $this->lists['order_Dir'], $this->lists['order']); ?>
							</th>

							<th width="20%">
								<?php echo JHtml::_('grid.sort', 'QTC_CUST_COUNTRY', 'country_code', $this->lists['order_Dir'], $this->lists['order']); ?>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php
						$id=1;
						foreach($result as $orders)
						{
							?>
							<tr class="row0">
								<?php
								if (!$orders_site)
								{
									?>
									<td align="center">
										<?php echo JHtml::_('grid.id', $id, $orders->id ); ?>
									</td>
									<?php
								}
								?>

								<td>
									<a href="<?php echo  JUri::base().substr(JRoute::_('index.php?option=com_quick2cart&view=orders&layout=customerdetails&orderid='.$orders->order_id.'&store_id='.$this->store_id),strlen(JUri::base(true))+1); ?>"><?php echo JHtml::tooltip(JText::_('QTC_TOOLTIP_VIEW_CUST_INFO'), JText::_('QTC_TOOLTIP_VIEW_CINFO'), '', $orders->firstname." ".$orders->lastname ) ;?></a>
								</td>

								<td>
									<?php echo $orders->user_email; ?>
								</td>

								<td class="qtc_pending_action" >
									<?php echo $orders->phone; ?>
								</td>

								<td>
									<?php echo $orders->city; ?>
								</td>

								<td>
									<?php echo $orders->countryName; ?>
								</td>

							</tr>
						<?php
						}
						?>
					</tbody>
				</table>

				<?php if (JVERSION >= '3.0'): ?>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php else: ?>
					<div class="pager">
						<?php echo $this->pagination->getListFooter(); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<input type="hidden" name="option" value="com_quick2cart" />
		<!--
		<input type="hidden" id='hidid' name="id" value="" />
		<input type="hidden" id='hidstat' name="status" value="" />
		-->
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="view" value="orders" />
		<!--
		<input type="hidden" name="controller" value="orders" />
		-->
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
