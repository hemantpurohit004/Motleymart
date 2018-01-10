<?php
/**
 * @version     2.2
 * @package     com_quick2cart
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://techjoomla.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function() {

    });

    Joomla.submitbutton = function(task)
    {
        if (task == 'taxrate.cancel') {
            Joomla.submitform(task, document.getElementById('taxrate-form'));
        }
        else {

            if (task != 'taxrate.cancel' && document.formvalidator.isValid(document.id('taxrate-form'))) {

                Joomla.submitform(task, document.getElementById('taxrate-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="taxrate-form" class="form-validate">

    <div class="form-horizontal">
        <div class="row-fluid">
            <div class="span10 form-horizontal">
				<fieldset class="adminform">

					<input type="hidden" name="jform[taxrate_id]" value="<?php echo $this->item->taxrate_id; ?>" />
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('percentage'); ?></div>
						<div class="controls">
							<div class="input-append ">
								<?php echo $this->form->getInput('percentage'); ?>
								<span class="add-on">%</span>
							</div>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('zone_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('zone_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />

			</fieldset>
			</div>
		</div>

        <input type="hidden" name="task" value="" />

		<input type="hidden" name="id" id="id" value="<?php echo $this->item->get('id')?>" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>
</div>
