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
$id = JFactory::getApplication()->input->get('id', "0", 'INT');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?> my-products container-fluid">
	<form  method="post" name="adminForm" id="adminForm" class="form-validate">
		<div id="qtc-filter-bar" class="qtc-btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search"
				placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_PRODUCTS'); ?>"
				value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				class="qtc-hasTooltip input-medium"
				title="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_PRODUCTS'); ?>" />
			</div>
			<div class="pull-left">
				<button type="submit" class="btn btn-default qtc-hasTooltip"
				title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
					<i class="<?php echo QTC_ICON_SEARCH; ?>"></i>
				</button>
				<button type="button" class="btn  btn-default qtc-hasTooltip"
				title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
				onclick="document.id('filter_search').value='';this.form.submit();">
					<i class="<?php echo QTC_ICON_REMOVE; ?>"></i>
				</button>
			</div>

			<?php if (JVERSION >= '3.0') : ?>
				<div class=" pull-right hidden-xs ">
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>


			<div class="pull-right hidden-xs">
				<select name="filter_category" class="input-medium" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
					<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_quick2cart'), 'value', 'text', $this->state->get('filter.category'));?>
				</select>
			</div>
		</div>

		<div class="clearfix"> &nbsp;</div><br/>

		<div class=" qtc_productblog">
			<?php
			if (empty($this->items)) : ?>
				<div class="alert alert-warning">
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
							<th class="q2c_width_15 hidden-xs">
								<?php echo JHtml::_('grid.sort', 'COM_QUICK2CART_CAT', 'category', $listDirn, $listOrder); ?>
							</th>
							<th class="q2c_width_1 nowrap center hidden-xs">
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
							foreach ($this->products as $row)
							{
								?>
								<tr class="<?php echo 'row'.$k." "; ?> qtc-products" id="qtc-selected-product<?php echo $row->item_id;?>">
									<td class="q2c_width_1 nowrap center">
										<?php echo JHtml::_('grid.id', $row->item_id, $row->item_id); ?>
									</td>
									<td>
										<?php echo $row->name; ?>
									</td>
									<td class="q2c_width_1 nowrap center">
										<?php echo ($row->state == '1')?JText::_("QTC_PROD_PUBLISH"):JText::_("QTC_PROD_UNPUBLISH"); ?>
									</td>
									<td class="q2c_width_15 hidden-xs">
										<?php
											$catname = $comquick2cartHelper->getCatName($row->category);
											echo !empty($catname) ? $catname : $row->category;
										 ?>
									</td>
									<td class="q2c_width_1 nowrap center hidden-xs">
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
						?>
					</tbody>
				</table>
			<?php endif; ?>
			<div class="center">
				<a class="btn btn-large btn-success" onclick="submitprod('<?php echo $id;?>')"> Apply </a>
			</div>
			<input type="hidden" name="view" value="category" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
<script>
techjoomla.jQuery(document).ready(function ()
{
	var id = <?php echo $id;?>;

	var selectedProds = window.parent.document.getElementById('rule_conditions_'+id+'_condition_attribute_value').value;

	var selectedProdsArray = selectedProds.split(",");

	for (i = 0; i < selectedProdsArray.length; i++)
	{
		techjoomla.jQuery("#qtc-selected-product"+selectedProdsArray[i]+" input").prop('checked', true);
	}
});

function submitprod(id)
{
	var flag = 0;

	var selectedProducts ='';

	techjoomla.jQuery('.qtc-products :checked').each(function() {

		if (Number(flag) != 0)
		{
			selectedProducts += ",";
		}

		selectedProducts += techjoomla.jQuery(this).val();

		flag++;
	});

	window.parent.document.getElementById('rule_conditions_'+id+'_condition_attribute_value').value = selectedProducts;

	window.parent.SqueezeBox.close();
}
</script>
