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
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_quick2cart/assets/css/quick2cart.css');
?>
<script type="text/javascript">
	techjoomla.jquery = jQuery.noConflict();
	techjoomla.jquery(document).ready(function() {

	var wrapper         = techjoomla.jquery(".input_fields_wrap"); //Fields wrapper
	var add_button      = techjoomla.jquery(".add_field_button"); //Add button ID

	// if there are options on edit form then show option heading
	if (<?php echo !empty($this->optionList)?1:0;?>)
	{
		if (techjoomla.jquery(".q2cattributeheading").length)
		{
			techjoomla.jquery(wrapper).append('<div id="qtcoptionheading"><span class="qtc-attributeset-name"><b>Option Name</b></span><span><b>Ordering</b></span></div>');
		}
	}

	var optionListCount = <?php echo !empty($this->optionList)? count($this->optionList) : 0;?>; //initlal text box count
	techjoomla.jquery(add_button).click(function(e)
	{ //on add input button click
		e.preventDefault();

		if (!techjoomla.jquery(".q2cattributeheading").length)
		{
			techjoomla.jquery(wrapper).append('<div id="qtcoptionheading"><span class="qtc-attributeset-name"><b>Option Name</b></span><span><b>Ordering</b></span></div>');
		}

		//on add options click add an clone of option fields
		techjoomla.jquery(".qtc-attribute-options-list").append('<div class="q2cattributeheading"><div class="control-group" id="qtcoptionclone"><label class="control-label hidden" for="optionsname'+optionListCount+'">Option Name</label><label class="control-label hidden" for="optionsordering'+optionListCount+'">Ordering</label><div class="controls"><span class="qtc-global-attribute-option-input"><input type="text" class="input-small required" id="optionsname'+optionListCount+'"name="options['+optionListCount+'][option_name]" placeholder="Option Name" value=""></span><span class="qtc-global-attribute-option-input"><input type="text" class="input-small required validate validate-numeric" id="optionsordering'+optionListCount+'" name="options['+optionListCount+'][ordering]" placeholder="Ordering" value=""></span><span><a class="btn btn-small btn-danger remove_field">Remove</a></span><span class=""><input type="hidden" class="input-small" name="options['+optionListCount+'][id]" placeholder="option id" value=""></span></div></div></div>');
		optionListCount++;
	});

	//remove option input on click of remove button
	techjoomla.jquery('.qtc-attribute-options-list').on("click",".remove_field", function(e){
		e.preventDefault();
		techjoomla.jquery(this).parent().parent().parent().parent().remove();
		//if there is no option input then remove option heading
		if (!techjoomla.jquery(".q2cattributeheading").length)
		{
			techjoomla.jquery('#qtcoptionheading').remove();
		}
	});
});

	Joomla.submitbutton = function(task)
	{
		if (task == 'globalattribute.cancel')
		{
			Joomla.submitform(task, document.getElementById('attribute-form'));
		}
		else
		{
			if (task != 'globalattribute.cancel' && document.formvalidator.isValid(document.id('attribute-form')))
			{

				Joomla.submitform(task, document.getElementById('attribute-form'));
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="attribute-form" class="form-validate">
	<div class="form-horizontal">
		<div class="row-fluid">
			<?php
			if (empty($this->item->id))
			{
			?>
			<div class="alert alert-info"><?php echo JText::_('COM_QUICK2CART_GLOBAL_ATTRIBUTES_MSG');?></div>
			<?php
			}
			else
			{
			?>
			<div class="alert alert-info"><?php echo JText::_('COM_QUICK2CART_GLOBAL_ATTRIBUTES_INFO');?></div>
			<?php
			}
			?>

			<div class="span12 form-horizontal">
				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('attribute_name'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('attribute_name'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('display_name'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('display_name'); ?></div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_SELECT_RENDERER_DESC'), JText::_('COM_QUICK2CART_SELECT_RENDERER'), '', JText::_('COM_QUICK2CART_SELECT_RENDERER'));?></label>
					<div class="controls">
						<?php
						echo JHtml::_('select.genericlist', $this->layoutsList, "renderer", 'class="inputbox input-large" size="1" name="renderer"', "value", "text", $this->item->renderer);
						?>
					</div>
				</div>
				<div class="controls input_fields_wrap"></div>
				<br>
				<div class="qtc-attribute-options-list">
				<?php
					if ($this->item->id)
					{
						foreach ($this->optionList as $key => $optiondetail)
						{
					?>
							<div class="q2cattributeheading q2cattributeoption<?php echo $optiondetail->id;?>">
								<div class="control-group">
									<label class="control-label hidden" for="optionsname<?php echo $key;?>"><?php echo JText::_('QTC_ADDATTRI_OPTNAME')?></label>
									<label class="control-label hidden" for="optionsordering<?php echo $key;?>"><?php echo JText::_('COM_QUICK2CART_ATTRIBUTE_OPTION')?></label>
									<div class="controls">
										<span>
											<input type="text" class="input-small required" id="optionsname<?php echo $key;?>" name="options[<?php echo $key ?>][option_name]" placeholder="Option name" value="<?php echo $optiondetail->option_name;?>">
										</span>
										<span>
											<input type="text" title="<?php echo JText::_('COM_QUICK2CART_OPTION_ORDERING_INFO');?>" class="input-small required" id="optionsordering<?php echo $key;?>" name="options[<?php echo $key ?>][ordering]" placeholder="Ordering" value="<?php echo $optiondetail->ordering;?>">
										</span>
										<span>
										<div id="q2coptremovebutton<?php echo $optiondetail->id;?>" class="btn btn-small btn-danger delete_field" onclick="deleteOption(<?php echo $optiondetail->id;?>,this.id)">Remove</div>
										</span>
										<span>
											<input type="hidden" class="input-small" name="options[<?php echo $key ?>][id]" placeholder="option id" value="<?php echo $optiondetail->id;?>">
										</span>
									</div>
								</div>
							</div>
					<?php
						}
					}
					?>
				</div>
				<?php
				if (!empty($this->item->id))
				{
				?>
					<div class="control-group">
						<div class="controls">
							<a class="btn btn-small btn-success add_field_button"><?php echo JText::_('COM_QUICK2CART_ADD_OPTIONS');?></a><br><br>
						</div>
					</div>
				<?php
				}
				?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="globalattribute" />
		<input type="hidden" name="id" value="<?php echo $this->item->id?$this->item->id:""?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
