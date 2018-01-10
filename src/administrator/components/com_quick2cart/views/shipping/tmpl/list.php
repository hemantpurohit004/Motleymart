<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// no direct access
defined('_JEXEC') or die;

$lang = JFactory::getLanguage();
$lang->load('com_quick2cart', JPATH_SITE);

?>
<div class=" <?php echo Q2C_WRAPPER_CLASS; ?>">
	<?php
	if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php
	else : ?>
		<div id="j-main-container">
		<?php
	endif; ?>
		<?php
		if (!empty($this->form))
		{
			echo $this->form;
		}
		?>
	</div>
</div>
