<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
/**
 * helper class for tjnotificationss
 *
 * @package     TJnotification
 * @subpackage  com_tjnotifications
 * @since       2.2
 */
class EcommHelper
{
	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    0.0.1
	 */
	function addSideBar()
	{
		/*JHtmlSidebar::addEntry(JText::_('<i class="fa fa-pencil-square-o"></i> Subscription form'), 'index.php?option=com_ecomm&view=subscription');
		JHtmlSidebar::addEntry(JText::_('<i class="fa fa-bars"></i>  Subscriptions plans'), 'index.php?option=com_ecomm&view=subscriptions');
		JHtmlSidebar::addEntry(JText::_('<i class="fa fa-user"></i>  User\'s Subscription form'), 'index.php?option=com_ecomm&view=usersubscription');
		JHtmlSidebar::addEntry(JText::_('<i class="fa fa-users"></i>  User\'s Subscriptions'), 'index.php?option=com_ecomm&view=usersubscriptions');
		JHtmlSidebar::addEntry(JText::_('<i class="fa fa-star"></i>  Rating form'), 'index.php?option=com_ecomm&view=rating');
		JHtmlSidebar::addEntry(JText::_('<i class="fa fa-star"></i>  Ratings'), 'index.php?option=com_ecomm&view=ratings');
		JHtmlSidebar::addEntry(JText::_('<i class="fa fa-image"></i>  Banner form'), 'index.php?option=com_ecomm&view=banner');
		JHtmlSidebar::addEntry(JText::_('<i class="fa fa-image"></i>  Banners'), 'index.php?option=com_ecomm&view=banners');
		JHtmlSidebar::addEntry(JText::_('<i class="fa fa-star"></i>  Feedbacks'), 'index.php?option=com_ecomm&view=feedbacks');*/

		$lang = JFactory::getLanguage();
		$extension = 'com_quick2cart';
		$base_dir = JPATH_ADMINISTRATOR;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		JHtmlSidebar::addEntry(JText::_('QTC_DASHBOARD'), 'index.php?option=com_quick2cart&view=dashboard', $vName == 'dashboard');
		JHtmlSidebar::addEntry(

		JText::_('COM_QUICK2CART_TITLE_STORES'), 'index.php?option=com_quick2cart&view=stores',
		$vName == 'stores'
		);

		JHtmlSidebar::addEntry(
		JText::_('COM_QUICK2CART_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_quick2cart',
		$vName == 'categories'
		);

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

		JHtmlSidebar::addEntry(JText::_('REPORTS'), 'index.php?option=com_quick2cart&view=payouts', $vName == 'payouts');

		JHtmlSidebar::addEntry(JText::_('Subscription Plans'), 'index.php?option=com_ecomm&view=subscriptions', $vName == 'subscriptions');

		JHtmlSidebar::addEntry(JText::_('Vendors'), 'index.php?option=com_ecomm&view=usersubscriptions', $vName == 'usersubscriptions');

		JHtmlSidebar::addEntry(JText::_('Banners'), 'index.php?option=com_ecomm&view=banners', $vName == 'banners');

		JHtmlSidebar::addEntry(JText::_('Feedbacks'), 'index.php?option=com_ecomm&view=feedbacks', $vName == 'feedbacks');

	}

	function getSubscriptionDetails($subscriptionId, $fields)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models');
		$subscriptionModel = JModelLegacy::getInstance('Subscription', 'EcommModel');

		$subscriptionDetails = $subscriptionModel->getItem($subscriptionId);
		if (!empty($subscriptionDetails))
		{
			return $subscriptionDetails->$fields;
		}

		return '';
	}

	function getProductDetails($productId, $fields)
	{
		// Get the product details
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_quick2cart/models');
		$modelCart = JModelLegacy::getInstance('Cart', 'Quick2cartModel');
		$productDetails = $modelCart->getItemRec($productId);

		if (!empty($productDetails))
		{
			return $productDetails->$fields;
		}

		return '';
	}
}
