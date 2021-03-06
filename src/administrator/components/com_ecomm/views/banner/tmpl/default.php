<?php

// No direct access
defined('_JEXEC') or die;
JHtml::_('formbehavior.chosen','select');
JHtml::_('behavior.formvalidator');
?>

<form action="<?php echo JRoute::_('index.php?option=com_ecomm&layout=default&id='. (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span12">

				  <ul class="nav nav-tabs">
					<li  class="active">
						<a href="#basic" aria-controls="basic" data-toggle="tab">
							<?php echo JText::_('Basic Details') ?>
						</a>
					</li>
				  </ul>

				<div class="tab-content">
					<div class="tab-pane active" id="basic">
						<fieldset>
						<?php foreach ($this->form->getFieldset('basic') as $field): ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label; ?></div>
							<div class="controls"><?php echo $field->input ; ?></div>
						</div>
					<?php endforeach; ?>
					<?php if($this->showPreview) { ?>
						<div class="control-group">
							<div class="control-label">Preview Banner </div>
							<div class="controls">
								<a href="<?php echo ($this->item->img_path); ?>" target="_blank">Click here to see the image
							</div>
						</div>
					<?php }?>

					</fieldset>
					</div>
				</div>

				<input type="hidden" name="jform[state]" id="jform_state" value="1"/>
			</div>
		</div>
	</div>
		<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>
