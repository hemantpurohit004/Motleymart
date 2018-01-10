<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.filesystem.file');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.ordering');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_quick2cart&task=products.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'productsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}

$input = JFactory::getApplication()->input;
$cid   = $input->get( 'cid', '', 'ARRAY');

// Used to store store_name against store_id.
$store_names         = array();
$comquick2cartHelper = new comquick2cartHelper;

// Store details
$store_details = $this->store_details;
?>

<script type="text/javascript">
	Joomla.submitbutton = function(action)
	{
		if (action=='products.publish' || action=='products.unpublish')
		{
			Joomla.submitform(action);
		}
		else if (action=='products.delete')
		{
			var r=confirm("<?php echo JText::_('QTC_DELETE_CONFIRM_PROD');?>");
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
			window.location = 'index.php?option=com_quick2cart&view=products';
		}

		var form = document.adminForm;
		Joomla.submitform( action );

		return;
	}
</script>


<form  method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="<?php echo Q2C_WRAPPER_CLASS;?> q2c-products">
		<?php
		// JHtmlsidebar for menu.
		if (JVERSION >= '3.0'):
			if (!empty( $this->sidebar)) : ?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar; ?>
				</div>
				<div id="j-main-container" class="span10">
					<?php
						// Search tools bar
						echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
					?>
			<?php else : ?>
				<div id="j-main-container">
					<?php
						// Search tools bar
						echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
					?>
			<?php endif;
		endif;
		?>

		<?php if (JVERSION < '3.0'): ?>
			<fieldset id="filter-bar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_PRODUCTS'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_PRODUCTS'); ?>" />
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


				<div class="filter-select fltrt hidden-phone pull right">
					<?php
					echo JHtml::_('select.genericlist', $this->clients, "filter_client", 'class="input-medium"  onchange="document.adminForm.submit();" name="filter_client"', "value", "text", $this->state->get('filter.client'));

					if (!empty($this->store_details))
					{
						$options[] = JHtml::_('select.option', 0, JText::_( 'QTC_SELET_STORE' ));

						if (count($this->store_details)>1)
						{
							$filter_store = $this->state->get('filter.store');
							$default = !empty($filter_store) ? $filter_store : 0;

							foreach($this->store_details as $key=>$value)
							{
								$options[] = JHtml::_('select.option', $key,$value['title']);
							}

							echo $this->dropdown = JHtml::_('select.genericlist', $options, 'filter_store', 'class="input-medium"  onchange="document.adminForm.submit();" ', 'value', 'text', $default);
						}
					}

					echo JHtml::_('select.genericlist', $this->sstatus, "filter_published", 'class="input-medium"  onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.published'));
					?>

					<select name="filter_category" class="inputbox input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
						<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_quick2cart'), 'value', 'text', $this->state->get('filter.category'));?>
					</select>
				</div>
			</fieldset>

			<div class="clr"> </div>
		<?php endif;

		if (empty($this->items)) : ?>
			<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_QUICK2CART_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php
		else : ?>
			<table class="table table-striped" id="productsList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php
							echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING');
							?>
						</th>

						<th class="nowrap q2c_width_1 center">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>

						<th class="nowrap q2c_width_5 center">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_PUB', 'state', $listDirn, $listOrder); ?>
						</th>

						<th>
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_NAM', 'name', $listDirn, $listOrder);?>
						</th>

						<th class="nowrap q2c_width_5 center hidden-phone hidden-tablet">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_FEATURED', 'featured', $listDirn, $listOrder); ?>
						</th>

						<th class="nowrap q2c_width_15 hidden-phone hidden-tablet">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_CLIENT', 'parent', $listDirn, $listOrder); ?>
						</th>

						<th class="nowrap q2c_width_15 hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_CAT', 'category', $listDirn, $listOrder); ?>
						</th>

						<th class="nowrap q2c_width_15 hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_STORE_NAME', 'store_id', $listDirn, $listOrder); ?>
						</th>

						<th class="nowrap q2c_width_10 hidden-phone hidden-tablet">
							<?php echo JText::_('COM_QUICK2CART_CREATD_BY'); ?>
						</th>

						<th class="nowrap q2c_width_5 center hidden-phone hidden-tablet">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_CDATE', 'cdate', $listDirn, $listOrder); ?>
						</th>

						<th class="nowrap q2c_width_5 center hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_ID', 'item_id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>

				<tbody>
					<?php
					$k = 0;

					if (!empty($this->products))
					{
						$n = count($this->products);

						for ($i=0; $i < $n; $i++)
						{
							$zone_type = '';
							$row = $this->products[$i];

							$ordering = ($listOrder == 'a.ordering');

							$published = JHtml::_('jgrid.published', $row->state, $i, 'products.');
							$edit_link   = '<a href="'. $row->edit_link . '" >' . $row->name . '</a>';
							?>

							<tr class="<?php echo 'row'.$k;?>">
								<td class="order nowrap center hidden-phone">
									<?php
										$iconClass = '';
										if (!$saveOrder)
										{
											$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
										}
										?>
										 <span class="sortable-handler <?php echo $iconClass ?>">
											 <span class="icon-menu"></span>
										 </span>
										 <?php

										 if ($saveOrder) : ?>
										 <input type="text" style="display:none" name="order[]" size="8" value="<?php echo $row->ordering; ?>" class="width-20 text-area-order " />
										 <?php endif; ?>

								</td>

								<td class="nowrap q2c_width_1 center">
									<?php echo JHtml::_('grid.id', $i, $row->item_id); ?>
								</td>

								<td class="nowrap q2c_width_5 center">
									<?php echo $published;?>
								</td>

								<td>
									<?php echo $edit_link;?>
								</td>

								<td class="nowrap q2c_width_5 center hidden-phone hidden-tablet">
									<a href="#"
									 class='btn btn-micro active hasTooltip'
									 onclick="listItemTask('cb<?php echo $i;?>', '<?php echo ($comquick2cartHelper->isFeatured($row->item_id)) ? 'products.unfeatured' : 'products.featured';?>')"
									 title="<?php echo ($comquick2cartHelper->isFeatured($row->item_id)) ? JText::_('COM_QUICK2CART_UNFEATURE_TOOLBAR') : JText::_('COM_QUICK2CART_FEATURE_TOOLBAR');?>">
										<?php $fclass = ($comquick2cartHelper->isFeatured($row->item_id)) ? 'icon-star icon-featured' : 'icon-star-empty';?>
										<i class="<?php echo $fclass;?>"></i>
									</a>
								</td>

								<td class="nowrap q2c_width_15 hidden-phone hidden-tablet">
									<?php echo $row->parent; ?>
								</td>

								<td class="nowrap q2c_width_15 hidden-phone">
									<?php echo $row->category; ?>
								</td>

								<td class="nowrap q2c_width_15 hidden-phone">
									<?php echo $row->store_name; ?>
								</td>

								<td class="nowrap q2c_width_10 hidden-phone hidden-tablet">
									<?php echo $row->store_owner; ?>
								</td>

								<td class="nowrap q2c_width_5 center hidden-phone hidden-tablet">
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

								<td class="nowrap q2c_width_5 center hidden-phone">
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
		<input type="hidden" name="view" value="products" />
		<input type="hidden" name="task" value="" />

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

		<?php echo JHtml::_( 'form.token' ); ?>
	</div>
</form>
