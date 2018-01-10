<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Qtcshiphelper
 *
 * @package     Com_Quick2cart
 * @subpackage  site
 * @since       2.2
 */
class Qtcshiphelper
{
	/**
	 * Construct
	 *
	 * @since   2.2
	 */
	public function __construct()
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
	}

	/**
	 * Only returns plugins that have a specific event
	 *
	 * @param   string  $eventName  string name.
	 * @param   string  $folder     folder name.
	 *
	 * @since   2.2
	 * @return   result.
	 */
	public static function getPluginsWithEvent($eventName, $folder = 'tjshipping')
	{
		$return = array();

		if ($plugins = self::getPlugins($folder))
		{
			foreach ($plugins as $plugin)
			{
				if (self::hasEvent($plugin, $eventName))
				{
					$return[] = $plugin;
				}
			}
		}

		return $return;
	}

	/**
	 * Returns Array of active Plugins
	 *
	 * @param   iteger  $extension_id  extension_id id.
	 *
	 * @since   2.2
	 * @return   result.
	 */
	public static function getPluginDetail($extension_id)
	{
		$database = JFactory::getDBO();
		$query = "SELECT `element` FROM  #__extensions WHERE  enabled = '1' AND extension_id ={$extension_id}";
		$database->setQuery($query);

		return $database->loadResult();
	}

	/**
	 * GetPluginDetailByShipMethId
	 *
	 * @param   iteger  $shipmethId  shipmethId id.
	 *
	 * @since   2.2
	 * @return   result.
	 */
	public function getPluginDetailByShipMethId($shipmethId)
	{
		$database = JFactory::getDBO();
		$query = "SELECT  `extension_id`, sm.methodId
				FROM  `#__kart_shipProfileMethods` AS sm
				LEFT JOIN  `#__extensions` AS e ON sm.client = e.element
				WHERE sm.`id` =" . $shipmethId;
		$database->setQuery($query);

		return $database->loadAssoc();
	}

	/**
	 * Returns Array of active Plugins
	 *
	 * @param   string  $folder        the plugin JTable object
	 * @param   iteger  $extension_id  extension id.
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public static function getPlugins($folder = 'tjshipping', $extension_id = '')
	{
		$database = JFactory::getDBO();
		$order_query = " ORDER BY ordering ASC ";
		$folder = strtolower($folder);

		$query = "
		SELECT
		*
		FROM
		#__extensions
		WHERE  enabled = '1'
		AND
		LOWER(`folder`) = '{$folder}'
		{$order_query}
		";

		$database->setQuery($query);
		$data = $database->loadObjectList();

		return $data;
	}

	/**
	 * Returns HTML
	 *
	 * @param   Boolean  $event    the plugin JTable object
	 * @param   Boolean  $options  the name of the event to test for
	 * @param   string   $method   the name of the event to test for
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public static function getPluginsContent($event, $options, $method='vertical')
	{
		$text = "";
		jimport('joomla.html.pane');

		if (!$event)
		{
			return $text;
		}

		$args = array();
		$dispatcher	   = JDispatcher::getInstance();
		$results = $dispatcher->trigger($event, $options);

		if (!count($results) > 0)
		{
			return $text;
		}

		// Grab content
		switch (strtolower($method))
		{
			case "vertical":
				for ($i = 0; $i < count($results); $i++)
				{
					$result = $results[$i];
					$title = $result[1] ? JText::_($result[1]) : JText::_('Info');
					$content = $result[0];

					// Vertical
					$text .= '<p>' . $content . '</p>';
				}
				break;
			case "tabs":
				break;
		}

		return $text;
	}

	/**
	 * Checks if a plugin has an event (NOT USED).
	 *
	 * @param   object  $element    the plugin JTable object
	 * @param   string  $eventName  the name of the event to test for
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public static function hasEvent($element, $eventName)
	{
		$success = false;

		if (!$element || !is_object($element))
		{
			return $success;
		}

		if (!$eventName || !is_string($eventName))
		{
			return $success;
		}

		// Check if they have a particular event
		$import 	= JPluginHelper::importPlugin(strtolower('tjshipping'), $element->element);
		$dispatcher	= JDispatcher::getInstance();
		$result 	= $dispatcher->trigger($eventName, array($element));

		if (in_array(true, $result, true))
		{
			$success = true;
		}

		return $success;
	}

	/**
	 * Get Method detail.
	 *
	 * @param   integer  $methodId  methodId.
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public function getShipMethDetail($methodId)
	{
		if (empty($methodId))
		{
			return array();
		}

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Here trule for  tax rule
		$query->select('m.*');
		$query->from('#__kart_zoneShipMethods AS m');
		$query->where('m.id=' . $methodId);
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * This function Save shipping method detail ( for defaul ship plugin).
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public function createShippingMethod($jinput)
	{
		$db = JFactory::getDBO();
		$post = $jinput->post;
		$shipForm = $post->get('shipForm', array(), 'ARRAY');

		$obj = new stdClass;
		$obj->id = !empty($shipForm['methodId']) ? $shipForm['methodId'] : '0';
		$obj->name = !empty($shipForm['name']) ? $shipForm['name'] : '0';
		$obj->store_id = $shipForm['store_id'];
		$obj->taxprofileId = $shipForm['taxprofileId'];
		$obj->state = $shipForm['state'];
		$obj->shipping_type = $shipForm['shipping_type'];

		switch ($obj->shipping_type)
		{
			// For ship meth = price per store item
			case 3 :

				// Add first curr field value. Useful wn we use currency convertor
				if (is_array($shipForm['min_value']))
				{
					foreach ($shipForm['min_value'] as $curr)
					{
						$obj->min_value	 = $curr;
						break;
					}
				}
				else
				{
					$obj->min_value	 = $shipForm['min_value'];
				}

				// Add first curr field value. Useful wn we use currency convertor
				if (is_array($shipForm['max_value']))
				{
					foreach ($shipForm['max_value'] as $curr)
					{
						$obj->max_value	 = $curr;
						break;
					}
				}
				else
				{
					$obj->max_value	 = $shipForm['max_value'];
				}

			break;

			// For ship meth = qtc/weight per store item
			case 1 || 2 :
				$obj->min_value	 = $shipForm['min_value'];
				$obj->max_value	 = $shipForm['max_value'];
			break;
		}

		if ($obj->id)
		{
			$action = 'updateObject';
		}
		else
		{
			$action = 'insertObject';
		}

		if (!$db->$action('#__kart_zoneShipMethods', $obj, 'id'))
		{
			echo $this->_db->stderr();

		return false;
		}

		// For meth = price per store item , Add entry in __kart_zoneShipMethods curr tb.
		if ($obj->shipping_type == 3)
		{
			foreach ($shipForm['min_value'] as $currName => $value)
			{
				$methCurrTb = new stdClass;
				$methCurrTb->methodId = $obj->id;
				$methCurrTb->min_value = $value;
				$methCurrTb->max_value = $shipForm['max_value'][$currName];
				$methCurrTb->currency = $currName;

				// Check for new/edit
				$query = $db->getQuery(true);
				$query->select('m.id');
				$query->from('#__kart_zoneShipMethodCurr AS m');
				$query->where('m.methodId=' . $methCurrTb->methodId);
				$query->where("m.currency='" . $methCurrTb->currency . "'");
				$db->setQuery($query);
				$id = $db->loadResult();

				$methCurrTb->id = !empty($id) ? $id :'';

				if ($methCurrTb->id)
				{
					$newAction = 'updateObject';
				}
				else
				{
					$newAction = 'insertObject';
				}

				if (!$db->$newAction('#__kart_zoneShipMethodCurr', $methCurrTb, 'id'))
				{
					echo $this->_db->stderr();

					return false;
				}
			}
		}
		elseif(!empty($shipForm['methodId']))
		{
			// For edit method : DELETE entris from  __kart_zoneShipMethodCurr if u have changed shiptype
			$db = JFactory::getDBO();
			$query = "DELETE FROM #__kart_zoneShipMethodCurr WHERE methodId=" . $shipForm['methodId'];
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

		return 0;
			}
		}

		return $obj->id;
	}

	/**
	 * Delete shipping method.
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 * @param   string  $client  state.
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public function deleteShipMeth($jinput, $client)
	{
		$db = JFactory::getDBO();
		$post = $jinput->post;
		$shipMethId = $post->get('shipMethId', '');
		$count = 0;

		if (empty($shipMethId))
		{
			$cidArray = $post->get('cid', array(), 'ARRAY');
		}
		else
		{
			$cidArray[] = $shipMethId;
		}

		$qtcshiphelper = new qtcshiphelper;

		foreach ($cidArray as $cid)
		{
			try
			{
				// Check whether entry present in ship profile methods .
				$count_id = $qtcshiphelper->isAllowedToDelShipmethod($cid, $client);

				if ($count_id === true)
				{
					print $query = $db->getQuery(true)
						->delete('#__kart_zoneShipMethods')
						->where('id =' . $cid);
					$db->setQuery($query);

					if (!$db->execute())
					{
						$this->setError($this->_db->getErrorMsg());

						return 0;
					}

					$count++;
				}
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return 0;
			}
		}

		return $count;
	}

	/**
	 * Method check whether Shipmethod is allowed to delete or not.  If not the enqueue error message accordingly..
	 *
	 * @param   string  $id      shipmetho id .
	 * @param   string  $client  client id .
	 *
	 * @since   2.2
	 * @return   boolean true or false.
	 */
	public function isAllowedToDelShipmethod($id, $client)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Check in tax related table
		$query->select('count(*)');
		$query->from('#__kart_shipProfileMethods AS z');
		$query->where('z.methodId=' . $id);
		$query->where('z.client="' . $client . '"');

		try
		{
			$db->setQuery($query);
			$count = $db->loadResult();

			if (!empty($count))
			{
				$errMsg = JText::sprintf('COM_QUICK2CART_SHIPMETHO_DEL_FOUND_SHIPPROFILE', $id);
				$app->enqueueMessage($errMsg, 'error');

				return false;
			}
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Render view.
	 *
	 * @param   string  $type    An optional associative array of configuration settings.
	 * @param   string  $prefix  An optional associative array of configuration settings.
	 * @param   array   $config  An optional associative array of configuration settings.
	 *
	 * @since   2.2
	 * @return   null
	 */

	public function getTable($type = 'Shipmethods', $prefix = 'Quick2cartTable', $config = array())
	{
		// @TODO fin batter way to load tables in helper
		$path = JPATH_ADMINISTRATOR . '/components/com_quick2cart/tables/shipmethods.php';
		JLoader::register('Quick2cartTableShipmethods', $path);

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * ChangeShipMethState
	 *
	 * @param   object   $jinput  Joomla's jinput Object.
	 * @param   integer  $state   state.
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public function changeShipMethState($jinput, $state)
	{
		$post = $jinput->post;
		$shipMethId = $post->get('shipMethId', '');

		if (empty($shipMethId))
		{
			$cidArray = $post->get('cid', array(), 'ARRAY');
		}
		else
		{
			$cidArray[] = $shipMethId;
		}

		$methIds = implode(',', $cidArray);

		$db = JFactory::getDBO();
		$query = "UPDATE #__kart_zoneShipMethods SET state=" . $state . " WHERE id IN (" . $methIds . ")";
		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}
	}

	/**
	 * This function add zone specific rates  for shipping  method detail ( for default ship plugin).
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public function addShipMethRate($jinput)
	{
		$db = JFactory::getDBO();
		$returnRes = array('errorMsg' => '',);
		$post = $jinput->post;
		$shipForm = $post->get('shipForm', array(), 'ARRAY');

		$zone_id = $post->get('zone_id');
		$methodId = $jinput->get('methodId', 0);
		$rateId = $jinput->get('rateId', '');

		if (empty($zone_id) || empty($methodId))
		{
			$returnRes['errorMsg'] = JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_SOMETHING_MISSING");
		}

		$obj = new stdClass;
		$obj->id = $rateId;
		$obj->zone_id = $zone_id;
		$obj->methodId = $methodId;
		$obj->rangeFrom = $post->get('rangeFrom', 0);
		$obj->rangeTo = $post->get('rangeTo', 99999);

		if ($obj->id)
		{
			$action = 'updateObject';
		}
		else
		{
			$action = 'insertObject';
		}

		$returnRes['rangeTd'] = $obj->rangeFrom . ' ' . JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_TO") . ' ' . $obj->rangeTo;

		// After success full inesertion add its price related things
		$comParams = JComponentHelper::getParams('com_quick2cart');

		// Get Currencies
		$currencies = $comParams->get('addcurrency');
		$curr = explode(',', $currencies);

		if (!$db->$action('#__kart_zoneShipMethodRates', $obj, 'id'))
		{
			// If error to insert
			echo $this->_db->stderr();

			return false;
		}

		$returnRes['rateId'] = $obj->id;
		$shipCost = $post->get('shipCost', array(), 'ARRAY');
		$handleCost = $post->get('handleCost', array(), 'ARRAY');

		foreach ($curr as $currKey => $value)
		{
			if ($shipCost[$value] == '')
			{
				$returnRes['status'] = 0;

				return json_encode($returnRes);
			}

			if ($handleCost[$value] == '')
			{
				$returnRes['status'] = 0;

				return json_encode($returnRes);
			}
		}

		$returnRes['shipCostTd'] = '';
		$returnRes['handleCostTd'] = '';

		$currencyCount = count($curr);
		$currCount = 0;

		// Key contain 0,1,2... // value contain INR...
		foreach ($curr as $currKey => $value)
		{
			$currCount++;

			// Check whether previously exist rec.
			$query = " SELECT id FROM `#__kart_zoneShipMethodRateCurr`
						WHERE  rateId = " . $obj->id . " AND currency = '" . $value . "'";
			$db->setQuery($query);
			$id = $db->loadResult();
			$objPrice = new stdClass;

			if (!empty($id))
			{
				// Update
				$objPrice->id = $id;
				$action = 'updateObject';
			}
			else
			{
				// Insert
				$objPrice->id = '';
				$action = 'insertObject';
			}

			$objPrice->rateId = $obj->id;
			$objPrice->shipCost = isset($shipCost[$value]) ? $shipCost[$value] : 0;
			$objPrice->handleCost = isset($handleCost[$value]) ? $handleCost[$value] : 0;
			$objPrice->currency = $value;

			if (!$db->$action('#__kart_zoneShipMethodRateCurr', $objPrice, 'id'))
			{
				// If error to insert
				echo $this->_db->stderr();

				return false;
			}

			$returnRes['shipCostTd'] .= ' ' . number_format($objPrice->shipCost, 2) . ' ' . $objPrice->currency;

			if ($currCount < $currencyCount)
			{
				$returnRes['shipCostTd'] .= ',';
			}

			$returnRes['handleCostTd'] .= ' ' . number_format($objPrice->handleCost, 2) . ' ' . $objPrice->currency;

			if ($currCount < $currencyCount)
			{
				$returnRes['handleCostTd'] .= ',';
			}
		}

		$returnRes['status'] = 1;

		return json_encode($returnRes);
	}

	/**
	 * This function update zone specific shipping rates ( for default ship plugin).
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public function qtcUpdateShipMethRate($jinput)
	{
		$qtcshiphelper = new qtcshiphelper;

		return $qtcshiphelper->addShipMethRate($jinput);
	}

	/**
	 * This delete zone specific rates  for shipping  method detail ( for default ship plugin).
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public function qtcDelshipMethRate($jinput)
	{
		$db = JFactory::getDBO();
		$returnRes = array('errorMsg' => '', 'error' => 0);
		$post = $jinput->post;

		$rateId = $post->get('rateId');

		if (empty($rateId))
		{
			$returnRes['errorMsg'] = JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_SET_INVALID_REATE_ID");
		}

		// Delete ship meth rate
		$query = "DELETE FROM `#__kart_zoneShipMethodRates`  WHERE id=" . (int) $rateId;
		$db->setQuery($query);
		$result = $db->execute();

		// Delete ship, hanlde cost aginst reate
		$query = "DELETE FROM `#__kart_zoneShipMethodRateCurr`  WHERE rateId=" . (int) $rateId;
		$db->setQuery($query);
		$result = $db->execute();

		return json_encode($returnRes);
	}

	/**
	 * This  provides specific srates  for shipping  method detail ( for default ship plugin).
	 *
	 * @param   integer  $rateId  Rate id.
	 *
	 * @since   2.2
	 * @return   status.
	 */
	public function getShipMethRateDetail($rateId)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("id AS rateId,methodId,zone_id,rangeFrom,rangeTo")
		->from('#__kart_zoneShipMethodRates')
		->where('id=' . $rateId);
		$db->setQuery((string) $query);
		$rate = $db->loadAssoc();

		if (!empty($rate))
		{
			$query = $db->getQuery(true);
			$query->select(" id AS rateCurrId,rateId,shipCost,handleCost,currency")
			->from('#__kart_zoneShipMethodRateCurr')
			->where('rateId=' . $rate['rateId']);
			$db->setQuery((string) $query);
			$rate['rateCurrDetails'] = $db->loadAssocList();
		}

		return $rate;
	}

	/**
	 * Method to get ship profiles method(s) detail according to shipping profile id or shipping method id.
	 *
	 * @param   integer  $shipprofile_id  ship profile id id.
	 * @param   integer  $methodId        Tax rule id.
	 *
	 * @since   2.2
	 * @return   object list.
	 */
	public function getShipMethods($shipprofile_id, $methodId)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Here sm for  tax rule
		$query->select('sm.id,sm.shipprofile_id,sm.client,sm.methodId,e.extension_id, e.name as plugName');
		$query->from('#__kart_shipProfileMethods AS sm');
		$query->join('LEFT', '#__extensions AS e ON sm.client = e.element');
		$query->order('sm.id ASC');

		if (!empty($shipprofile_id))
		{
			$query->where('sm.shipprofile_id=' . $shipprofile_id);
		}

		if (!empty($methodId))
		{
			$query->where('sm.id=' . $methodId);
		}

		$query->order('sm.ordering');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to give shipping pluign's method select list.
	 *
	 * @param   string   $extension_id  extentin id from #_extention table .
	 * @param   integer  $store_id      store_id.
	 * @param   string   $default       default value to be selected .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function qtcLoadShipPlgMethods($extension_id, $store_id, $default = '')
	{
		$methList = array();
		$response = array();
		$response['error'] = 0;

		if (!empty($extension_id))
		{
			$qtcshiphelper = new qtcshiphelper;
			$plugName = $qtcshiphelper->getPluginDetail($extension_id);

			// GET PLUGIN LIST
			$import = JPluginHelper::importPlugin('tjshipping', $plugName);
			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger('TjShip_getAvailableShipMethods', array($store_id));

			if (!empty($result[0]))
			{
				$methList = $result[0];
			}
		}

		$plugin_options[] = JHtml::_('select.option', '', JText::_("COM_QUICK2CART_SHIPPLUGIN_SELECT_SHIP_METH"));

		foreach ($methList as $item)
		{
			$plugin_options[] = JHtml::_('select.option', $item['methodId'], $item['name']);
		}

		$attr = 'class=""  aria-invalid="false" size="1"  autocomplete="off" ';

		/* $response['shipMethList'] = JHtml::_(
		 * 'select.genericlist', 'qtc_shipMethod', $plugin_options, $attr, 'value', 'text', $default, 'qtc_shipMethod'
		 * );*/
		$response['shipMethList'] = JHtml::_('select.genericlist', $plugin_options, $attr, 'qtc_shipMethod', 'value', 'text', $default, 'qtc_shipMethod');

		return $response;
	}

	/**
	 * Method to give shipping shipping profile select box.
	 *
	 * @param   string  $store_id        store for which shipping profile have to select .
	 * @param   string  $defaultProfile  default value to be selected .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function qtcLoadShipProfileSelectList($store_id, $defaultProfile = '')
	{
		$shipProfileList = array();

		// Get option List
		$option[] = JHtml::_('select.option', '', JText::_("COM_QUICK2CART_SEL_SHIP_PROFILE"));

		if (!empty($store_id))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('id,name, store_id');
			$query->from('#__kart_shipprofile AS sp');
			$query->where('sp.store_id=' . $store_id);
			$query->where('sp.state=1');
			$db->setQuery($query);
			$shipProfileList = $db->loadAssocList();

			foreach ($shipProfileList as $item)
			{
				$option[] = JHtml::_('select.option', $item['id'], $item['name']);
			}
		}

		$attr = 'class="" data-chosen="qtc" aria-invalid="false" size="1"  autocomplete="off" ';

		return JHtml::_('select.genericlist', $option, 'qtc_shipProfile', $attr, 'value', 'text', $defaultProfile, 'qtc_shipProfileSelList');
	}

	/**
	 * GetShipProfileDetail
	 *
	 * @param   integer  $shipProfilId  shipProfilId .
	 *
	 * @since   2.2
	 * @return   array of shipping profile details
	 */
	public function getShipProfileDetail($shipProfilId)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__kart_shipprofile AS sp');
		$query->where('sp.id=' . $shipProfilId);
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Method to give available shipping mehtods list and its detail for cart items.
	 *
	 * @param   object  $shipping_details  Post object .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getCartItemsShiphDetail($shipping_details)
	{
		$productHelper = new productHelper;
		$itemWiseShipDetail = array();
		$shipMethodHtml = '';

		$address = new stdClass;
		$address->billing_address = $shipping_details->bill;
		$address->shipping_address = $shipping_details->ship;
		$address->ship_chk = 0;
		$qtcshiphelper = new qtcshiphelper;
		$Quick2cartModelcart = new Quick2cartModelcart;
		$cartitems	=	$Quick2cartModelcart->getCartitems();

		if (!empty($cartitems))
		{
			foreach ($cartitems as $citem)
			{
				//  Get item's shipping profile id
				$profieId = $this->getItemsShipProfileId($citem['item_id']);

				if (!empty($profieId))
				{
					// Get shipping methods list.
					$shipMeths = $this->getShipProfileMethods($profieId);

					if (!empty($shipMeths))
					{
						$shipDetail = array();
						$shipMethsDetail = array();
						$shipDetail['item_id'] = $citem['item_id'];

						// Add current cart item detail
						$shipDetail['itemDetail'] = $citem;

						foreach ($shipMeths as $meth)
						{
							$methodId = $meth['methodId'];

							// $shipDetail['shippingMeths'] = $qtcshiphelper->getItemsShipMethods($citem['item_id'], $bill, $ship, $citem, $shipMeths);
							$shipDetail['shippingMeths'][$methodId] = $qtcshiphelper->getItemsShipMethods($citem['item_id'], $address, $citem, $meth);
						}

						$itemWiseShipDetail[] = $shipDetail;
					}
				}
			}
		}

		return $itemWiseShipDetail;
	}

	/**
	 * Method to give available shipping mehtods item.
	 *
	 * @param   object  $itemWiseShipDetail  item_id shipping details .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getShipMethodHtml($itemWiseShipDetail)
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$path = $comquick2cartHelper->getViewpath('cartcheckout', 'default_shipmethods');

		ob_start();
			include $path;
			$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Method to give profiles's shipping methods.
	 *
	 * @param   integer  $item_id         item_id of product .
	 * @param   Array    $address         address object
	 * @param   Array    $cartItemDetail  Single cart item Detail .
	 * @param   Array    $shipMethod      Shipping method detail eg methodId,client, .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getItemsShipMethods($item_id, $address, $cartItemDetail, $shipMethod)
	{
		$shipMethsDetail = array();
		$profieId = $this->getItemsShipProfileId($item_id);
		$productHelper = new productHelper;

		// $address->billing_address = $post->get('bill', array(), 'ARRAY');
		// $address->shipping_address

		$vars = new stdClass;
		$vars->productDetail = $productHelper->getProductsShipRelFields($item_id);
		$vars->billing_address = $address->billing_address;
		$vars->shipping_address = $address->shipping_address;
		$vars->ship_chk = $address->ship_chk;
		$vars->shipMethId = $shipMethod['methodId'];
		$vars->cartItemDetail = $cartItemDetail;

		if (!empty($shipMethod['client']))
		{
			// Call specific plugin trigger
			JPluginHelper::importPlugin('tjshipping', $shipMethod['client']);
			$dispatcher = JDispatcher::getInstance();
			$plgRes = $dispatcher->trigger('TjShip_getShipMethodChargeDetail', array($vars));
			$plgActionRes = array();

			if (!empty($plgRes))
			{
				$plgActionRes = $plgRes[0];

				if (!empty($plgActionRes))
				{
					$plgActionRes['client'] = $shipMethod['client'];
				}

				$shipMethsDetail = $plgActionRes;
			}
		}

		return $shipMethsDetail;
	}

	/**
	 * Method to give item's shipping profile id item.
	 *
	 * @param   INTEGER  $item_id  item_id of product .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getItemsShipProfileId($item_id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('i.shipProfileId');
		$query->from('#__kart_items AS i');
		$query->where('i.item_id=' . $item_id);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to give profiles's shipping methods.
	 *
	 * @param   integer  $profieId  item_id of product .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getShipProfileMethods($profieId)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('sm.*');
		$query->from('#__kart_shipProfileMethods AS sm');
		$query->where('sm.shipprofile_id=' . $profieId);
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * Method to give you weight detial from weight class id.
	 *
	 * @param   INTEGER  $wtUnite  from weight class id .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getWeightDetailFromUnite($wtUnite)
	{
		if ($wtUnite)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('w.id,w.title,w.unit,w.value,w.state');
			$query->from('#__kart_weights AS w');
			$query->where("w.unit='" . $wtUnite . "'");
			$db->setQuery($query);

			return $db->loadAssoc();
		}
	}

	/**
	 * Method to give you weight detial from weight class id.
	 *
	 * @param   INTEGER  $wtClasId  from weight class id .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getWeightDetail($wtClasId)
	{
		if ($wtClasId)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('w.id,w.title,w.unit,w.value,w.state');
			$query->from('#__kart_weights AS w');
			$query->where('w.id=' . $wtClasId);
			$db->setQuery($query);

			return $db->loadAssoc();
		}
	}

	/**
	 * Method to convert weight from one weight class to another.
	 *
	 * @param   INTEGER  $value       value of weight to be converted .
	 * @param   INTEGER  $fromClasId  from weight class id .
	 * @param   INTEGER  $toClassId   to weight class id .
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function convertWeight($value, $fromClasId, $toClassId)
	{
		// Check for valid class ids.
		if (empty($fromClasId) || empty($toClassId))
		{
			return false;
		}

		// If same weight class
		if ((int) $fromClasId == (int) $toClassId)
		{
			return $value;
		}

		// Get weight details.
		$fromWtDetail = $this->getWeightDetail($fromClasId);
		$toWtDetail = $this->getWeightDetail($toClassId);

		return $value * ($toWtDetail['value'] / $fromWtDetail['value']);
	}

	/**
	 * Method cCheck whether ShipProfile is allowed to delete or not.  If not the enqueue error message accordingly..
	 *
	 * @param   string  $id  ShipProfile id .
	 *
	 * @since   2.2
	 * @return   boolean true or false.
	 */
	public function isAllowedToDelShipProfile($id)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Check in tax related table
		$query->select('count(*)');
		$query->from('#__kart_items AS z');
		$query->where('z.shipProfileId=' . $id);

		try
		{
			$db->setQuery($query);
			$count = $db->loadResult();

			if (!empty($count))
			{
				$errMsg = JText::sprintf('COM_QUICK2CART_SHIPPROFILE_DEL_FOUND_PRODUCT', $id);
				$app->enqueueMessage($errMsg, 'error');

				return false;
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');

			return false;
		}

		$query = $db->getQuery(true);

		//  Delete the shippgin and shipping methods mappting
		$query->delete('#__kart_shipProfileMethods');
		$query->where('shipprofile_id=' . $id);

		try
		{
			$db->setQuery($query);
			$status = $db->execute();

			if (empty($status))
			{
				$errMsg = JText::sprintf('COM_QUICK2CART_UNABLE_TO_DEL_SHIPPING_PROFILE_METH', $id);
				$app->enqueueMessage($errMsg, 'error');

				return false;
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}
}
