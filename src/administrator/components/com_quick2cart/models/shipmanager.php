<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

/**
 * Shipmanager Model.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelShipmanager extends JModelLegacy
{
	/**
	 * Method getCountry.
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function getCountry()
	{
		$db = JFactory::getDBO();

		// Remove prefix to #__
		$query = "select country from #__kart_country";
		$db->setQuery($query);
		$rows = $db->loadColumn();
		/*$country=array();
		print "<pre>";	print_r($rows); die("helper die ");
		foreach($rows as $row)
		{
		array_push($country,$row->country );

		}		*/
		return $rows;
	}

	/**
	 * Method getStatelist.
	 *
	 * @param   Integer  $country  country
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function getStatelist($country)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT r.region FROM #__kart_region AS r LEFT JOIN #__kart_country as c
		ON r.country_code = c.country_code where c.country = \"" . $country . "\"";
		$db->setQuery($query);
		/*$rows = $db->loadAssocList();*/
		$rows = $db->loadColumn();
		/*$assoc = array();
		foreach($rows as $key => $row)
		{
		$assoc[$key]=$row->region;

		}
		*/
		return $rows;
	}

	/**
	 * Method getCity.
	 *
	 * @param   Integer  $country  country
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function getCity($country)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT r.region FROM #__kart_region AS r LEFT JOIN #__kart_country as c
		ON r.country_code = c.country_code where c.country = \"" . $country . "\"";
		$query = "SELECT r.city FROM  `#__kart_city` AS r
		LEFT JOIN `#__kart_country` AS c ON r.country_code = c.country_code
		WHERE c.country =\"" . $country . "\"";
		$db->setQuery($query);
		/*$rows = $db->loadAssocList();*/
		$rows = $db->loadColumn();

		return $rows;
	}

	/**
	 * Method storeShipData.
	 *
	 * @param   Array  $data  Data
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function storeShipData($data)
	{
		$params      = JComponentHelper::getParams('com_quick2cart');
		$multi_curr  = $params->get('addcurrency');
		$multi_currs = explode(",", $multi_curr);
		$keytype     = "";

		$ship_unit = isset($data['geo_type']) ? $data['geo_type'] : "everywhere";

		switch ($ship_unit)
		{
			case "everywhere":
				$indexname = $keytype = "country";
				break;
			case "byregion":
				$keytype   = "region";
				$indexname = "region";
				break;
			case "bycity":
				$keytype = $indexname = "city";
				break;
			case "qtc_forallcountry":
				$keytype   = "qtc_forallcountry";
				$indexname = "country";
				break;
		}

		if (!empty($data['geo'][$indexname]))
		{
			$add = 1;

			foreach ($multi_currs as $cur)
			{
				$key = "";
				$key = $keytype . '_' . $cur;

				if (empty($data[$key]))
				{
					// DONT ADD when price is not entered
					$add = -1;
				}
				else
				{
					$add = 1;
					break;
				}
			}

			if ($add == 1)
			{
				$countylist = explode('|', $data['geo'][$indexname]);
				$countylist = array_filter($countylist, "trim");

				foreach ($countylist as $value)
				{
					$shipid = 0;
					$shipid = $this->addShipManagerEntry($keytype, $value);

					$this->shipManagerCurrency($keytype, $shipid, $data, $multi_currs);
				}
			}
		}
	}

	/**
	 * Method addShipManagerEntry.
	 *
	 * @param   Integer  $key    key contain one of state,county,city
	 * @param   Array    $value  contain key's value
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function addShipManagerEntry($key, $value)
	{
		$key   = ($key == "qtc_forallcountry") ? "country" : $key;
		$db    = JFactory::getDBO();
		$query = "select id from `#__kart_ship_manager` where `value`='$value' AND `key`='$key' ";
		$db->setQuery($query);
		$id = $db->loadResult();

		if ($id)
		{
			return $id;
		}
		else
		{
			$row        = new stdClass;
			$row->key   = $key;
			$row->value = $value;

			if (!$db->insertObject('#__kart_ship_manager', $row, 'cart_id'))
			{
				echo $this->_db->stderr();

				return false;
			}

			return $db->insertid();
		}
	}

	/**
	 * Method shipManagerCurrency.
	 *
	 * @param   String   $name       name
	 * @param   Integer  $shipid     Ship Id
	 * @param   Array    $data       Data
	 * @param   Integer  $multicurr  Multicurrency
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function shipManagerCurrency($name, $shipid, $data, $multicurr)
	{
		$db = JFactory::getDBO();

		foreach ($multicurr as $curr)
		{
			$key = "";
			$key = $name . "_" . $curr;

			if (!empty($data[$key]))
			{
				$present = $this->getshipManagerCurrencyId($shipid, $curr);

				// Present then update
				if (!empty($present))
				{
					$row                  = new stdClass;
					$row->id              = $present;
					$row->ship_manager_id = $shipid;
					$row->shipvalue       = $data[$key];
					$row->currency        = $curr;

					if (!$db->updateObject('#__kart_ship_manager_currency', $row, 'id'))
					{
						echo $this->_db->stderr();

						return false;
					}
				}
				else
				{
					// Not present then add
					$row                  = new stdClass;
					$row->ship_manager_id = $shipid;
					$row->shipvalue       = $data[$key];
					$row->currency        = $curr;

					if (!$db->insertObject('#__kart_ship_manager_currency', $row, 'id'))
					{
						echo $this->_db->stderr();

						return false;
					}
				}
			}
		}
	}

	/**
	 * Method getshipManagerCurrencyId.
	 *
	 * @param   Integer  $shipid  Ship Id
	 * @param   Integer  $curr    Currency
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function getshipManagerCurrencyId($shipid, $curr)
	{
		$db    = JFactory::getDBO();
		$query = "select id from `#__kart_ship_manager_currency` where `ship_manager_id`='$shipid' AND `currency`='$curr' ";
		$db->setQuery($query);

		return $id = $db->loadResult();
	}

	/**
	 * Method getshippinglist.
	 *
	 * @return	id
	 *
	 * @since	1.6
	 */
	public function getshippinglist()
	{
		$db    = JFactory::getDBO();
		$query = "select * from #__kart_ship_manager ";
		$db->setQuery($query);
		$id = $db->loadobjectlist();

		foreach ($id as $key)
		{
			$query = "SELECT CONCAT(`shipvalue`,`currency`) as shipprice FROM `#__kart_ship_manager_currency` WHERE ship_manager_id=$key->id";
			$db->setQuery($query);
			$sid              = $db->loadobjectlist();
			$key->shipcharges = $sid;
		}

		return $id;
	}

	/**
	 * Method deletshiplist.
	 *
	 * @param   Integer  $shpid  Shipid
	 *
	 * @return	boolean
	 *
	 * @since	1.6
	 */
	public function deletshiplist($shpid)
	{
		$odid_str = implode(',', $shpid);
		$query    = "DELETE FROM #__kart_ship_manager_currency where ship_manager_id IN (" . $odid_str . ") ";
		$this->_db->setQuery($query);
		$a = $this->_db->execute();

		$query = "DELETE FROM #__kart_ship_manager where id IN (" . $odid_str . ")";
		$this->_db->setQuery($query);
		$a = $this->_db->execute();

		return true;
	}
}
