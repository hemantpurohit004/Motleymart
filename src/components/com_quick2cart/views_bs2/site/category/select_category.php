<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();
$this->items = JHtml::_('category.options', 'com_quick2cart', array('filter.published' => array(1)));
$id = JFactory::getApplication()->input->get('id', "0", 'INT');
?>
<form action="" method="post" enctype="multipart/form-data" name="catForm" id="catForm-form">
	<div class="form-inline">
		<div class="row-fluid q2c-wrapper">
			<div class="alert alert-info"><div class="center"><h3><?php echo JText::_("COM_QUICK2CART_PROMOTION_CONDITION_SELECT_CATEGORYS");?></h3></div></div>
				<div class="form-group qtc-categorys">
				<?php
				if (empty($this->items))
				{
				?>
					<div class="clearfix">&nbsp;</div>
						<div class="alert alert-no-items">
							<?php echo JText::_('COM_QUICK2CART_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php
				}
				else
				{
					foreach ($this->items as $item)
					{
						?>
						<div class="qtc-category">
							<input type="checkbox" id="qtc-selected-category<?php echo $item->value;?>" value="<?php echo $item->value;?>">
							<label for="qtc-selected-category<?php echo $item->value;?>"><?php echo "&nbsp;".$item->text;?></label>
						</div>
							<?php
					}
				}
				?>
				</div>
			</div>
		</div>
	</div>
	<div class="center">
		<a class="btn btn-large btn-success" onclick="submitCat('<?php echo $id;?>')"> Apply </a>
	</div>
</form>
<script>
	techjoomla.jQuery(document).ready(function ()
	{
		var id = <?php echo $id;?>;

		var selectedCats = window.parent.document.getElementById('rule_conditions_'+id+'_condition_attribute_value').value;

		var selectedCatsArray = selectedCats.split(",");

		for (i = 0; i < selectedCatsArray.length; i++)
		{
			techjoomla.jQuery("#qtc-selected-category"+selectedCatsArray[i]).prop('checked', true);
		}
	});

	function submitCat(id)
	{
		var flag = 0;

		// rule_conditions_60_condition_attribute_value
		var selectedCat ='';

		techjoomla.jQuery('.qtc-categorys :checked').each(function() {

			if (Number(flag) != 0)
			{
				selectedCat += ",";
			}

			selectedCat += techjoomla.jQuery(this).val();

			flag++;
		});

		window.parent.document.getElementById('rule_conditions_'+id+'_condition_attribute_value').value = selectedCat;

		window.parent.SqueezeBox.close();
	}
</script>
