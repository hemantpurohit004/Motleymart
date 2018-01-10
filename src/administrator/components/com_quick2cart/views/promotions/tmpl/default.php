<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'administrator/components/com_quick2cart/assets/css/quick2cart.css');
$document->addStyleSheet(JUri::root(true) . '/components/com_quick2cart/assets/css/q2c-tables.css');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>
<?php

// Side bar
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>
<script>
	Joomla.submitbutton = function(task)
	{
		if (task == 'promotions.delete')
		{
			var confirmdelete = confirm("<?php echo JText::_('COM_QUICK2CART_PROMOTIONS_DELETE_POPUP');?>");

			if( confirmdelete == false )
			{
				return false;
			}
			else
			{
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		}
		else
		{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&view=promotions'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="<?php echo Q2C_WRAPPER_CLASS; ?> my_promotions">
		<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
			<?php else : ?>
			<div id="j-main-container">
				<?php endif; ?>
			<?php
				$view = JPATH_SITE . '/components/com_quick2cart/views_bs2/site/promotions/list.php';
				ob_start();
				include($view);
				$html = ob_get_contents();
				ob_end_clean();
				echo $html;
			?>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
