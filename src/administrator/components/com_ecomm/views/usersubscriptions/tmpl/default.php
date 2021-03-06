<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jticketing
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$this->sidebar = JHtmlSidebar::render();
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<?php
	if (!empty($this->sidebar)):?>
			<div id="j-sidebar-container" class="span2">
				<?php echo $this->sidebar;?>
			</div>
			<div id="j-main-container" class="span10">
	<?php else :?>
			<div id="j-main-container">
	<?php endif;?>
<form action="<?php echo JRoute::_('index.php?option=com_ecomm&view=usersubscriptions'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span10">
		<?php
			echo JLayoutHelper::render(
				'joomla.searchtools.default',
				array('view' => $this)
			);
		?>
		</div>
	</div>
	<div class="btn-group pull-right hidden-phone">
		<label for="limit" class="element-invisible">
			<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
		</label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
	<br>
	<div class="clearfix"></div>
	<?php if(empty($this->items)) :?>
		<div class="clearfix">&nbsp;</div>
			<div class="alert alert-no-items">
				<?php echo JText::_("No match found"); ?>
		</div>
		<?php else :?>
		<div class="col-md-10 col-md-offset-2">
			<table class="table table-striped table-hover">
				<thead>
				<tr>
					<th width="5%" class="nowrap center">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th width="10%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', JText::_("State"), 'state', $listDirn, $listOrder);?>
					</th>
					<th width="20%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', JText::_("User Name"), 'user_id', $listDirn, $listOrder);?>
					</th>
					<th width="20%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', JText::_("Subscription Plan"), 'subscription_id', $listDirn, $listOrder);?>
					</th>
					<th width="10%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', JText::_("Purchase Date"), 'purchase_date', $listDirn, $listOrder);?>
					</th>
					<th width="10%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', JText::_("Expiration Date"), 'expiration_date', $listDirn, $listOrder);?>
					</th>
					<th width="10%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', JText::_("Id"), 'id', $listDirn, $listOrder);?>
					</th>
				</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="10">
							<?php echo $this->pagination->getListFooter();
							?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php if (!empty($this->items)) : ?>

					<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_ecomm&task=subscription.edit&id=' . $row->id);
					?>
						<tr>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $row->id); ?>
							</td>
							<td class="center ">
								<!--<div class="btn-group">
									<a class="btn btn-micro  hasTooltip" href="javascript:void(0);" onclick="return listItemTask('cb0','subscriptions.unpublish')" title="" data-original-title="Unpublish Item"><span class="icon-publish" style="color: #ab0f0f;color: #32c5d2;"></span></a>
									<a href="#" onclick="return listItemTask('cb0','subscriptions.featured')" class="btn btn-micro hasTooltip" title="" data-original-title="Toggle featured status."><i class="icon-featured primary"></i></a>
								</div>-->
								<?php echo ($row->state == 1) ? 'Published' : 'Unpublished'; ?>
							</td>
							<td class="center">
								<a href="<?php echo $link;?>">
									<?php echo JFactory::getUser($row->user_id)->name; ?>
								</a>
							</td>
							<td class="center">
								<?php echo $this->helper->getSubscriptionDetails($row->subscription_id, 'title'); ?>
							</td>
							<td class="center">
								<?php echo $row->purchase_date; ?>
							</td>
							<td class="center">
								<?php echo $row->expiration_date; ?>
							</td>
							<td class="center">
								<?php echo $row->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
			<?php endif ?>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
	</div>
</div>

</form>
