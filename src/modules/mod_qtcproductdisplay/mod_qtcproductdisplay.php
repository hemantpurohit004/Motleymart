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
	$doc  = JFactory::getDocument();
	$lang = JFactory::getLanguage();
	$lang->load('mod_qtcproductdisplay', JPATH_SITE);

	// GETTING MODULE PARAMS
	$prodLimit     = $params->get('limit', 2);
	$module_mode   = $params->get('module_mode', 'qtc_featured');
	$productHelper = new productHelper;

	if (!empty($module_mode))
	{
		switch ($module_mode)
		{
			case 'qtc_featured';
				$target_data = $productHelper->getAllFeturedProducts('', '', $prodLimit);
				break;

			case 'qtc_recentlyAdded';
				$target_data = $productHelper->getNewlyAdded_products($prodLimit);
				break;

			case 'qtc_recentlyBought';
				$target_data = $productHelper->getRecentlyBoughtproducts($prodLimit);
				break;

			case 'qtc_topSeller';
				$target_data = $productHelper->getTopSellerProducts('', '', $prodLimit);
				break;
		}
	}

	if (empty($target_data))
	{
		return false;
	}

	require JModuleHelper::getLayoutPath('mod_qtcproductdisplay');
}
