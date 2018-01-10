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

jimport('joomla.plugin.plugin');

/**
 * System plguin
 *
 * @package     Plgshare_For_Discounts
 * @subpackage  site
 * @since       1.0
 */
class PlgSystemQtc_Sys extends JPlugin
{
	/**
	 * [onAfterRoute description]
	 *
	 * @return  [type]  [description]
	 */
	public function onAfterRoute()
	{
		$document = JFactory::getDocument();
		$app      = JFactory::getApplication();

		// Return if called from backend EXCEPT FOR INSTALLER
		if ($app->getName() != 'site')
		{
			$jinput = JFactory::getApplication()->input;
			$option = $jinput->get("option");

			if ($option == "com_installer")
			{
				$document->addStyleSheet(JUri::root(true) . '/media/techjoomla_strapper/css/bootstrap.min.css');
			}
			elseif ($option == "com_quick2cart" && $this->_exits_q2c())
			{
				$this->_loadHelperFiles();
			}

			return;
		}

		// IF Q2C NOT EXIST
		if (!$this->_exits_q2c())
		{
			return;
		}

		if (!defined('TJ_QTC_MULTI_LOAD'))
		{
			$this->_loadHelperFiles();
			$document  = JFactory::getDocument();
			/*bootstrap related*/
			$comparams = JComponentHelper::getParams('com_quick2cart');
			$document->addStyleSheet(JUri::root(true) . '/components/com_quick2cart/assets/css/quick2cart.css');

			/* Now we are usng common tjassetloader plg so removed
			include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
			TjAkeebaStrapper::bootstrap();
			*/

			// Loading tj strapper
			jimport('joomla.filesystem.file');
			$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

			if (JFile::exists($tjStrapperPath))
			{
				require_once $tjStrapperPath;
				TjStrapper::loadTjAssets('com_quick2cart');
			}

			// FOR J3.X. some template require to load bootstrap file so LOAD IT
			$laod_boostrap = $comparams->get('loadBootstrap');

			if (!empty($laod_boostrap))
			{
				// $document->addStyleSheet(JUri::root(true) . '/media/jui/css/bootstrap.min.css');
			}

			define('TJ_QTC_MULTI_LOAD', 1);
		}
	}

	/**
	 * [J1.5 trigger for user login]
	 *
	 * @param   [type]  $user     [description]
	 * @param   [type]  $options  [description]
	 *
	 * @return  [type]            [description]
	 */
	public function onLoginUser($user, $options)
	{
		$app = JFactory::getApplication();

		if ($app->getName() != 'site')
		{
			return;
		}

		if (!$this->_exits_q2c())
		{
			return;
		}

		$db      = JFactory::getDBO();
		$session = JFactory::getSession();
		$path    = JPATH_SITE . '/components/com_quick2cart/helper.php';

		if (!class_exists('comquick2cartHelper'))
		{
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$currentsession      = $session->getId();
		$old_sessionid       = $session->get('old_sessionid');
		$old_sessionid       = $currentsession;

		$user_id      = intval(JUserHelper::getUserId($user['username']));

		// Gives last cart id
		$oldcartid    = $comquick2cartHelper->getcartidForuser($user_id);
		$guestcart_id = $comquick2cartHelper->guestCartId($old_sessionid);

		if ($oldcartid)
		{
			if ($guestcart_id)
			{
				/* condition no 11:: IF GUEST CART_id AND USER_CART_ID  BOTH FOUND THEN	delete rec with user_id*/
				$query = "Select cart_id FROM #__kart_cart WHERE user_id='$user_id' ORDER BY last_updated DESC";
				$db->setQuery($query);
				$cart_ids = $db->loadColumn();

				if (!empty($cart_ids))
				{
					$comquick2cartHelper->deleteCartItemRec($cart_ids);
				}

				$q = "DELETE FROM #__kart_cart WHERE user_id=" . $user_id;
				$db->setQuery($q);
				$db->execute();

				// Update cartid from 0 to 1
				$row               = new stdClass;
				$row->cart_id      = $guestcart_id;
				$row->session_id   = $old_sessionid;
				$row->user_id      = $user_id;
				$row->last_updated = date("Y-m-d H:i:s");

				if (!$db->updateObject('#__kart_cart', $row, 'cart_id'))
				{
					echo $db->stderr();

					return false;
				}
			}
			else
			{
				/*  condition no 10::IF USER_CART_ID   and GUST_CART_ID NOT FOUND THEN
				delete all entry Except last*/

				$query = "Select cart_id FROM #__kart_cart WHERE user_id='$user_id' ORDER BY last_updated DESC";
				$db->setQuery($query);
				$cart_ids = $db->loadColumn();

				unset($cart_ids[0]);

				if (!empty($cart_ids))
				{
					$comquick2cartHelper->deleteCartItemRec($cart_ids);
				}

				$q = "DELETE FROM #__kart_cart WHERE user_id=" . $user_id . " And `cart_id` !=$oldcartid ";
				$db->setQuery($q);
				$db->execute();
			}
		}
		else
		{
			/* condition no 01:: IF USER_ID_CART NOT FOUND  AND GUEST CART IS PRESENT THEN 	Update user id (0-> id)entry in cart table aginst oldsession*/
			if ($guestcart_id)
			{
				$row               = new stdClass;
				$row->cart_id      = $guestcart_id;
				$row->session_id   = $old_sessionid;
				$row->user_id      = $user_id;
				$row->last_updated = date("Y-m-d H:i:s");

				if (!$db->updateObject('#__kart_cart', $row, 'cart_id'))
				{
					echo $db->stderr();

					return false;
				}
			}
		}
	}

	/**
	 * [J2.5 trigger for user login]
	 *
	 * @param   [type]  $user     [description]
	 * @param   array   $options  [description]
	 *
	 * @return  [type]            [description]
	 */
	public function onUserLogin($user, $options = array())
	{
		$this->onLoginUser($user, $options);
	}

	/**
	 * [Check whether component is installed]
	 *
	 * @return  [type]  [description]
	 */
	function _exits_q2c()
	{
		jimport('joomla.filesystem.file');

		if (JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * [_loadHelperFiles ]
	 *
	 * @return  [type]  [description]
	 */
	function _loadHelperFiles()
	{
		// Main helper
		$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

		if (!class_exists('comquick2cartHelper'))
		{
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		// LOAD STORE HELPER
		$path = JPATH_SITE . '/components/com_quick2cart/helpers/storeHelper.php';

		if (!class_exists('storeHelper'))
		{
			JLoader::register('storeHelper', $path);
			JLoader::load('storeHelper');
		}

		// LOAD product HELPER
		$path = JPATH_SITE . '/components/com_quick2cart/helpers/product.php';

		if (!class_exists('productHelper'))
		{
			JLoader::register('productHelper', $path);
			JLoader::load('productHelper');
		}
	}
}
