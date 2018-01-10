<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.defined('_JEXEC') or die();

$input = JFactory::getApplication()->input;

if (JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
{
	require JModuleHelper::getLayoutPath('mod_q2c_search');
}
