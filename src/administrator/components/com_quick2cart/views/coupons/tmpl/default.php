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

// Allow adding non select list filters
if (! empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<script type="text/javascript">
	Joomla.submitbutton = function(action)
	{
		if (action=='coupons.publish' || action=='coupons.unpublish')
		{
			Joomla.submitform(action);
		}
		elseif (action=='coupons.delete')
		{
			var r=confirm("<?php echo JText::_('QTC_DELETE_CONFIRM_COUPON');?>");
			if (r==true)
			{
				var aa;
			}
			else
			{
				return;
			}
		}
		else
		{
			window.location = 'index.php?option=com_quick2cart&view=coupons';
		}

		var form = document.adminForm;
		submitform( action );

		return;
	}
</script>

<div class="<?php echo Q2C_WRAPPER_CLASS;?> q2c-coupons">
	<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&view=coupons'); ?>"
		method="post" name="adminForm" id="adminForm">

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
				placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_COUPONS'); ?>"
				value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				class="hasTooltip"
				title="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_COUPONS'); ?>" />
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

			<div class="btn-group pull-right hidden-phone">
				<?php
				echo JHtml::_('select.genericlist', $this->publish_states, "filter_published", 'class="input-medium" size="1" onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.published'));
				?>
			</div>
			<div class="clearfix"> </div>
		</div>

		<div class="clearfix"></div>

		<?php if (empty($this->items)) : ?>
			<div class="clearfix">&nbsp;</div>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_QUICK2CART_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php
		else : ?>
			<div id='no-more-tables'>
				<table class="table table-striped" id="couponList">
					<thead>
						<tr>
							<th class="q2c_width_1 center"><input
								type="checkbox" name="checkall-toggle" value=""
								title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
								onclick="Joomla.checkAll(this)" />
							</th>

							<th class="q2c_width_1 center">
								<?php echo JHtml::_('grid.sort', 'C_PUB', 'published', $listDirn, $listOrder ); ?>
							</th>

							<th class="left" align="left">
								<?php echo JHtml::_('grid.sort', 'C_NAM', 'name', $listDirn, $listOrder ); ?>
							</th>

							<th class="q2c_width_10 nowrap left">
								<?php echo JHtml::_('grid.sort', 'C_COD', 'code', $listDirn, $listOrder ); ?>
							</th>

							<th class="q2c_width_10 left hidden-phone">
								<?php echo JHtml::_('grid.sort', 'C_TYP', 'val_type', $listDirn, $listOrder ); ?>
							</th>

							<th class="q2c_width_5 nowrap">
								<?php echo JHtml::_('grid.sort', 'BACKEND_COUPAN_VALUE', 'value', $listDirn, $listOrder ); ?>
							</th>

							<th class="q2c_width_5 nowrap hidden-phone hidden-tablet">
								<?php echo JHtml::_('grid.sort', 'M_USE', 'max_use', $listDirn, $listOrder ); ?>
							</th>

							<th class="q2c_width_5 nowrap center hidden-phone">
								<?php echo JHtml::_('grid.sort', 'C_VALF', 'from_date', $listDirn, $listOrder ); ?>
							</th>

							<th class="q2c_width_5 nowrap center hidden-phone">
								<?php echo JHtml::_('grid.sort', 'C_EXP', 'exp_date', $listDirn, $listOrder ); ?>
							</th>

							<th class="q2c_width_10 left hidden-phone hidden-tablet">
								<?php echo JHtml::_('grid.sort', 'C_STORE_NAME', 'store_id', $listDirn, $listOrder ); ?>
							</th>

							<th class="q2c_width_5 nowrap center hidden-phone hidden-tablet">
								<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_ID', 'id', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php
						$i = 0;
						foreach ($this->items as $i => $item)
						{
							$ordering = ($listOrder == 'a.ordering');
							$canCreate = $user->authorise('core.create', 'com_quick2cart');
							$canEdit = $user->authorise('core.edit', 'com_quick2cart');
							$canCheckin = $user->authorise('core.manage', 'com_quick2cart');
							$canChange = $user->authorise('core.edit.state', 'com_quick2cart');
							?>

							<tr class="row<?php echo $i % 2;?>" data-title="<?php echo JText::_('QTC_AMOUNT');?>">
								<td class="q2c_width_1 center" data-title="<?php echo JText::_('COM_QUICK2CART_GRID_SELECT');?>">
									<?php echo JHtml::_('grid.id', $i, $item->id);?>
								</td>

								<?php if (isset($item->published)): ?>
									<td class="q2c_width_1 center" data-title="<?php echo JText::_('C_PUB');?>">
										<?php echo JHtml::_('jgrid.published', $item->published, $i, 'coupons.', $canChange, 'cb'); ?>
									</td>
								<?php endif; ?>

								<td class="left" data-title="<?php echo JText::_('C_NAM');?>">
									<a href="<?php echo 'index.php?option=com_quick2cart&task=coupon.edit&id=' . $item->id; ?>"
									title="<?php echo JText::_('COM_QUICK2CART_PAYOUT_ID_TOOLTIP');?>">
										<?php echo $item->name;
									?>
									</a>
								</td>

								<td class="q2c_width_10 nowrap left" data-title="<?php echo JText::_('C_COD');?>"><?php echo stripcslashes($item->code);?></td>

								<td class="q2c_width_10 left hidden-phone" data-title="<?php echo JText::_('C_TYP');?>">
									<?php
									if ($item->val_type==0)
									{
										echo JText::_( "C_FLAT");
									}
									else
									{
										echo JText::_( "C_PER");
									}
									?>
								</td>

								<td class="q2c_width_5 nowrap" data-title="<?php echo JText::_('BACKEND_COUPAN_VALUE');?>"><?php echo $item->value;?></td>

								<td class="q2c_width_5 nowrap hidden-phone hidden-tablet">
									<?php echo $item->max_use;?>
								</td>

								<td class="q2c_width_5 nowrap center hidden-phone" data-title="<?php echo JText::_('C_VALF');?>">
									<?php
									if($item->from_date != '0000-00-00 00:00:00')
									{
										$from_date = date("Y-m-d", strtotime($item->from_date));
										echo $from_date;
									}
									else
									{
										echo "-";
									}
									?>
								</td>

								<td class="q2c_width_5 nowrap center hidden-phone" data-title="<?php echo JText::_('C_EXP');?>">
									<?php
									if($item->exp_date != '0000-00-00 00:00:00')
									{
										$exp_date = date("Y-m-d", strtotime($item->exp_date));
										echo $exp_date ;
									}
									else
									{
										echo "-";
									}
									?>
								</td>

								<td class="q2c_width_10 left hidden-phone hidden-tablet">
									<?php echo $item->store_name;?>
								</td>

								<td class="q2c_width_5 nowrap center hidden-phone hidden-tablet">
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php
						$i++;
						}
						?>
					</tbody>

					<?php if (JVERSION < '3.0'): ?>
					<tfoot>
						<tr>
							<td colspan="9">
								<div class="pager pull-left">
									<?php echo $this->pagination->getListFooter(); ?>
								</div>
							</td>
						</tr>
					</tfoot>
					<?php endif; ?>
				</table>
			</div>

			<?php if (JVERSION >= '3.0'): ?>
				<?php echo $this->pagination->getListFooter(); ?>
			<?php endif; ?>
		<?php endif; ?>

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="coupons" />
		<input type="hidden" name="layout" value="default" />

		<input type="hidden" id="task" name="task" value="" />

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
