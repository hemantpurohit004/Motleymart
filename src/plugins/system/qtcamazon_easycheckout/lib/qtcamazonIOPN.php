<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

define('_JEXEC', 1);

/**
 * Initialize the joomla instance
 *
 * @return  void
 *
 * @since   2.6
 */
function initializeJOOMLA()
{
	// $siteDir = str_replace("plugins/system/qtcamazon_easycheckout/qtcamazonIOPN.php", "", __FILE__);
	$siteDir = dirname(__FILE__);

	if (file_exists($siteDir . '/defines.php'))
	{
		include_once $siteDir . '/defines.php';
	}

	if (!defined('_JDEFINES'))
	{
		define('JPATH_BASE', $siteDir);
		require_once JPATH_BASE . '/includes/defines.php';
	}

	require_once JPATH_BASE . '/includes/framework.php';
	$app = JFactory::getApplication('site');
	$app->initialise();
}

// Initialize Joomla
initializeJOOMLA();

$jinput = JFactory::getApplication()->input;

/* Call Quick2cart trigger
require_once  JPATH_SITE . '/components/com_quick2cart/helper.php';
$qtcmainHelper = new comquick2cartHelper;
*/

// Call the plugin and get the result
$dispatcher = JDispatcher::getInstance();
JPluginHelper::importPlugin('system', "qtcamazon_easycheckout");
$results = $dispatcher->trigger('onATP_processIOPN');
