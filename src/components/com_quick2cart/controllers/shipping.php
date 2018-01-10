<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Shipping list controller class
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerShipping extends quick2cartController
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->set('suffix', 'shipping');
	}

	/**
	 * Method Ship view.
	 *
	 * @since	2.2
	 *
	 * @return void
	 */
	public function getShipView()
	{
		$app = JFactory::getApplication();
		$qtcshiphelper = new qtcshiphelper;
		$comquick2cartHelper = new comquick2cartHelper;
		$plgActionRes = array();

		$jinput = $app->input;
		$extension_id = $jinput->get('extension_id');
		$plugview = $jinput->get('plugview');

		// Plugin view is not found in URL then check in post array.
		if (empty($plugview))
		{
			$plugview = $jinput->post->get('plugview');
		}

		// If extension related view
		if (!empty($extension_id))
		{
			// Task is not empty then then call plugin save handler
			/*if (!empty($plugTask))*/
			{
				$plugName = $qtcshiphelper->getPluginDetail($extension_id);

				// Call specific plugin trigger
				JPluginHelper::importPlugin('tjshipping', $plugName);
				$dispatcher = JDispatcher::getInstance();
				$plgRes = $dispatcher->trigger('TjShip_plugActionkHandler', array($jinput));

				if (!empty($plgRes))
				{
					$plgActionRes = $plgRes[0];
				}
			}
		}
		// Enque msg
		if (!empty($plgActionRes['statusMsg']))
		{
			$app->enqueueMessage($plgActionRes['statusMsg']);
		}

		// Extra plugin Url params.
		if (!empty($plgActionRes['urlPramStr']))
		{
			$plgUrlParam = '&' . $plgActionRes['urlPramStr'];
		}
		else
		{
			$plgUrlParam = '&plugview=';
		}

		$itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$link = 'index.php?option=com_quick2cart&view=shipping&layout=list' . $plgUrlParam . '&extension_id=' . $extension_id . '&Itemid=' . $itemid;
		$this->setRedirect(JRoute::_($link, false));
	}

	/**
	 * This function calls respective task on respective plugin.
	 *
	 * @since	2.2
	 *
	 * @return void
	 */
	public function qtcHandleShipAjaxCall ()
	{
		$plgActionRes = '';
		$app = JFactory::getApplication();
		$qtcshiphelper = new qtcshiphelper;
		$comquick2cartHelper = new comquick2cartHelper;
		$jinput = JFactory::getApplication()->input;

		$extension_id = $jinput->get('extension_id');

		// Get plugin detail
		$plugName = $qtcshiphelper->getPluginDetail($extension_id);

		// Call specific plugin trigger
		JPluginHelper::importPlugin('tjshipping', $plugName);
		$dispatcher = JDispatcher::getInstance();
		$plgRes = $dispatcher->trigger('TjShip_AjaxCallHandler', array($jinput));

		if (!empty($plgRes))
		{
			$plgActionRes = $plgRes[0];
		}

		echo $plgActionRes;
		$app->close();
	}

	/**
	 * Function checkDeliveryAvailability.
	 *
	 * @since	2.2
	 *
	 * @return void
	 */
	public function checkDeliveryAvailability()
	{
		$input = JFactory::getApplication()->input;
		$post = $input->post;
		$params = JComponentHelper::getParams('com_quick2cart');

		// @TODO SET ITEM LEVEL AS DEF
		$shippingMode = $params->get('shippingMode', 'itemLevel');

		$store_id = $input->get('store_id', '0', 'int');
		$item_id = $input->get('item_id', '', 'int');
		$delivery_pincode = $input->get('delivery_pincode', '', 'int');
		$shippingInfo = new stdclass;
		$shippingInfo->store_id = $store_id;
		$shippingInfo->item_id = $item_id;
		$shippingInfo->delivery_pincode = $delivery_pincode;

		if ($shippingMode == "orderLeval")
		{
			JPluginHelper::importPlugin('qtcshipping');
			$dispatcher = JDispatcher::getInstance();
			$plgRes = $dispatcher->trigger('getShippingProviders', array($shippingInfo));
		}
		else
		{
			JPluginHelper::importPlugin('tjshipping');
			$dispatcher = JDispatcher::getInstance();
			$plgRes = $dispatcher->trigger('getShippingProviders', array($shippingInfo));
		}
	}
}
