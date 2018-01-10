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

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');

jimport('joomla.filesystem.file');

$user = JFactory::getUser();
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$input = JFactory::getApplication()->input;
$cid   = $input->get('cid', '', 'ARRAY');

// Used to store store_name against store_id.
$store_names = array();
$comquick2cartHelper = new comquick2cartHelper;

// Store details
$store_details = $this->store_details;
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task=='product.addNew')
		{
			Joomla.submitform(task);

			return true;
		}
		else if (task=='product.edit')
		{
			if (document.adminForm.boxchecked.value===0)
			{
				alert("<?php echo $this->escape(JText::_('COM_QUICK2CART_NO_PRODUCT_SELECTED')); ?>");

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
				alert("<?php echo $this->escape(JText::_('COM_QUICK2CART_NO_PRODUCT_SELECTED')); ?>");
				return false;
			}
			switch(task)
			{
				case 'category.publish':
					Joomla.submitform(task);
				break

				case 'category.unpublish':
					<?php
					$admin_approval_stores = (int) $this->params->get('admin_approval');

					if ($admin_approval_stores) :
					?>
						if (confirm("<?php echo JText::_('COM_QUICK2CART_MSG_CONFIRM_UNPUBLISH_PRODUCT'); ?>"))
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

				case 'category.delete':
					if (confirm("<?php echo JText::_('COM_QUICK2CART_DELETE_CONFIRM_PRODUCT'); ?>"))
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

<div class="<?php echo Q2C_WRAPPER_CLASS; ?> my-products">
	<form  method="post" name="adminForm" id="adminForm" class="form-validate">

		<?php
		$input      = JFactory::getApplication()->input;
		$option     = $input->get('option', '', 'STRING');

		if (!empty($this->store_role_list))
		{
			$active = 'my_products';
			ob_start();
			include($this->toolbar_view_path);
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
		}
		?>

		<legend><?php echo JText::_('COM_QUICK2CART_MY_PRODUCTS') ?></legend>

		<?php echo $this->toolbarHTML;?>

		<div class="clearfix"> </div>
		<hr class="hr-condensed" />

		<div id="filter-bar" class="qtc-btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search"
				placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_PRODUCTS'); ?>"
				value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				class="qtc-hasTooltip input-medium"
				title="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_PRODUCTS'); ?>" />
			</div>

			<div class="pull-left">
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
				<div class="pull-right hidden-phone ">
					<label for="limit" class="element-invisible">
						<?php echo JText::_('COM_QUICK2CART_SEARCH_SEARCHLIMIT_DESC'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>

			<div class="pull-right hidden-phone">
			<?php
				echo JHtml::_('select.genericlist', $this->statuses, "filter_published", 'class="input-medium"  onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.published'));
			?>
			</div>

			<div class="pull-right hidden-phone">
				<select name="filter_category" class="inputbox input-medium" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
					<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_quick2cart'), 'value', 'text', $this->state->get('filter.category'));?>
				</select>
			</div>
		</div>

		<div class="clearfix">&nbsp;</div>

		<div class="row-fluid qtc_productblog">
			<?php
			if (empty($this->items)) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_QUICK2CART_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php
			else : ?>
				<table class="table table-striped table-bordered table-responsive" id="productList">
					<thead>
						<tr>
							<th class="q2c_width_1 nowrap center">
								<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
							</th>

							<th class=''>
								<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_NAM', 'name', $listDirn, $listOrder);?>
							</th>

							<th class="q2c_width_1 nowrap center">
								<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_PUB', 'state', $listDirn, $listOrder); ?>
							</th>

							<th class="q2c_width_5 nowrap center">
								<?php echo JText::_('COM_QUICK2CART_EDIT'); ?>
							</th>

							<th class="q2c_width_15 hidden-phone">
								<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_STORE_NAME', 'store_id', $listDirn, $listOrder); ?>
							</th>

							<th class="q2c_width_15 hidden-phone">
								<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_CAT', 'category', $listDirn, $listOrder); ?>
							</th>

							<th class="q2c_width_10 nowrap center hidden-phone">
								<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_CDATE', 'cdate', $listDirn, $listOrder); ?>
							</th>

							<th class="q2c_width_1 nowrap center hidden-phone">
								<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_ID', 'item_id', $listDirn, $listOrder); ?>
							</th>
						</tr>

					</thead>

					<tbody>
						<?php
						$comquick2cartHelper = new comquick2cartHelper;
						$k = 0;

						if (!empty($this->products))
						{
							$n = count($this->products);

							for ($i=0; $i < $n; $i++)
							{
								$zone_type = '';
								$row = $this->products[$i];

								$link = $comquick2cartHelper->getProductLink($row->item_id, 'detailsLink');
								$edit_link = $comquick2cartHelper->getProductLink($row->item_id, 'editLink');
								$link = '<a href="'. $link . '">' . $row->name . '</a>';
								$edit_link = '<a href="'. $edit_link . '">' . JText::_('QTC_EDIT') . '</a>';
								?>

								<tr class="<?php echo 'row'.$k; ?>">

									<td class="q2c_width_1 nowrap center">
										<?php echo JHtml::_('grid.id', $i, $row->item_id); ?>
									</td>

									<td class=''>
										<?php echo $link; ?>
									</td>

									<td class="q2c_width_1 nowrap center">
										<a class=" "
											href="javascript:void(0);"
											title="<?php echo ( $row->state ) ? JText::_('QTC_UNPUBLISH') : JText::_('QTC_PUBLISH'); ;?>"
											onclick="document.adminForm.cb<?php echo $i;?>.checked=1; document.adminForm.boxchecked.value=1; Joomla.submitbutton('<?php echo ($row->state) ? 'category.unpublish' : 'category.publish';?>');">
												<img class="q2c_button_publish" src="<?php echo JUri::root(true);?>/components/com_quick2cart/assets/images/<?php echo ($row->state) ? 'publish.png' : 'unpublish.png';?>"/>
										</a>
									</td>

									<td class="q2c_width_5 nowrap center">
										<?php echo $edit_link; ?>
									</td>

									<td class="q2c_width_15 hidden-phone small">
										<?php
										if (!empty($store_details[$row->store_id]))
										{
											echo $store_details[$row->store_id]['title'];
										}
										?>
									</td>

									<td class="q2c_width_15 hidden-phone small">
										<?php
											$catname = $comquick2cartHelper->getCatName($row->category);
											echo !empty($catname) ? JText::_(trim($catname)) : $row->category;
										 ?>
									</td>

									<td class="q2c_width_10 nowrap center hidden-phone small">
										<?php
										if ($row->cdate !='0000-00-00 00:00:00')
										{
											$cdate=date("Y-m-d",strtotime($row->cdate));
											echo $cdate;
										}
										else
										{
											echo "-";
										}
										?>
									</td>

									<td class="q2c_width_1 nowrap center hidden-phone small">
										<?php echo $row->item_id; ?>
									</td>
								</tr>

								<?php
								if ($k%2!=1)
								{
									$k++;
								}
								else
								{
									$k = 0;
								}
							}
						}
						// End if products.
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
			<input type="hidden" name="view" value="category" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
