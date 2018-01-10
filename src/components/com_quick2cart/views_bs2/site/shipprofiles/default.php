<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// no direct access
defined('_JEXEC') or die;
$isShippingEnabled = $this->params->get('shipping', 0);

$user = JFactory::getUser();
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = $listOrder == 'a.ordering';
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task=='shipprofileform.add')
		{
			Joomla.submitform(task);

			return true;
		}
		else
		{
			if (document.adminForm.boxchecked.value==0)
			{
				alert("<?php echo $this->escape(JText::_('COM_QUICK2CART_MESSAGE_SELECT_ITEMS')); ?>");
				return false;
			}

			switch(task)
			{
				case 'shipprofiles.publish':
					Joomla.submitform(task);
				break

				case 'shipprofiles.unpublish':
					Joomla.submitform(task);
				break

				case 'shipprofiles.delete':
					if (confirm("<?php echo JText::_('COM_QUICK2CART_DELETE_CONFIRM_SHIPPROFILE'); ?>"))
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

<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
	<form action="" method="post" name="adminForm" id="adminForm">
		<!-- Toolbar -->
		<div class="row-fluid">
			<div class="span12">
				<?php
				$active = 'taxprofiles';
				ob_start();
				include($this->toolbar_view_path);
				$html = ob_get_contents();
				ob_end_clean();
				echo $html;
				?>
			</div>
		</div>

		<div class="row-fluid">
			<legend>
				<?php echo Jtext::_('COM_QUICK2CART_SHIPPROFILE_S_MANAGE_LIST_LEGEND'); ?>
			</legend>

			<!-- Help msg -->
			<div class="alert alert-info">
				<?php echo JText::_('COM_QUICK2CART_SHIPPROFILE_SETUP_HELP'); ?>
			</div>

			<?php
			// Shipping is diabled msg
			if ($isShippingEnabled == 0)
			{
				?>
				<div class="alert alert-error">
					<?php echo JText::_('COM_QUICK2CART_U_HV_DISABLED_SHIPPING_OPTION_HELP_MSG'); ?>
				</div>
				<?php
			}
			?>

			<!-- Toolbar buttons -->
			<?php echo $this->toolbarHTML;?>

			<div class="clearfix"> </div>
			<hr class="hr-condensed" />
		</div>

		<div id="filter-bar" class="qtc-btn-toolbar">
			<div class="filter-search btn-group pull-left q2c-btn-wrapper">
				<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_SEARCH_FILTER'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="qtc-hasTooltip"
					title="<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_SEARCH_FILTER'); ?>" />
			</div>

			<div class="btn-group pull-left q2c-btn-wrapper">
				<button type="submit" class="btn qtc-hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
					<i class="<?php echo QTC_ICON_SEARCH; ?>"></i>
				</button>
				<button type="button" class="btn qtc-hasTooltip"
				title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
				onclick="document.id('filter_search').value='';this.form.submit();">
					<i class="<?php echo QTC_ICON_REMOVE; ?>"></i>
				</button>
			</div>

			<?php if (JVERSION >= '3.0') : ?>
				<div class="btn-group pull-right q2c-btn-wrapper">
					<label for="limit" class="element-invisible">
						<?php echo JText::_('COM_QUICK2CART_SEARCH_SEARCHLIMIT_DESC'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>

			<div class="btn-group pull-right hidden-phone q2c-btn-wrapper">
				<?php
				echo JHtml::_('select.genericlist', $this->publish_states, "filter_published", 'class="input-medium"  onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.state'));
				?>
			</div>
			<div class="btn-group pull-right hidden-phone q2c-btn-wrapper">
				<?php
				echo JHtml::_('select.genericlist', $this->stores, "filter_store", 'class="input-medium"  onchange="document.adminForm.submit();" name="filter_store"', "id", "title", $this->state->get('filter.stores'));
				?>
			</div>
		</div>

		<div class="clearfix"></div>

		<?php if (empty($this->items)) : ?>
			<div class="clearfix">&nbsp;</div>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_QUICK2CART_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php
		else : ?>
			<table class="table table-striped table-bordered" id="shippingProfilesList">
				<thead>
					<tr>
						<th class="q2c_width_1">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>

						<th>
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_SHIPPROFILE_S_SHIPPROFILE_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>

						<th>
							<?php echo JText::_('COM_QUICK2CART_STORE_NAME'); ?>
						</th>

						<?php if (isset($this->items[0]->state)): ?>
							<th class="hidden-phone nowrap center q2c_width_20">
								<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>
					</tr>
				</thead>

				<tbody>
					<?php
					foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'a.ordering');
						$canCreate	= $user->authorise('core.create', 'com_quick2cart');
						$canEdit	= $user->authorise('core.edit.own', 'com_quick2cart');
						$canCheckin	= $user->authorise('core.manage', 'com_quick2cart');
						$canChange	= $user->authorise('core.edit.state', 'com_quick2cart');
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="q2c_width_1">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>

							<td>
								<?php if (isset($item->checked_out) && $item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'shipprofiles.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&task=shipprofileform.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->name); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->name); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php echo $item->store_title?>
							</td>

							<?php
							if (isset($this->items[0]->state)): ?>
								<td class="hidden-phone center q2c_width_10">
									<a class=" "
										href="javascript:void(0);"
										title="<?php echo ( $item->state ) ? JText::_('QTC_UNPUBLISH') : JText::_('QTC_PUBLISH'); ;?>"
										onclick="document.adminForm.cb<?php echo $i;?>.checked=1; document.adminForm.boxchecked.value=1; Joomla.submitbutton('<?php echo ($item->state) ? 'zones.unpublish' : 'zones.publish';?>');">
										<img class="q2c_button_publish" src="<?php echo JUri::root(true);?>/components/com_quick2cart/assets/images/<?php echo ($item->state) ? 'publish.png' : 'unpublish.png';?>"/>
									</a>
								</td>
								<?php
							endif; ?>
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

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="shipprofiles" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
