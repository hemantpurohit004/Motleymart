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
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task=='couponform.addNew')
		{
			Joomla.submitform(task);

			return true;
		}
		elseif (task=='couponform.edit')
		{
			if (document.adminForm.boxchecked.value===0)
			{
				alert('<?php echo JText::_("COM_QUICK2CART_NO_SELECTION_MSG");?>');

				return;
			}
			elseif (document.adminForm.boxchecked.value > 1)
			{
				alert('<?php echo JText::_("COM_QUICK2CART_MAKE_ONE_SEL");?>');

				return;
			}

			Joomla.submitform(task);
		}
		else
		{
			if (document.adminForm.boxchecked.value==0)
			{
				alert('<?php echo JText::_("COM_QUICK2CART_NO_SELECTION_MSG");?>');

				return false;
			}
			switch(task)
			{
				case 'coupons.publish':
					Joomla.submitform(task);
				break

				case 'coupons.unpublish':
					Joomla.submitform(task);
				break

				case 'coupons.delete':
					if (confirm("<?php echo JText::_('COM_QUICK2CART_DELETE_CONFIRM_COUPON'); ?>"))
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

<div class="<?php echo Q2C_WRAPPER_CLASS; ?> my-coupons">
	<form method="post" name="adminForm" id="adminForm" class="form-validate">
		<?php
		$active = 'my_coupons';
		ob_start();
		include($this->toolbar_view_path);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		?>

		<legend>
			<?php
			if (!empty($this->store_role_list))
			{
				$app = JFactory::getApplication();
				$default = $app->getUserStateFromRequest('com_quick2cart' . '.current_store', 'current_store');
				$storehelp = new storeHelper();
				//$index = $storehelp->array_search2d($this->store_id, $this->store_role_list);
				$index = $storehelp->array_search2d($default, $this->store_role_list);

				if (is_numeric( $index))
				{
					$store_name = $this->store_role_list[$index]['title'];
				}

				echo JText::sprintf('QTC_MANAGE_STORE_COUPONS', $store_name) ;
			}
			else
			{
				echo JText::_('QTC_MANAGE_STORE_COUPONS');
			}
			?>
		</legend>

		<?php echo $this->toolbarHTML;?>

		<div class="clearfix"> </div>
		<hr class="hr-condensed" />

		<div id="qtc-filter-bar" class="qtc-btn-toolbar">
			<div class="filter-search btn-group pull-left q2c-btn-wrapper">
				<input type="text" name="filter_search" id="filter_search"
				placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_COUPONS'); ?>"
				value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				class="qtc-hasTooltip"
				title="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_COUPONS'); ?>" />
			</div>

			<div class="qtc-btn-group pull-left q2c-btn-wrapper">
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

			<div class="btn-group pull-right q2c-btn-wrapper">
				<?php
				echo JHtml::_('select.genericlist', $this->publish_states, "filter_published", 'class="input-medium"  onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.state'));
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
			<table class="table table-striped table-bordered" id="couponList">
				<thead>
					<tr>
						<th class="q2c_width_1 nowrap center">
							<input type="checkbox" name="checkall-toggle" value=""
							title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
							onclick="Joomla.checkAll(this);" />
						</th>

						<th class="">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_COUPON_LIST_NAME', 'name', $listDirn, $listOrder ); ?>
						</th>

						<th class="q2c_width_1 nowrap center">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_COUPON_LIST_PUB', 'published', $listDirn, $listOrder ); ?>
						</th>

						<th class="q2c_width_15 center hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_COUPON_LIST_COD', 'code', $listDirn, $listOrder ); ?>
						</th>

						<th class="q2c_width_15 center hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_COUPON_LIST_COUPONP_VAL', 'value', $listDirn, $listOrder ); ?>
						</th>

						<th class="q2c_width_10 center hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_COUPON_LIST_C_TYP', 'val_type', $listDirn, $listOrder ); ?>
						</th>

						<th class="q2c_width_30 hidden-phone">
							<?php echo JText::_('COM_QUICK2CART_COUPON_LIST_C_PROD'); ?>
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

						<tr class="row<?php echo $i % 2;?>">
							<td class="q2c_width_1 nowrap center">
								<?php echo JHtml::_('grid.id', $i, $item->id);?>
							</td>

							<td class="">
								<a href="<?php	echo 'index.php?option=com_quick2cart&task=couponform.edit&id=' . $item->id; ?>"
								title="<?php echo JText::_('COM_QUICK2CART_COUPON_EDIT_TOOLTIP');?>">
									<?php echo $item->name;
								?>
								</a>
							</td>

							<?php if (isset($this->items[0]->published)): ?>
								<td class="q2c_width_1 nowrap center">
									<a class=" "
										href="javascript:void(0);"
										title="<?php echo ( $item->published ) ? JText::_('QTC_UNPUBLISH') : JText::_('QTC_PUBLISH'); ;?>"
										onclick="document.adminForm.cb<?php echo $i;?>.checked=1; document.adminForm.boxchecked.value=1; Joomla.submitbutton('<?php echo ( $item->published ) ? 'coupons.unpublish' : 'coupons.publish';?>');">
											<img class="q2c_button_publish" src="<?php echo JUri::root(true);?>/components/com_quick2cart/assets/images/<?php echo ($item->published) ? 'publish.png' : 'unpublish.png';?>"/>
									</a>
								</td>
							<?php endif; ?>

							<td class="q2c_width_15 center hidden-phone small">
								<?php echo stripcslashes($item->code);?>
							</td>

							<td class="q2c_width_15 center hidden-phone small">

								<span class="small badge badge-info">
									<?php echo $item->value;?>
								</span>
							</td>

							<td class="q2c_width_10 center hidden-phone small">
								<?php
								if ($item->val_type==0)
								{
									echo JText::_("COM_QUICK2CART_COUPON_PER");
								}
								else
								{
									echo JText::_("COM_QUICK2CART_COUPON_LIST_C_FLAT");
								}
								?>
							</td>

							<td class="q2c_width_30 hidden-phone small">
								<?php
								if (isset($item->item_id_name))
								{
									echo $item->item_id_name;
								}
								else
								{
									echo '-';
								}
								?>
							</td>
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
		<input type="hidden" name="view" value="coupons" />
		<input type="hidden" name="layout" value="my" />

		<input type="hidden" id="task" name="task" value="" />

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
