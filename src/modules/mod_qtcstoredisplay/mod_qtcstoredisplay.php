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
	$lang->load('mod_qtcstoredisplay', JPATH_SITE);

	// GETTING MODULE PARAMS
	$prodLimit = $params->get('limit');

	// Allow to display all stores
	if ($prodLimit == -1)
	{
		$prodLimit = '';
	}

	// $modSufx=$params->get('moduleclass_sfx');

	$model                 = new productHelper;
	$module_mode           = $params->get('module_mode', 'qtc_latestStore');
	$qtc_modViewType       = $params->get('qtc_modViewType', 'qtc_blockView');
	$qtc_mod_scroll_height = $params->get('scroll_height');


	if (!empty($module_mode))
	{
		switch ($module_mode)
		{
			case 'qtc_latestStore';
				$target_data = $model->getLatestStore(1, $prodLimit);
				break;

			case 'qtc_bestSellerStore';
				$target_data = $model->getTopSellerStore($prodLimit);
				break;

			case 'qtc_storeList';

				// $target_data = $model->getRecentlyBoughtproducts($prodLimit);
				// LOAD ALL STORE
				$storeHelper = new storeHelper;
				$target_data = $storeHelper->getStoreList(1, $prodLimit);


				break;
		}
	}

	if (empty($target_data))
	{
		return false;
	}

	require JModuleHelper::getLayoutPath('mod_qtcstoredisplay');
}
