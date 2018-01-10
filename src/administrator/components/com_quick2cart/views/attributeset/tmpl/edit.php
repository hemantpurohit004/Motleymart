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
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_quick2cart/assets/css/quick2cart.css');

?>
<script type="text/javascript">
	techjoomla.jquery = jQuery.noConflict();

	Joomla.submitbutton = function(task)
	{
		if (task == 'attributeset.cancel') {
			Joomla.submitform(task, document.getElementById('attributeset-form'));
		}
		else {

			if (task != 'attributeset.cancel' && document.formvalidator.isValid(document.id('attributeset-form'))) {

				Joomla.submitform(task, document.getElementById('attributeset-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}

	function add_attribute_to_list()
	{
		var wrapper = techjoomla.jquery(".selectedattributes"); //Fields wrapper
		var e = document.getElementById("attributelist");
		var selectedOptiontext = e.options[e.selectedIndex].text;
		var selectedOptionvalue = e.options[e.selectedIndex].value;
		var attributesetid = <?php echo $this->item->id?$this->item->id:0;?>
		// note - take it from form itself
		var optionListCount = techjoomla.jquery('.qtcattributeclone').length;
		var optionListCountNew = optionListCount+1;
		if(!techjoomla.jquery("#qtcattribute"+selectedOptionvalue).length)
		{
			if (selectedOptionvalue != 0)
			{
				var url = "?option=com_quick2cart&task=attributeset.addattribute&attributeid="+selectedOptionvalue+"&attributesetid="+attributesetid;
				techjoomla.jQuery.ajax({
					type: "get",
					url:url,
					async:false,
					success: function(response)
					{
						if (!techjoomla.jquery("#qtcoptionheading").length)
						{
							techjoomla.jquery(wrapper).append('<div class="control-group" id="qtcoptionheading"><div class="controls"><span class="qtc-attributeset-name"><b><?php echo JText::_('COM_QUICK2CART_GLOBALATTRIBUTES_ATTRIBUTE_NAME');?></b></span><span><b><?php echo JText::_('COM_QUICK2CART_FORM_LBL_ATTRIBUTE_ORDERING');?></b></span></div></div>');
						}
						//on add options click add an clone of option fields
						techjoomla.jquery(wrapper).append('<div class="control-group"><div id="qtcoptionclone" class="controls qtcattributeclone"><span><input type="text" class="input-large disabled center" disabled="disabled" id="qtcattribute'+selectedOptionvalue+'" name="attributes['+optionListCount+'][attribute_name]" placeholder="attribute Name" value="'+selectedOptiontext+'"></span>	<span class="center"><input type="text" class="input-small" name="attributes['+optionListCount+'][attribute_option]" placeholder="Order" value="'+optionListCountNew+'"></span>	<a class="btn btn-small btn-danger" onclick="removeclone('+selectedOptionvalue+')">Remove</a><span><input type="hidden" class="input-small" name="attributes['+optionListCount+'][id]" placeholder="attribute id" value="'+selectedOptionvalue+'"></span></div></div>');
						optionListCount++;
					},
					error: function(response)
					{
						alert("error");
						console.log(' ERROR!!' );
						return e.preventDefault();
					}
				});
			}
			else
			{
				alert("Please select attribute");
			}
		}
		else
		{
			alert("Attribute already selected");
		}
	}

	function removeclone(clone_id)
	{
		var confirmdelete = confirm("<?php echo JText::_('COM_QUICK2CART_REMOVE_ATTRIBUTE_MSG');?>");

		if( confirmdelete == false )
		{
			return false;
		}

		var attributesetid = <?php echo $this->item->id?$this->item->id:0;?>;
		var url = "?option=com_quick2cart&task=attributeset.removeattribute&attributeid="+clone_id+"&attributesetid="+attributesetid;
		techjoomla.jQuery.ajax({
			type: "get",
			url:url,
			async:false,
			success: function(response)
			{
				var message = JSON.parse(response);

				if(message[0].error)
				{
					alert(message[0].error);
				}
				else
				{
					techjoomla.jquery('#qtcattribute'+clone_id).parent().parent().parent().remove();

					if (!techjoomla.jquery("#qtcoptionclone").length)
					{
						techjoomla.jquery('#qtcoptionheading').remove();
					}
				}
			},
			error: function(response)
			{
				alert("error");
				console.log(' ERROR!!' );
				return e.preventDefault();
			}
		});
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="attributeset-form" class="form-validate">
	<div class="form-horizontal">
		<div class="row-fluid">
			<?php if($this->item->id != null):?>
			<div class="alert alert-info"><?php echo JText::_("COM_QUICK2CART_GLOBAL_ATTRIBUTE_SET_INFO");?></div>
			<?php endif;?>
			<div class="span12 form-horizontal">
					<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
					<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('global_attribute_set_name'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('global_attribute_set_name'); ?></div>
					</div>
					<?php if (!isset($this->item->id)):?>
						<div class="alert alert-info"><?php echo JText::_('COM_QUICK2CART_ATTRIBUTESET_TOOLTIP');?></div>
					<?php else:?>
					<div class="control-group">
						<label class="control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_SELECT_ATTRIBUTE_DESC'), JText::_('COM_QUICK2CART_SELECT_ATTRIBUTE'), '', JText::_('COM_QUICK2CART_SELECT_ATTRIBUTE'));?></label>
					<div class="controls">
					<?php
							echo JHtml::_('select.genericlist', $this->attributeList, "attributelist", 'class="ad-status inputbox input-large" size="1" name="attributeList"', "value", "text", $this->state->get('filter.campaignslist'));
					?>
					<div class="btn btn btn-success" onclick="add_attribute_to_list()">add</div>
					<?php endif;?>
					</div><br><br>
				<?php
				if (!empty($this->attributeLists)):?>
						<div class="control-group" id="qtcoptionheading">
							<div class="controls">
								<span class="qtc-attributeset-name">
									<b><?php echo JText::_('COM_QUICK2CART_GLOBALATTRIBUTES_ATTRIBUTE_NAME');?></b>
								</span>
								<span>
									<b><?php echo JText::_('COM_QUICK2CART_FORM_LBL_ATTRIBUTE_ORDERING');?></b>
								</span>
							</div>
						</div>
					<?php foreach($this->attributeLists as $key => $attributeDetail):?>
					<div class="control-group">
						<div id="qtcoptionclone" class="controls qtcattributeclone">
							<span>
								<input type="text" class="input-large disabled center" disabled="disabled" id="qtcattribute<?php echo $attributeDetail['id']?>" name="attributes[<?php echo $key;?>][attribute_name]" placeholder="attribute Name" value="<?php echo $attributeDetail['attribute_name']?>">
							</span>
							<span class="center">
								<input type="text" class="input-small" name="attributes[<?php echo $key;?>][attribute_option]" placeholder="Order" value="<?php echo $key+1;?>">
							</span>
							<a class="btn btn-small btn-danger" onclick="removeclone(<?php echo $attributeDetail['id']?>)">Remove</a>
							<span class="center">
								<input type="hidden" class="input-small" name="attributes[<?php echo $key;?>][id]" placeholder="attribute id" value="<?php echo $attributeDetail['id']?>">
							</span>
						</div>
					</div>
					<?php endforeach;?>
				<?php endif;?>
				<div class="row-fluid">
						<div class="selectedattributes">
						</div>
					</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="attributeset" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
