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

// Load backend language file for shared views in FE/BE
$lang = JFactory::getLanguage();
$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);

$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

if (!class_exists('comquick2cartHelper'))
{
	JLoader::register('comquick2cartHelper', $path);
	JLoader::load('comquick2cartHelper');
}

// Load assets
comquick2cartHelper::loadQuicartAssetFiles();
comquick2cartHelper::defineIcons('SITE');

$path = JPATH_SITE . '/components/com_quick2cart/helpers/storeHelper.php';

if (!class_exists('storeHelper'))
{
	JLoader::register('storeHelper', $path);
	JLoader::load('storeHelper');
}

$path = JPATH_SITE . '/components/com_quick2cart/helpers/zoneHelper.php';

if (!class_exists('zoneHelper'))
{
	JLoader::register('zoneHelper', $path);
	JLoader::load('zoneHelper');
}

$path = JPATH_SITE . '/components/com_quick2cart/helpers/product.php';

if (!class_exists('productHelper'))
{
	JLoader::register('productHelper', $path);
	JLoader::load('productHelper');
}

$path = JPATH_SITE . '/components/com_quick2cart/helpers/taxHelper.php';

if (!class_exists('taxHelper'))
{
	JLoader::register('taxHelper', $path);
	JLoader::load('taxHelper');
}

// Load ship helper
$path = JPATH_SITE . '/components/com_quick2cart/helpers/qtcshiphelper.php';

if (!class_exists('qtcshiphelper'))
{
	JLoader::register('qtcshiphelper', $path);
	JLoader::load('qtcshiphelper');
}

require_once JPATH_COMPONENT . '/controller.php';
JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');

$input = JFactory::getApplication()->input;

// Task for checking store releated view :checking authorization starts.
$view   = $input->get('view', 'category', 'STRING');
$input->set('view', $view);
$layout = $input->get('layout', 'default');
$ck     = $view . "_" . $layout;
$comquick2cartHelper = new comquick2cartHelper;
$path = JPATH_SITE . '/components/com_quick2cart/authorizeviews.php';
$params   = JComponentHelper::getParams('com_quick2cart');

include $path;

$store_releatedview = 0;

foreach ($rolearray as $arr)
{
	if (in_array($ck, $arr))
	{
		$store_releatedview = 1;
		break;
	}
}

if ($store_releatedview == 1)
{
	$user = JFactory::getUser();

	if (empty($user->id))
	{
		?>
			<div class="techjoomla-bootstrap" >
				<div class="well well-small" >
					<div class="alert alert-error alert-danger">
						<span ><?php echo JText::_('QTC_LOGIN'); ?> </span>
					</div>
				</div>
			</div>
			<!-- eoc techjoomla-bootstrap -->
		<?php
			return false;
	}

	$comquick2cartHelper = new comquick2cartHelper;
	$authority = $comquick2cartHelper->store_authorize($ck);

	if (empty($authority))
	{
		?>
			<div class="techjoomla-bootstrap" >
				<div class="well well-small" >
					<div class="alert alert-error alert-danger">
						<span ><?php echo JText::_('QTC_VIOLATING_UR_ROLE'); ?> </span>
					</div>
				</div>
			</div>
			<!-- eoc techjoomla-bootstrap -->
		<?php
		return false;
	}
}

	$result = $comquick2cartHelper->displaySocialToolbar();

	if ($params['multivendor'] == 0)
	{
		$result = $comquick2cartHelper->isAllowedToVisitView();

		if ($result == false)
		{
			return false;
		}
	}

// Global icon constants.
JHtml::_('behavior.framework');

if (version_compare(JVERSION, '3.0', 'lt'))
{
	JHtml::_('behavior.tooltip');
}
else
{
	// Tabstate
	JHtml::_('behavior.tabstate');

	// Bootstrap tooltip and chosen js
	JHtml::_('bootstrap.tooltip');

	// JHtml::_('behavior.multiselect');
}

$helperPath = JPATH_SITE . '/components/com_quick2cart/helpers/reports.php';

if (!class_exists('reportsHelper'))
{
	JLoader::register('reportsHelper', $helperPath);
	JLoader::load('reportsHelper');
}

$document = JFactory::getDocument();

// Frontend css
$document->addStyleSheet(JUri::root(true) . '/components/com_quick2cart/assets/css/quick2cart.css');

// Responsive tables
$document->addStyleSheet(JUri::root(true) . '/components/com_quick2cart/assets/css/q2c-tables.css');

// Include dependancies
jimport('joomla.application.component.controller');

$controller = JControllerLegacy::getInstance('Quick2cart');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
