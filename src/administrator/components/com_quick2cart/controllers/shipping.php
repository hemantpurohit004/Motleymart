<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

// Load Quick2cart Controller for list views
require_once __DIR__ . '/q2clist.php';

/**
 * Taxprofiles list controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerShipping extends Quick2cartControllerQ2clist
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
	 * This to get ship view
	 *
	 * @return  null
	 *
	 * @since  1.5
	 * */
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

			//  if (!empty($plugTask))
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
	 * This function calls respective task on respective plugin
	 *
	 * @return  null
	 *
	 * @since  1.5
	 * */
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
}
