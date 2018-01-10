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

//JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.modal');
$comquick2cartHelper = new comquick2cartHelper
?>

<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
	<form id="adminForm" name="adminForm"   method="post" class="form-validate" enctype="multipart/form-data">
		<?php
		$actionViewName = 'shipprofile';
		$actionControllerName = 'shipprofile';
		$formName = 'adminForm';
		// Check for view override
		$att_list_path = $comquick2cartHelper->getViewpath('shipprofile', 'shipprofiledata', "ADMINISTRATOR", "ADMINISTRATOR");
		ob_start();
		include($att_list_path);
		$item_options = ob_get_contents();
		ob_end_clean();
		echo $item_options;
		?>
	</form>

</div>
