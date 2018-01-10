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

/**
 * This file define the icon set for admin view
 * */

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true) . '/media/techjoomla_strapper/vendors/font-awesome/css/font-awesome.min.css');

$qtcParams = JComponentHelper::getParams('com_quick2cart');
$currentBSViews = $qtcParams->get('currentBSViews', "bs3");
$icon_color = " icon-white ";

if (version_compare(JVERSION, '3.0', 'lt'))
{
	$icon_color = '';
}

// Check if icon set is already defined or not
if (!defined('Q2C_ICON_IS_DEFINED_CEHCK'))
{
	define('Q2C_ICON_TRASH', " fa fa-trash ");
	define('Q2C_ICON_ENVELOPE', " fa fa-envelope ");
	define('Q2C_ICON_ARROW_RIGHT', " fa fa-arrow-right ");
	define('Q2C_ICON_ARROW_CHEVRON_RIGH', " fa fa-chevron-right ");
	define('Q2C_ICON_ARROW_CHEVRON_LEFT', " fa fa-chevron-left ");
	define('QTC_ICON_SEARCH', " fa fa-search ");
	define('Q2C_TOOLBAR_ICON_SETTINGS', " fa fa-cog ");
	define('QTC_ICON_PUBLISH', " fa fa-check ");
	define('QTC_ICON_REFRESH', " fa fa-refresh ");
	define('QTC_ICON_USER', " fa fa-user ");
	define('QTC_ICON_UNPUBLISH', " fa fa-minus ");
	define('QTC_ICON_INFO', " fa fa-info ");
	define('Q2C_ICON_HOME', " fa fa-home ");
	define('QTC_ICON_CHECKMARK', " fa fa-check ");
	define('QTC_ICON_MINUS', " fa fa-minus ");
	define('QTC_ICON_PLUS', " fa fa-plus ");
	define('QTC_ICON_EDIT', " fa fa-edit ");
	define('QTC_ICON_CART', " fa fa-shopping-cart ");
	define('QTC_ICON_BACK', " fa fa-arrow-left ");
	define('QTC_ICON_REMOVE', " fa fa-remove ");
	define('QTC_ICON_LIST', " fa fa-list ");
	define('Q2C_TOOLBAR_ICON_CART', " fa fa-shopping-cart ");
	define('Q2C_ICON_RIGHT_HAND', " fa fa-hand-o-right ");
	define('QTC_ICON_CALENDER', " fa fa-calendar ");
	define('Q2C_TOOLBAR_ICON_HOME', Q2C_ICON_HOME);
	define('Q2C_TOOLBAR_ICON_LIST', QTC_ICON_LIST);
	define('Q2C_TOOLBAR_ICON_PLUS', QTC_ICON_PLUS);
	define('Q2C_TOOLBAR_ICON_USERS', " fa fa-user ");
	define('Q2C_TOOLBAR_ICON_COUPONS', " fa fa-gift ");
	define('Q2C_TOOLBAR_ICON_PAYOUTS', " fa fa-briefcase ");
	define('Q2C_ICON_WHITECOLOR', "");
}
