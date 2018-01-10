<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('DS'))
{
	define('DS', '/');
}


/**
 * Example K2 Plugin to render YouTube URLs entered in backend K2 forms to video
 * players in the frontend.
 */

// Load the K2 Plugin API
JLoader::register('K2Plugin', JPATH_ADMINISTRATOR . '/components/com_k2/lib/k2plugin.php');

/**
 * Form field for Quick2cart
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class PlgK2Qtc_K2 extends K2Plugin
{
	// Some params
	var $pluginName = 'qtc_k2';

	var $pluginNameHumanReadable = 'Quick2Cart K2 Plugin';

	/**
	 * [plgK2Qtc_k2 description]
	 *
	 * @param   [type]  &$subject  [description]
	 * @param   [type]  $params    [description]
	 *
	 * @return  [type]            [description]
	 */
	function plgK2Qtc_k2 (&$subject, $params)
	{
		parent::__construct($subject, $params);
	}

	/**
	 * Called onAfterK2Save item: This function trigger to content trigger.
	 *
	 * @param   [type]  $item   [description]
	 * @param   [type]  $isNew  [description]
	 *
	 * @return  [type]          [description]
	 */
	function onAfterK2Save ($item, $isNew)
	{
		$pid = $item->id;
		$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

		if (! class_exists('comquick2cartHelper'))
		{
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		$input = JFactory::getApplication()->input;
		$post_data = $input->post;
		$comquick2cartHelper = new comquick2cartHelper;

		$client = $input->post->set('client', 'com_k2');
		$pid = $post_data->set('pid', $pid);

		$comquick2cartHelper = $comquick2cartHelper->saveProduct($post_data);
	}

	/**
	 * [Event to display (in the frontend) the YouTube URL as entered in the item form]
	 *
	 * @param   [type]  &$item       [description]
	 * @param   [type]  &$params     [description]
	 * @param   [type]  $limitstart  [description]
	 *
	 * @return  [type]               [description]
	 */
	function onK2AfterDisplayContent (&$item, &$params, $limitstart)
	{
		// Add Language file.
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);

		jimport('joomla.filesystem.file');

		if (!JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
		{
			return true;
		}

		$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

		if (!class_exists('comquick2cartHelper'))
		{
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		$mainframe = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart');
		$comquick2cartHelper = new comquick2cartHelper;
		$output = $comquick2cartHelper->getBuynow($item->id, 'com_k2');

		return $output;
	}
}
