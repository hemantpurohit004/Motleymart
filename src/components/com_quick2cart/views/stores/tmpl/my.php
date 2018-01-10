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

$user = JFactory::getUser();
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$strapperClass = Q2C_WRAPPER_CLASS;

// Check user is logged or not.
if (!$user->id)
{
	?>
	<div class="<?php echo $strapperClass; ?>" >
		<div class="well" >
			<div class="alert alert-danger">
				<span><?php echo JText::_('QTC_LOGIN'); ?></span>
			</div>
		</div>
	</div>
	<?php
	return false;
}

/*if (empty($this->items))
{
	?>
	<div class="<?php echo $strapperClass; ?>" >
		<?php
			$createStore_link = JRoute::_('index.php?option=com_quick2cart&view=vendor&layout=createstore' . '&Itemid=' . $this->createstore_itemid);
			$clickhere = '<a href="' . $createStore_link . '">' . JText::_('QTC_CLICK_HERE') . '</a>' . JText::_('QTC_TO_CREATE_STORE');

			JFactory::getApplication()->enqueueMessage(JText::sprintf('NO_STORE_FOUND', $clickhere), 'Notice');
		?>
	</div>
	<?php
	return false;
}
else{
	$this->store_id = $this->items[0]->id;
}*/
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task=='vendor.addNew')
		{
			Joomla.submitform(task);

			return true;
		}
		else if (task=='vendor.edit')
		{
			if (document.adminForm.boxchecked.value===0)
			{
				alert("<?php echo $this->escape(JText::_('COM_QUICK2CART_NO_STORE_SELECTED')); ?>");

				return;
			}
			else if (document.adminForm.boxchecked.value > 1)
			{
				alert("<?php echo $this->escape(JText::_('COM_QUICK2CART_MAKE_ONE_SEL')); ?>");

				return;
			}

			Joomla.submitform(task);
		}
		else
		{
			if (document.adminForm.boxchecked.value==0)
			{
				alert("<?php echo $this->escape(JText::_('COM_QUICK2CART_NO_STORE_SELECTED')); ?>");

				return false;
			}
			switch(task)
			{
				case 'stores.publish':
					Joomla.submitform(task);
				break

				case 'stores.unpublish':
					<?php
					$admin_approval_stores = (int) $this->params->get('admin_approval_stores');

					if ($admin_approval_stores) :
					?>
						if (confirm("<?php echo JText::_('COM_QUICK2CART_MSG_CONFIRM_UNPUBLISH_STORE'); ?>"))
						{
							Joomla.submitform(task);
						}
						else
						{
							return false;
						}
					<?php
					else:
					?>
						Joomla.submitform(task);
					<?php
					endif;
					?>
				break

				case 'stores.delete':
					if (confirm("<?php echo JText::_('COM_QUICK2CART_DELETE_CONFIRM_VENDER'); ?>"))
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

<div class="<?php echo Q2C_WRAPPER_CLASS; ?> my-stores">
	<form method="post" name="adminForm" id="adminForm" class="form-validate">
		<?php
		$active = 'my_stores';
		ob_start();
		include($this->toolbar_view_path);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		?>

		<legend><?php echo JText::_('COM_QUICK2CART_MY_STORES')?></legend>

		<div class="hidden-xs">
		<?php echo $this->toolbarHTML;?>
		</div>
		<div class="clearfix"> </div>
		<hr class="hr-condensed" />
		<div id="qtc-filter-bar" class="qtc-btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search"
				placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_STORES'); ?>"
				value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				class="qtc-hasTooltip input-medium"
				title="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_STORES'); ?>" />
			</div>

			<div class="qtc-btn-group pull-left">
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
				<div class="qtc-btn-group pull-right hidden-xs">
					<label for="limit" class="element-invisible">
						<?php echo JText::_('COM_QUICK2CART_SEARCH_SEARCHLIMIT_DESC'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>

			<div class="qtc-btn-group pull-right hidden-xs">
			<?php
				echo JHtml::_('select.genericlist', $this->statuses, "filter_published", 'class="input-medium" size="1" onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.state'));
			?>
			</div>
		</div>

		<div class="clearfix"> &nbsp;</div><br/>

		<?php
		if (empty($this->items)) :
		 ?>
			<div class="clearfix">&nbsp;</div>
			<div class="alert alert-warning">
				<?php echo JText::_('COM_QUICK2CART_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php
		else : ?>
			<div id='no-more-tables'>
			<table class="table table-striped table-bordered table-responsive" id="storeList">
				<thead>
					<tr>
						<th class="q2c_width_1 nowrap center hidden-xs">
							<input type="checkbox" name="checkall-toggle" value=""
							title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
							onclick="Joomla.checkAll(this)" />
						</th>

						<th class=''>
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_STORE_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>

						<?php if (isset($this->items[0]->published)): ?>
							<th class="q2c_width_1 nowrap center">
								<?php echo JHtml::_('grid.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>

						<th class='q2c_width_15 '>
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_STORE_EMAIL', 'a.store_email', $listDirn, $listOrder); ?>
						</th>

						<th class='q2c_width_20 '>
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_STORE_PHONE', 'a.phone', $listDirn, $listOrder); ?>
						</th>

<!--
						<th class='q2c_width_10 hidden-xs'>
							<?php echo JText::_('STORE_ROLE'); ?>
						</th>
-->
					</tr>
				</thead>

				<tbody>
					<?php
					foreach ($this->items as $i => $item):
						$ordering = ($listOrder == 'a.ordering');
						$canCreate = $user->authorise('core.create', 'com_quick2cart');
						$canEditOwn = $user->authorise('core.edit.own', 'com_quick2cart');
						//$canCheckin = $user->authorise('core.manage', 'com_quick2cart');
						$canChange = $user->authorise('core.edit.state', 'com_quick2cart');
					?>

						<tr class="row<?php echo $i % 2; ?>">
							<td class="q2c_width_1 nowrap center hidden-xs" >
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>

							<td class="qtcWordWrap" data-title="<?php echo JText::_('COM_QUICK2CART_STORE_TITLE');?>">
								<?php if ($canEditOwn) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&view=vendor&layout=createstore&store_id=' . (int) $item->id); ?>"
									title="<?php echo JText::_('COM_QUICK2CART_EDIT_ITEM_LINK'); ?>">
										<?php echo $this->escape($item->title); ?>
									</a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</td>

							<?php if (isset($this->items[0]->published)): ?>
								<td class="q2c_width_1 nowrap center"  data-title="<?php echo JText::_('JSTATUS');?>">
									<a class=" "
										href="javascript:void(0);"
										title="<?php echo ( $item->published ) ? JText::_('QTC_UNPUBLISH') : JText::_('QTC_PUBLISH'); ;?>"
										onclick="document.adminForm.cb<?php echo $i;?>.checked=1; document.adminForm.boxchecked.value=1; Joomla.submitbutton('<?php echo ( $item->published ) ? 'stores.unpublish' : 'stores.publish';?>');">
											<img class="q2c_button_publish" src="<?php echo JUri::root(true);?>/components/com_quick2cart/assets/images/<?php echo ($item->published) ? 'publish.png' : 'unpublish.png';?>"/>
									</a>
								</td>
							<?php endif; ?>

							<td class="q2c_width_15 qtcWordWrap" data-title="<?php echo JText::_('COM_QUICK2CART_STORE_EMAIL');?>">
								<?php echo $item->store_email; ?>
							</td>

							<td class="q2c_width_20 " data-title="<?php echo JText::_('COM_QUICK2CART_STORE_PHONE');?>">
								<?php echo empty($item->phone) ? "-" : $item->phone; ?>
							</td>

<!--
							<td class="q2c_width_10 hidden-xs small">
								<?php echo $item->role;?>
							</td>
-->
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			</div>
			<?php if (JVERSION >= '3.0'): ?>
				<?php echo $this->pagination->getListFooter(); ?>
			<?php else: ?>
				<div class="pager">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="stores" />
		<input type="hidden" name="layout" value="my" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
