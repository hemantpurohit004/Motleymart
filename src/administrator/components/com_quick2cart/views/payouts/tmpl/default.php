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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_quick2cart');
$saveOrder = $listOrder == 'a.ordering';
?>

<?php
// Joomla Component Creator code to allow adding non select list filters
if (! empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<script type="text/javascript">
	Joomla.submitbutton = function(action)
	{
		if (action=='payouts.delete')
		{
			var r=confirm("<?php echo JText::_('COM_QUICK2CART_DELETE_CONFIRM_PAYOUTS');?>");
			if (r===false)
			{
				return;
			}
		}
		else
		{
			window.location = 'index.php?option=com_quick2cart&view=payouts';
		}

		var form = document.adminForm;
		submitform(action);

		return;
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&view=payouts'); ?>"
		method="post" name="adminForm" id="adminForm">
		<div class="<?php echo Q2C_WRAPPER_CLASS;?> q2c-payouts">
		<?php
		// JHtmlsidebar for menu.
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

		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search"
				placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_PAYOUTS'); ?>"
				value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				class="hasTooltip"
				title="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_PAYOUTS'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip"
				title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
					<i class="icon-search"></i>
				</button>
				<button type="button" class="btn hasTooltip"
				title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
				onclick="document.id('filter_search').value='';this.form.submit();">
					<i class="icon-remove"></i>
				</button>
			</div>

			<?php if (JVERSION >= '3.0') : ?>
				<div class="btn-group pull-right btn-wrapper">
					<label for="limit" class="element-invisible">
						<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>
			<div class="clearfix">&nbsp;</div>
		</div>

		<div class="clearfix">&nbsp;</div>

		<?php if (empty($this->items)) : ?>
			<div class="clearfix">&nbsp;</div>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_QUICK2CART_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php
		else : ?>
			<table class="table table-striped" id="payoutList">
				<thead>
					<tr>
						<th class="q2c_width_1">
							<input type="checkbox" name="checkall-toggle" value=""
							title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
							onclick="Joomla.checkAll(this)" />
						</th>

						<?php if (isset($this->items[0]->com_quick2cart)): ?>
							<th width="1%" class="nowrap center">
								<?php echo JHtml::_('grid.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>

						<th class='q2c_width_10'>
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_PAYOUT_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>

						<th class="q2c_width_10">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_PAYEE_NAME', 'a.payee_name', $listDirn, $listOrder); ?>
						</th>

						<th class="q2c_width_10 hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_PAYPAL_EMAIL', 'a.email_id', $listDirn, $listOrder); ?>
						</th>
						<th class="q2c_width_10 hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_TRANSACTION_ID', 'a.transaction_id', $listDirn, $listOrder); ?>
						</th>
						<th class='q2c_width_10 hidden-phone'>
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_PAYOUT_DATE', 'a.date', $listDirn, $listOrder); ?>
						</th>

						<th class='q2c_width_5 hidden-phone'>
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_STATUS', 'a.status', $listDirn, $listOrder); ?>
						</th>

						<th class='q2c_width_5'>
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_CASHBACK_AMOUNT', 'a.amount', $listDirn, $listOrder); ?>
						</th>

					</tr>
				</thead>

				<tbody>
					<?php
					$i = 0;

					foreach ($this->items as $payout)
					{
					?>
						<tr class="row<?php echo $i % 2;?>">
							<td align="center">
								<?php echo JHtml::_('grid.id', $i, $payout->id);?>
							</td>

							<td class=''>
								<a href="<?php

								$original_payout_id = $payout->id;

								if (strlen($payout->id) <= 6)
								{
									$append = '';

									for ($z=0; $z < (6-strlen($payout->id)); $z++)
									{
										$append .= '0';
									}

									$payout->id = $append . $payout->id;
								}

								echo 'index.php?option=com_quick2cart&task=payout.edit&id=' . $original_payout_id; ?>"
								title="<?php echo JText::_('COM_QUICK2CART_PAYOUT_ID_TOOLTIP');?>">
									<?php echo $payout->id;
								?>
								</a>
							</td>

							<td class='q2c_width_10 small'><?php echo $payout->payee_name;?></td>

							<td class='q2c_width_10 small hidden-phone'><?php echo $payout->email_id;?></td>

							<td class='q2c_width_10 small hidden-phone'><?php echo $payout->transaction_id;?></td>

							<td class='q2c_width_10 small hidden-phone'>
								<?php echo JHtml::_('date', $payout->date, "Y-m-d");?>
							</td>

							<td class='q2c_width_5 small hidden-phone'>
								<?php
								if ($payout->status==1)
								{
									echo JText::_('COM_QUICK2CART_PAID');
								}
								else
								{
									echo JText::_('COM_QUICK2CART_NOT_PAID');
								}
								?>
							</td>

							<td class='q2c_width_5'><?php echo $payout->amount; ?></td>
						</tr>
					<?php
					$i++;
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

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="payouts" />
		<input type="hidden" name="layout" value="default" />
		<!--
		<input type="hidden" id="controller" name="controller" value="" />
		-->
		<input type="hidden" id="task" name="task" value="" />

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
