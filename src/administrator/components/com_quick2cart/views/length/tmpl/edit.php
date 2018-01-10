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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.modal');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'length.cancel')
		{
			Joomla.submitform(task, document.getElementById('length-form'));
		}
		else
		{
			if (task != 'length.cancel' && document.formvalidator.isValid(document.id('length-form')))
			{
				Joomla.submitform(task, document.getElementById('length-form'));
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<div class="<?php echo Q2C_WRAPPER_CLASS;?> qtc_admin_length">
	<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>"
		method="post" enctype="multipart/form-data" name="adminForm" id="length-form" class="form-validate">

		<div class="form-horizontal">
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset class="adminform">
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('unit'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('unit'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('value'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('value'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
						</div>
						<!--
						div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('store_id'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('store_id'); ?></div>
						</div>
						-->

						<?php
						if(empty($this->item->created_by))
						{
							?>
							<input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>" />
							<?php
						}
						else
						{
							?>
							<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" />
							<?php
						}
						?>

						<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
						<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
					</fieldset>
				</div>
			</div>

			<input type="hidden" name="task" value="" />

			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
