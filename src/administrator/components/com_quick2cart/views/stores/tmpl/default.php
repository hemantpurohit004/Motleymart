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

$sortFields = $this->getSortFields();
?>

<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order !== '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn, '');
	}

	Joomla.submitbutton = function(task)
	{
		if(task=='vendor.addNew')
		{
			Joomla.submitform(task);
		}
		else if(task=='vendor.edit')
		{
			if (document.adminForm.boxchecked.value===0)
			{
				alert('<?php echo JText::_("QTC_MAKE_SEL");?>');
				return;
			}
			else if(document.adminForm.boxchecked.value > 1)
			{
				alert('<?php echo JText::_("QTC_MAKE_ONE_SEL");?>');
				return;
			}

			Joomla.submitform(task);
		}
		else
		{
			if(document.adminForm.boxchecked.value==0)
			{
				alert('<?php echo JText::_("QTC_MAKE_SEL");?>');
				return false;
			}
			switch(task)
			{
				case 'stores.publish':
					Joomla.submitform(task);
				break
				case 'stores.unpublish':
					Joomla.submitform(task);
				break
				case 'stores.trash':
					if(confirm("<?php echo JText::_('COM_QUICK2CART_TRASH_CONFIRM_VENDER'); ?>"))
					{
						Joomla.submitform(task);
					}
					else
					{
						return false;
					}
				break
				case 'stores.delete':
					if(confirm("<?php echo JText::_('QTC_DELETE_CONFIRM_VENDER'); ?>"))
					{
						Joomla.submitform(task);
					}
					else
					{
						return false;
					}
				break
			}
		}
	}
</script>

<?php
// Allow adding non select list filters
if (! empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>
<form
		action="<?php echo JRoute::_('index.php?option=com_quick2cart&view=stores'); ?>"
		method="post" name="adminForm" id="adminForm">
		<div class="<?php echo Q2C_WRAPPER_CLASS;?> q2c-stores">

		<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">

		<?php else : ?>
			<div id="j-main-container">
			<?php endif; ?>

			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_STORES'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_STORES'); ?>" />
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
					<div class="btn-group pull-right">
						<label for="limit" class="element-invisible">
							<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
						</label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>

					<div class="btn-group pull-right hidden-phone hidden-tablet">
						<label for="directionTable" class="element-invisible">
							<?php echo JText::_('JFIELD_ORDERING_DESC'); ?>
						</label>
						<select name="directionTable" id="directionTable"
							class="input-medium" onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
							<option value="asc"
								<?php
									if ($listDirn == 'asc')
									{
										echo 'selected="selected"';
									}
								?>>
									<?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
							</option>
							<option value="desc"
								<?php
								if ($listDirn == 'desc')
								{
									echo 'selected="selected"';
								}
								?>>
									<?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?>
							</option>
						</select>
					</div>

					<div class="btn-group pull-right hidden-phone hidden-tablet">
						<label for="sortTable" class="element-invisible">
							<?php echo JText::_('JGLOBAL_SORT_BY'); ?>
						</label>
						<select name="sortTable" id="sortTable" class="input-medium"
							onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
							<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
						</select>
					</div>
				<?php endif; ?>

				<div class="btn-group pull-right hidden-phone">
				<?php
					echo JHtml::_('select.genericlist', $this->statuses, "filter_published", 'class="input-medium" size="1" onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.state'));
				?>
				</div>

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
				<table class="table table-striped" id="storeList">
					<thead>
						<tr>
							<th class="q2c_width_1">
								<input type="checkbox" name="checkall-toggle" value=""
								title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
								onclick="Joomla.checkAll(this)" />
							</th>

							<?php if (isset($this->items[0]->published)): ?>
								<th class="nowrap q2c_width_1 center">
									<?php echo JHtml::_('grid.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>

							<th class='left'>
								<?php echo JHtml::_('grid.sort', 'STORE_TITLE', 'a.title', $listDirn, $listOrder); ?>
							</th>

							<th class="q2c_width_15 center hidden-phone">
								<?php echo JHtml::_('grid.sort', 'VENDOR_NAME', 'u.name', $listDirn, $listOrder); ?>
							</th>

							<th class='q2c_width_15 left hidden-phone'>
								<?php echo JHtml::_('grid.sort', 'STORE_EMAIL', 'a.store_email', $listDirn, $listOrder); ?>
							</th>

							<th class='q2c_width_15 left hidden-phone'>
								<?php echo JHtml::_('grid.sort', 'STORE_PHONE', 'a.phone', $listDirn, $listOrder); ?>
							</th>

							<?php if (isset($this->items[0]->id)): ?>
								<th class="q2c_width_5 nowrap center hidden-phone">
									<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>
						</tr>
					</thead>

					<tbody>
						<?php
						foreach ($this->items as $i => $item):
							$ordering = ($listOrder == 'a.ordering');
							$canCreate = $user->authorise('core.create', 'com_quick2cart');
							$canEdit = $user->authorise('core.edit', 'com_quick2cart');
							$canCheckin = $user->authorise('core.manage', 'com_quick2cart');
							$canChange = $user->authorise('core.edit.state', 'com_quick2cart');
						?>

						<tr class="row<?php echo $i % 2; ?>">

							<td class="q2c_width_1 center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>

							<?php if (isset($this->items[0]->published)): ?>
								<td class="q2c_width_1 center">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'stores.', $canChange, 'cb'); ?>
								</td>
							<?php endif; ?>

							<td>
								<?php if (isset($item->checked_out) && $item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'stores.', $canCheckin); ?>
								<?php endif; ?>

								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=vendor&layout=createstore&store_id=' . (int) $item->id); ?>"
									title="<?php echo JText::_('COM_QUICK2CART_EDIT_ITEM_LINK'); ?>">
										<?php echo $this->escape($item->title); ?>
									</a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</td>

							<td class="q2c_width_15 center hidden-phone small">
								<?php
								echo $item->name . '<br/><i>(' .
								$item->username . ')</i>';
								?>
							</td>

							<td class="q2c_width_15 left hidden-phone small">
								<?php echo $item->store_email; ?>
							</td>

							<td class="q2c_width_15 left hidden-phone small">
								<?php echo $item->phone; ?>
							</td>

							<?php if (isset($this->items[0]->id)): ?>
								<td class="q2c_width_5 center hidden-phone">
									<?php echo (int) $item->id; ?>
								</td>
							<?php endif; ?>
						</tr>
						<?php endforeach; ?>
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

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
