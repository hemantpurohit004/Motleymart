<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

$lang = JFactory::getLanguage();
$lang->load('com_quick2cart', JPATH_SITE);
//$params = JComponentHelper::getParams('com_quick2cart');
$data = $displayData;
?>
<div class="">
	<span class="qtcAddGlobalOption qtc_float_right">

		<button type="button" class="qtcHandPointer btn btn-primary" onclick="qtcLoadAttributeOption(this)" title="<?php echo JText::_('COM_QUICK2CART_ADD_GOLB_ATTROPTIONS_DESC'); ?>">
			<?php echo JText::_('COM_QUICK2CART_ADD_GOLB_ATTROPTIONS') ?>
		</button>
	</span>
	<select class="globalOptionSelect qtc_float_right">
		<option value="" ><?php echo JText::_('COM_QUICK2CART_ADDPROD_LOADALL_GOLB_ATTROPTIONS') ?></option>
		<?php
		if (!empty($data))
		{
			foreach ($data as $op_key => $option)
			{
				?>
				<option value="<?php echo $option->id ?>" > <?php echo $option->option_name ?></option>
				<?php
			}
		}
		?>
	</select>
	<div class="clearfix"></div>
</div>

