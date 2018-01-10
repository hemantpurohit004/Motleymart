<?php
/**
 * @version     1.0.0
 * @package     com_quick2cart
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <extensions@techjoomla.com> - http://techjoomla.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$qtc_cat_options = JHtml::_('category.options', 'com_quick2cart', array('filter.published' => array(1)));

$document     = JFactory::getDocument();

$document->addStyleSheet(JUri::root(true) . '/components/com_quick2cart/assets/css/q2c-tables.css');
?>
<script type="text/javascript">
	techjoomla.jquery = jQuery.noConflict();

	Joomla.submitbutton = function(task)
	{
		if (task == 'attributesetmapping.cancel') {
			Joomla.submitform(task, document.getElementById('attributemapping'));
		}
		else
		{
			if (task != 'attributesetmapping.cancel' && document.formvalidator.isValid(document.id('attributemapping')))
			{
				Joomla.submitform(task, document.getElementById('attributemapping'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit');?>" method="post" enctype="multipart/form-data" name="adminForm" id="attributemapping" class="form-validate">
	<div class="alert alert-info"><?php echo JText::_('COM_QUICK2CART_ATTRIBUTE_SET_MAPPING_INFO');?></div>
	<div id="no-more-tables">
		<table class="table table-responsive table-condensed table-bordered table-hover">
			<thead>
				<tr>
					<th width="2%"><?php echo JText::_('COM_QUICK2CART_CAT');?></th>
					<th width="2%"><?php echo JText::_('COM_QUICK2CART_GLOBAL_ATTRIBUTE_SET');?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($qtc_cat_options as $category):?>
				<?php
					$attribute_id = $this->model->getAttributeSet($category->value);
					$count = $this->model->checkForProductsInCategory($category->value);

					if ($count > 0)
					{
						$disabled = 'class="inputbox input-medium" disabled="disabled"';
					}
					else
					{
						$disabled = 'class="inputbox input-medium"';
					}
				?>
				<tr>
					<td width="2%"><input type="text" class="disabled" disabled="disabled" name="cats[catid][<?php echo $category->value?>]" value ="<?php echo $category->text?>"/></td>
					<td width="2%"><div><?php echo JHtml::_('select.genericlist', $this->attributeSetsList, "cat[".$category->value."][]", $disabled, "id", "global_attribute_set_name", $attribute_id);
					?>
					</div><div>
					<?php
					if ($count > 0)
					{
						echo sprintf(JText::_('COM_QUICK2CART_MAPPING_DISABLED'),$category->text);
					}
					?></div></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="attributesetmapping" />
</form>
