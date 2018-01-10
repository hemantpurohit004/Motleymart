<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script>
	function adduser()
	{

	}
</script>

<form name="adduserform" id="adduserform" method="post" class="form-validate form-horizontal" enctype="multipart/form-data" >
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('username'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('username'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('password1'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('password1'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('password2'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('password2'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('email'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('email'); ?></div>
	</div>
	<div class="control-group">
		<div class="controls">
			<a onclick="adduser()" class="btn btn-primary">
				<?php echo JText::_('JSUBMIT'); ?>
			</a>
			<a class="btn btn-default"
				onclick="window.parent.SqueezeBox.close();"
				title="<?php echo JText::_('JCANCEL'); ?>">
				<?php echo JText::_('JCANCEL'); ?>
			</a>
		</div>
	</div>
	<input type="hidden" name="option" value="com_quick2cart"/>
	<input type="hidden" name="task" value="customer_addressform.save"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
