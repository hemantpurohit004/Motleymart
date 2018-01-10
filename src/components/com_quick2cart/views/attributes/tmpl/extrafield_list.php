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

jimport('joomla.html.pane');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal', 'a.modal');
/*list of attributes of item*/
$params = JComponentHelper::getParams('com_quick2cart');
$qtc_base_url = JUri::base();
$lang = JFactory::getLanguage();
$lang->load('com_quick2cart', JPATH_SITE);

// declaration section
$quick2cartModelAttributes =  new quick2cartModelAttributes();
$path = JPATH_SITE.'/components/com_quick2cart/helpers/product.php';

$addMediaLink = $qtc_base_url.'index.php?option=com_quick2cart&view=attributes&layout=extrafield&tmpl=component&item_id='.$item_id;

$fparam = "'" . (!empty($item_id) ? $item_id :0 ) . "'";

	if (!class_exists('Quick2cartModelProductpage'))
	{
		JLoader::register('Quick2cartModelProductpage', JPATH_SITE . '/components/com_quick2cart/models/productpage.php');
		JLoader::load('Quick2cartModelProductpage');
	}

		$quick2cartModelProductpage = new Quick2cartModelProductpage;
		$extraData = $quick2cartModelProductpage->getDataExtra($fparam);
?>
<script type="text/javascript">
function AddExtraFields(id)
{
}
</script>
<?php
defined('_JEXEC') or die;
if(isset($extraData) && count($extraData))
{?>
	<table class="table table-striped table-bordered table-hover">
		<?php foreach($extraData as $f):?>
			<tr>
				<td>
					<strong><?php echo $f->label;?></strong>
				</td>
				<td>
					<?php if (!is_array($f->value)): ?>
						<?php echo $f->value; ?>
					<?php else: ?>
						<?php foreach($f->value as $option): ?>
								<?php echo $option->options; ?>
							<br/>
						<?php endforeach; ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php
}?>

<a rel="{handler: 'iframe', size: {x: window.innerWidth-350, y: window.innerHeight-150}, onClose: function(){AddExtraFields(<?php echo $fparam; ?>);}}" class="btn btn-primary btn-small <?php echo ($button_dis == ""?'modal':$button_dis)?> " href="<?php echo ($button_dis == ""?$addMediaLink:"#")?>">
		<?php echo JText::_('Add Extra Fields'); ?>
</a>

