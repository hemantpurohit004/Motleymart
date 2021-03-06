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
<form action="<?php echo JRoute::_('index.php?option=com_ecomm&view=feedbacks'); ?>" method="post" name="adminForm" id="adminForm">
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
					<!--<th width="25%" class="nowrap center">
						<?php //echo JHtml::_('grid.sort', JText::_("Name"), 'name', $listDirn, $listOrder);?>
					</th>
					<th width="10%" class="nowrap">
						<?php //echo JHtml::_('grid.sort', JText::_("Mobile No"), 'mobile_no', $listDirn, $listOrder);?>
					</th>-->
					<th width="25%" class="nowrap">
						<?php echo JHtml::_('grid.sort', JText::_("Email"), 'email', $listDirn, $listOrder);?>
					</th>
					<th width="50%" class="nowrap">
						<?php echo JHtml::_('grid.sort', JText::_("Feedback"), 'feedback', $listDirn, $listOrder);?>
					</th>
					<!--<th width="10%" class="nowrap center">
						<?php //echo JHtml::_('grid.sort', JText::_("Rating"), 'rating', $listDirn, $listOrder);?>
					</th>-->
					<th width="10%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', JText::_("Created Date"), 'created_date', $listDirn, $listOrder);?>
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
					$feedbackLink = JRoute::_('index.php?option=com_ecomm&task=feedback.edit&id=' . $row->id);
					?>
						<tr>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $row->id); ?>
							</td>
							<!--<td class="center">
								<a href="<?php echo $feedbackLink;?>">
									<?php //echo $row->name;?>
								</a>
							</td>
							<td class="">
								<?php //echo $row->mobile_no; ?>
							</td>-->
							<td class="">								
								<?php echo $row->email; ?>
							</td>
							<td class="">								
								<?php echo $row->feedback; ?>
							</td>
							<!--<td class="center">
								<span class="badge badge-info">
									<?php //echo $row->rating; ?>
								</span>
							</td>-->
							<td class="center">
								<?php echo $row->created_date; ?>
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
