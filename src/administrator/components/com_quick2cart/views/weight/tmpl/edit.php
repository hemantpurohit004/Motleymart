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
if (version_compare(JVERSION, '3.0', 'ge'))
{
	JHtml::_('formbehavior.chosen', 'select');
}
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.modal');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_quick2cart/assets/css/quick2cart.css');
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function() {

	});

	Joomla.submitbutton = function(task)
	{
		if (task == 'weight.cancel') {
			Joomla.submitform(task, document.getElementById('weight-form'));
		}
		else {

			if (task != 'weight.cancel' && document.formvalidator.isValid(document.id('weight-form'))) {

				Joomla.submitform(task, document.getElementById('weight-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>
<?php
if(JVERSION < '3.0')
{ ?>
	<div class="techjoomla-bootstrap">
	<?php
}
?>
<div class="qyc_admin_length">
<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="weight-form" class="form-validate">

	<div class="form-horizontal">
		<?php //echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php //echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QUICK2CART_TITLE_WEIGHT', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">

								<div class="control-group">

			</div>
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
			<div class="control-group">
				<div class="control-label"><?php //echo $this->form->getLabel('store_id'); ?></div>
				<div class="controls"><?php //echo $this->form->getInput('store_id'); ?></div>
			</div>
			-->

				<?php if(empty($this->item->created_by)){ ?>
					<input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>" />

				<?php }
				else{ ?>
					<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" />

				<?php } ?>				<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
				<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />


				</fieldset>
			</div>
		</div>




		<?php //echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
</div> <!--qyc_admin_length -->
<?php
if(JVERSION < '3.0')
{ ?>
	</div>
	<?php
}
 ?>
