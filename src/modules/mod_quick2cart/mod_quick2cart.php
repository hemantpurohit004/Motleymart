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

	$doc = JFactory::getDocument();
	$lang = JFactory::getLanguage();
	$lang->load('mod_quick2cart', JPATH_SITE);

	JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
	$model = new Quick2cartModelcart;
	$cart = $model->getCartitems();

	// Check for promotions
	$path = JPATH_SITE . '/components/com_quick2cart/helpers/promotion.php';

	if (!class_exists('PromotionHelper'))
	{
		JLoader::register('PromotionHelper', $path);
		JLoader::load('PromotionHelper');
	}

	$beforecartmodule = '';
	$aftercartdisplay = '';
	$aftercartdisplay = '';

	$promotionHelper = new PromotionHelper;
	$coupon = $promotionHelper->getSessionCoupon();
	$promotions = $promotionHelper->getCartPromotionDetail($cart, $coupon);

	// Trigger onBeforeCartModule
	$dispatcher = JDispatcher::getInstance();
	JPluginHelper::importPlugin('system');
	$result = $dispatcher->trigger('onQuick2cartBeforeCartModuleDisplay');

	if (!empty($result))
	{
		$beforecartmodule .= $result[0];
	}

	// Depricated start
	$result = $dispatcher->trigger('onBeforeCartModule');

	if (!empty($result))
	{
		$beforecartmodule .= $result[0];
	}

	// Depricated End

	// Trigger onAfterCartModule
	$dispatcher = JDispatcher::getInstance();
	JPluginHelper::importPlugin('system');
	$result = $dispatcher->trigger('onQuick2cartAfterCartModuleDisplay');

	if (!empty($result))
	{
		$aftercartdisplay .= $result[0];
	}

	// Depricated start
	$result = $dispatcher->trigger('onAfterCartModule');

	if (!empty($result))
	{
		$aftercartdisplay .= $result[0];
	}
	// Depricated End

	if (version_compare(JVERSION, '3.0', 'lt'))
	{
		// Define wrapper class
		if (!defined('Q2C_WRAPPER_CLASS'))
		{
			define('Q2C_WRAPPER_CLASS', "q2c-wrapper techjoomla-bootstrap");
		}
	}
	else
	{
		// Define wrapper class
		if (!defined('Q2C_WRAPPER_CLASS'))
		{
			define('Q2C_WRAPPER_CLASS', "q2c-wrapper");
		}

		// Bootstrap tooltip and chosen js
		JHtml::_('bootstrap.tooltip');
		JHtml::_('behavior.multiselect');
	}

	$moduleParams = $params;
	$hideOnCartEmpty = $moduleParams->get('hideOnCartEmpty', 0);
	$ckout_text = $moduleParams->get('checkout_text', '');
	$ckout_text = trim($ckout_text);
	$moduleclass_sfx = $moduleParams->get('moduleclass_sfx');
	require JModuleHelper::getLayoutPath('mod_quick2cart', $params->get('layout', 'default'));
}

