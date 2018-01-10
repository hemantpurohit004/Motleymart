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
?>
<form method="get" name="qtcSearchForm" id="qtcSearchForm">
	<input type="hidden" name="option" value="com_quick2cart">
	<input type="hidden" name="view" value="category">
	<input type="hidden" name="layout" value="default">
	<div id="qtcFilterWrapper">
		<input type="text" name="filter_search" placeholder="<?php echo JText::_("MOD_SEARCH_PRODUCT");?>" onkeydown="if (event.keyCode == 13) { qtcSearchForm.form.submit(); return false; }">
	</div>
</form>
