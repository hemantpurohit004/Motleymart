<?php
/**
 * @version    SVN: <svn_id>
 * @package    JGive
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

if (!class_exists('Quick2cartModelProduct'))
{
	JLoader::register('Quick2cartModelProduct', JPATH_ADMINISTRATOR . '/components/com_quick2cart/models/product.php');
	JLoader::load('Quick2cartModelProduct');
}

$quick2cartModelProduct = new Quick2cartModelProduct;
$this->form_extra = $quick2cartModelProduct->getFormExtra(
					array("category" => '',
					"clientComponent" => 'com_quick2cart',
					"client" => 'com_quick2cart.product',
					"view" => 'product',
					"layout" => 'new'));

$jinput = JFactory::getApplication()->input;

$data = $jinput->get;

//~ $data->get('item_id', '', 'INT');
//~ $data->get('item_id');

$item_id = $data->get('item_id');
?>

<script>
	function cancel()
	{
		window.parent.location.reload();
		window.parent.SqueezeBox.close();
	}
</script>

<?php
if ($this->form_extra)
{?>
	<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >
		<form method="POST" name="adminForm" class="form-validate" action="index.php">
			<legend><?php echo JText::_('Add Other Details')?></legend>
				<div class="row-fluid">
					<div class="span12">
						<div class="com_qtc_media_repeat_block well">
							<?php
							foreach ($this->form_extra as $form_extra)
								foreach ($form_extra->getFieldsets() as $fieldsets => $fieldset)
								{?>
									<dl>
										<div>
											<h3><?php print_r(ucfirst($fieldset->name));?></h3>
										</div>
										<?php foreach($form_extra->getFieldset($fieldset->name) as $field)
										{?>
											<!-- If the field is hidden, only use the input. -->
										<?php
											if ($field->hidden)
											{
												echo $field->input;
											}
											else
											{?>
												<div class="control-group">
													<div class="control-label">
														<?php echo $field->label; ?>
													</div>
													<div class="controls">
														<?php echo $field->input; ?>
													</div>
												</div>
											<?php
											}
										}?>
									</dl>
								<?php
								}?>
						</div>
					</div>
				</div>

				<div class="form-actions">
					<input type="hidden" name="item_id" value="<?php echo !empty($item_id) ? $item_id : ''; ?>" />
					<input type="hidden" name="option" value="com_quick2cart">
					<input type="hidden" name="task" value="attributes.addField" />
					<input type="hidden" name="controller" value="attributes" />
					<input class="btn btn-success validate" type="submit" value="<?php echo JText::_('QTC_ADDATTRI_SAVE')?>" />
					<a class="btn btn-default" onclick="cancel();" title="<?php echo JText::_('JCANCEL'); ?>">
						<?php echo JText::_('QTC_ADDATTRI_CANCEL'); ?>
					</a>
			</div>
					<?php echo JHtml::_('form.token'); ?>
				</div>
		</form>
	</div>
<?php
} ?>


