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

// DECLARATION SECTION
$classes = !empty($qtc_classes) ? $classes : '';
$max_scroll_ht = !empty($qtc_mod_scroll_height) ? trim($qtc_mod_scroll_height) . 'px' : '412px';
$scroll_style = "overflow-y:auto; max-height:" . $max_scroll_ht . "; overflow-x:hidden;";

$mainframe = JFactory::getApplication();
$storeHelper = new storeHelper();
$comquick2cartHelper = new comquick2cartHelper;

// CONVERTING TO OBJECT
$options = json_decode(json_encode($options), false);

// GETTING ITEM ID
$menu_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=default');
?>

<div class="row qtc_store_list <?php echo $classes;?>" style="<?php echo $scroll_style;?>">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="tj-list-group">
			<strong class="tj-list-group-item"><?php echo JText::_('QTC_SEL_VENDOR');?></strong>
			<?php
			$selected_storeid = $mainframe->getUserStateFromRequest('store_id', 'store_id', '', 'INTEGER' );
			$selected_current_store = $mainframe->getUserStateFromRequest('current_store', 'current_store', '', 'INTEGER' );
			$selected = !empty($selected_storeid)?$selected_storeid : $selected_current_store;

			if (!empty($options))
			{
				foreach ($options as $op)
				{
					$storeLink = $storeHelper->getStoreLink($op->id);
					$activeoption = "";

					if ($selected == $op->id)
					{
						$activeoption = "active";
					}

					// Store status is 1, Added by Sneha
					if ($op->live == 1)
					{
						?>
						<a class="tj-list-group-item <?php echo $activeoption;?>" href="<?php echo $storeLink ;?>">
							<?php echo ucfirst($op->title); ?>
						</a>
						<?php
					}
				}
			}
			?>
		</div>
	</div>
</div>

