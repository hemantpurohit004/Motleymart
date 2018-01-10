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

if (!defined('DS'))
{
	define('DS', '/');
}

jimport('joomla.filesystem.file');


if (JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
{
	$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

	if (!class_exists('comquick2cartHelper'))
	{
		JLoader::register('comquick2cartHelper', $path);
		JLoader::load('comquick2cartHelper');
	}

	// Load assets
	comquick2cartHelper::loadQuicartAssetFiles();

	// LOAD LANGUAGE FILES
	$doc = JFactory::getDocument();
	$lang = JFactory::getLanguage();
	$lang->load('mod_qtc_categorylist', JPATH_SITE);

	require JModuleHelper::getLayoutPath('mod_qtc_categorylist');
}
