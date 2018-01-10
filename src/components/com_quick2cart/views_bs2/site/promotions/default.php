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
<div class="<?php echo Q2C_WRAPPER_CLASS; ?> my_promotions">
	<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&view=promotions'); ?>" method="post" name="adminForm" id="adminForm">
		<?php
		$active = 'promotions';
		ob_start();
		include($this->toolbar_view_path);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		?>
		<div class="row-fluid">
			<div id="j-main-container" class="span12">
				<legend><?php echo JText::_("COM_QUICK2CART_PROMOTIONS");?></legend>
					<div class="clearfix">&nbsp;</div>
					<div><?php echo $this->toolbarHTML; ?></div>
					<div class="clearfix">&nbsp;</div>
				<hr>
				<?php
					$comquick2cartHelper = new Comquick2cartHelper();
					$view = $comquick2cartHelper->getViewpath('promotions', 'list');
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
</div>
