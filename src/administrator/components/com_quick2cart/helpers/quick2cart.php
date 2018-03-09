<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

/**
 * Quick2cart component helper.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2CartHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   String  $vName  The name of the active view.
	 * @param   String  $queue  Queue.
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	public static function addSubmenu($vName = '', $queue = '')
	{
		$params = JComponentHelper::getParams('com_quick2cart');
		$multivendor_enable = $params->get('multivendor');

		if (JVERSION >= '3.0')
		{
			JHtmlSidebar::addEntry(JText::_('QTC_DASHBOARD'), 'index.php?option=com_quick2cart&view=dashboard', $vName == 'dashboard');
			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_STORES'), 'index.php?option=com_quick2cart&view=stores',
			$vName == 'stores'
			);

			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_quick2cart',
			$vName == 'categories'
			);

			/* Commented By Nitesh
			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_GLOBAL_ATTRIBUTES'), 'index.php?option=com_quick2cart&view=globalattributes', $vName == 'globalattributes'
			);

			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_GLOBAL_ATTRIBUTE_SET'), 'index.php?option=com_quick2cart&view=attributesets', $vName == 'attributesets'
			);

			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_GLOBAL_ATTRIBUTE_SET_MAPPING'), 'index.php?option=com_quick2cart&view=attributesetmapping',
			$vName == 'attributesetmapping'
			);
			*/

			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_PRODUCTS'), 'index.php?option=com_quick2cart&view=products',
			$vName == 'products'
			);

			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_ADMIN_PROMOTIONS'), 'index.php?option=com_quick2cart&view=promotions',
			$vName == 'promotions'
			);

			JHtmlSidebar::addEntry(JText::_('QTC_ORDERS'), 'index.php?option=com_quick2cart&view=orders', $vName == 'orders');

			JHtmlSidebar::addEntry(JText::_('COM_QUICK2CART_SALES_REPORT'), 'index.php?option=com_quick2cart&view=salesreport', $vName == 'salesreport');

			/*JHtmlSidebar::addEntry(
			JText::_('SALES_PER_VENDER_TITLE'), 'index.php?option=com_quick2cart&view=vendor&layout=salespervendor', $queue == 'salespervendor'
			);

			JHtmlSidebar::addEntry(
			JText::_('QTC_DELAY_ORDERS_REPORT'), 'index.php?option=com_quick2cart&view=delaysreport',
			$vName =='delaysreport'
			);*/

			if (!empty($multivendor_enable))
			{
				JHtmlSidebar::addEntry(JText::_('REPORTS'), 'index.php?option=com_quick2cart&view=payouts', $vName == 'payouts');
			}

			/* Commented By Nitesh
			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_COUNTRIES'), 'index.php?option=com_tjfields&view=countries&client=com_quick2cart',
			$vName == 'countries'
			);

			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_REGIONS'), 'index.php?option=com_tjfields&view=regions&client=com_quick2cart',
			$vName == 'regions'
			);

			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_ZONES'), 'index.php?option=com_quick2cart&view=zones',
			$vName == 'zones'
			);

			/*JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_ZONE'), 'index.php?option=com_quick2cart&view=zone&layout=edit',
			$vName == 'zone'
			);*/

			/* Commented By Nitesh
			if ($params->get('enableTaxtion', 0))
			{
				JHtmlSidebar::addEntry(
				JText::_('COM_QUICK2CART_TITLE_TAXTRATES'), 'index.php?option=com_quick2cart&view=taxrates',
				$vName == 'taxrates'
				);

				JHtmlSidebar::addEntry(
				JText::_('COM_QUICK2CART_ADMIN_TITLE_TAXPROFILES'), 'index.php?option=com_quick2cart&view=taxprofiles',
				$vName == 'taxprofiles'
				);
			}

			if ($params->get('shipping', 0))
			{
				JHtmlSidebar::addEntry(
				JText::_('COM_QUICK2CART_SHIPPING'), 'index.php?option=com_quick2cart&view=shipping',
				$vName == 'shipping');
				JHtmlSidebar::addEntry(
				JText::_('COM_QUICK2CART_SHIPPING_PROFILES'), 'index.php?option=com_quick2cart&view=shipprofiles', $vName == 'shipprofiles'
				);
			}

			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_LENGTHS'), 'index.php?option=com_quick2cart&view=lengths',
			$vName == 'lengths'
			);

			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_WEIGHTS'), 'index.php?option=com_quick2cart&view=weights',
			$vName == 'weights');

			$group_link = "index.php?option=com_tjfields&view=groups&client=com_quick2cart.product";
			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_FORM_GROUP'), $group_link, $vName == 'group');

			$fields_link = "index.php?option=com_tjfields&view=fields&client=com_quick2cart.product";
			JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_FORM_FIELDS'), $fields_link, $vName == 'fields');
			*/
		}
		else
		{
			JSubMenuHelper::addEntry(JText::_('QTC_DASHBOARD'), 'index.php?option=com_quick2cart&view=dashboard', $vName == 'dashboard');
			JSubMenuHelper::addEntry(JText::_('COM_QUICK2CART_TITLE_STORES'), 'index.php?option=com_quick2cart&view=stores', $vName == 'stores');

			JSubMenuHelper::addEntry(
			JText::_('COM_QUICK2CART_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_quick2cart',
			$vName == 'categories'
			);

			JSubMenuHelper::addEntry(JText::_('COM_QUICK2CART_PRODUCTS'), 'index.php?option=com_quick2cart&view=products', $vName == 'products');
			JSubMenuHelper::addEntry(JText::_('QTC_ORDERS'), 'index.php?option=com_quick2cart&view=orders', $vName == 'orders');

			JSubMenuHelper::addEntry(JText::_('COM_QUICK2CART_SALES_REPORT'), 'index.php?option=com_quick2cart&view=salesreport', $vName == 'salesreport');

			/*JSubMenuHelper::addEntry(
			JText::_('SALES_PER_VENDER_TITLE'), 'index.php?option=com_quick2cart&view=vendor&layout=salespervendor',
			$queue == 'salespervendor'
			);

			JSubMenuHelper::addEntry(
			JText::_('QTC_DELAY_ORDERS_REPORT'), 'index.php?option=com_quick2cart&view=delaysreport',
			$vName =='delaysreport'
			);*/

			if (!empty($multivendor_enable))
			{
				JSubMenuHelper::addEntry(JText::_('REPORTS'), 'index.php?option=com_quick2cart&view=payouts', $vName == 'payouts');
			}

			JSubMenuHelper::addEntry(JText::_('QTC_COUPON'), 'index.php?option=com_quick2cart&view=coupons', $vName == 'coupons');

			JSubMenuHelper::addEntry(
			JText::_('COM_QUICK2CART_TITLE_COUNTRIES'), 'index.php?option=com_tjfields&view=countries&client=com_quick2cart',
			$vName == 'countries'
			);

			JSubMenuHelper::addEntry(
			JText::_('COM_QUICK2CART_TITLE_REGIONS'), 'index.php?option=com_tjfields&view=regions&client=com_quick2cart',
			$vName == 'regions'
			);

			JSubMenuHelper::addEntry(
			JText::_('COM_QUICK2CART_TITLE_ZONES'), 'index.php?option=com_quick2cart&view=zones',
			$vName == 'zones'
			);

			/*JHtmlSidebar::addEntry(
			JText::_('COM_QUICK2CART_TITLE_ZONE'), 'index.php?option=com_quick2cart&view=zone&layout=edit',
			$vName == 'zone'
			);*/

			if ($params->get('enableTaxtion', 0))
			{
				JSubMenuHelper::addEntry(
				JText::_('COM_QUICK2CART_TITLE_TAXTRATES'), 'index.php?option=com_quick2cart&view=taxrates',
				$vName == 'taxrates'
				);

				JSubMenuHelper::addEntry(
				JText::_('COM_QUICK2CART_TITLE_TAXPROFILES'), 'index.php?option=com_quick2cart&view=taxprofiles',
				$vName == 'taxprofiles'
				);
			}

			if ($params->get('shipping', 0))
			{
				JSubMenuHelper::addEntry(
				JText::_('COM_QUICK2CART_SHIPPING'), 'index.php?option=com_quick2cart&view=shipping',
				$vName == 'shipping'
				);

				JSubMenuHelper::addEntry(
				JText::_('COM_QUICK2CART_SHIPPING_PROFILES'), 'index.php?option=com_quick2cart&view=shipprofiles', $vName == 'shipprofiles'
				);
			}

			JSubMenuHelper::addEntry(
			JText::_('COM_QUICK2CART_TITLE_LENGTHS'), 'index.php?option=com_quick2cart&view=lengths',
			$vName == 'lengths'
			);

			JSubMenuHelper::addEntry(
			JText::_('COM_QUICK2CART_TITLE_WEIGHTS'), 'index.php?option=com_quick2cart&view=weights',
			$vName == 'weights'
			);

			$group_link = "index.php?option=com_tjfields&view=groups&client=com_quick2cart.product";
			JSubMenuHelper::addEntry(
			JText::_('COM_QUICK2CART_TITLE_FORM_GROUP'), $group_link, $vName == 'groups');

			$fields_link = "index.php?option=com_tjfields&view=fields&client=com_quick2cart.product";
			JSubMenuHelper::addEntry(
			JText::_('COM_QUICK2CART_TITLE_FORM_FIELDS'), $fields_link, $vName == 'fields');
		}

		if ($vName == 'categories')
		{
			JToolbarHelper::title(
			JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('COM_QUICK2CART')),
			'Quick2Cart-categories'
			);
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return JObject
	 *
	 * @since 1.6
	 */
	public static function getActions()
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_quick2cart';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
