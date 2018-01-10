<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
/**
 * TaxHelper
 *
 * @package     Com_Quick2cart
 * @subpackage  site
 * @since       2.2
 */
class TaxHelper
{
	/**
	 * Method to get the record form.
	 *
	 * @param   string  $taxrule_id  taxrule id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getTaxProfileId($taxrule_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('taxprofile_id')))->from('#__kart_taxrules')->where('taxrule_id=' . $taxrule_id);
		$db->setQuery((string) $query);

		return $db->loadResult();
	}

	/**
	 * Method to get the profile id of product.
	 *
	 * @param   integer  $item_id  item_id id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getProductProfileId($item_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('taxprofile_id')))->from('#__kart_items')->where('item_id=' . $item_id);
		$db->setQuery((string) $query);

		return $db->loadResult();
	}

	/**
	 * Method to get the store list for tax profile having tax rates against it.
	 *
	 * @param   integer  $user_id  user id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getStoreListForTaxprofile($user_id = 0)
	{
		if (empty($user_id))
		{
			$user    = JFactory::getUser();
			$user_id = $user->id;
		}

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('s.id AS store_id,s.title');
		$query->from('#__kart_store AS s');
		$query->join('INNER', '#__kart_zone AS z ON s.id = z.store_id');
		$query->join('INNER', '#__kart_taxrates AS tr ON z.id = tr.zone_id');
		$query->where('s.owner=' . $user_id);
		$query->group('s.id');
		$db->setQuery((string) $query);

		return $db->loadAssocList();
	}

	/**
	 * Method to get the store list for tax profile having tax rates against it.
	 *
	 * @param   string  $profileId  profileId id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getTaxprofileDetail($profileId)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("tp.`name` , tp.id, s.`title` , s.`id` AS store_id");
		$query->from('#__kart_store AS s');
		$query->join('INNER', '#__kart_taxprofiles AS tp ON s.id = tp.store_id');
		$query->where('tp.id=' . $profileId);
		$db->setQuery((string) $query);

		return $db->loadAssoc();
	}

	/**
	 * Method to get the store list for tax profile having tax rates against it.
	 *
	 * @param   string  $user_id  user id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getUsersTaxprofiles($user_id = 0)
	{
		if (empty($user_id))
		{
			$user    = JFactory::getUser();
			$user_id = $user->id;
		}

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("tp.`name` , tp.id, s.`title` , s.`id` AS store_id");
		$query->from('#__kart_store AS s');
		$query->join('INNER', '#__kart_taxprofiles AS tp ON s.id = tp.store_id');
		$query->join('INNER', '#__kart_taxrules AS trule ON trule.taxprofile_id = tp.id');
		$query->where('s.owner=' . $user_id);
		$query->group('tp.id');
		$db->setQuery((string) $query);
		$res = $db->loadAssocList();

		return $db->loadAssocList();
	}

	/**
	 * Method to get the store id from tax profile id.
	 *
	 * @param   integer  $taxPid  taxprofile id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getTaxProfileStoreId($taxPid)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('store_id')));
		$query->from('#__kart_taxprofiles AS p');
		$query->where('p.id=' . $taxPid);
		$db->setQuery((string) $query);

		return $db->loadResult();
	}

	/**
	 * Method to get the store list from tax rule.
	 *
	 * @param   string  $ruleId  taxrule id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getStoreIdFromTaxrule($ruleId)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('store_id')));

		$query->from('#__kart_taxprofiles AS tp');
		$query->join('LEFT', '#__kart_taxrules AS r ON r.taxprofile_id=tp.id');
		$query->where('taxrule_id=' . $ruleId);
		$db->setQuery((string) $query);

		return $db->loadResult();
	}

	/**
	 * Method provides item tax details. pass the product price and the product price and get the tax,
	 * nternally gets the taxprofile id and get the tax
	 *
	 * @param   float    $product_price  product price.
	 * @param   integer  $item_id        item id.
	 * @param   array    $address        Adress detail object
	 * @param   integer  $taxprofile_id  Tax profile id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getItemTax($product_price, $item_id, $address, $taxprofile_id = '')
	{
		$taxHelper         = new taxHelper;
		$amount            = 0;
		$ret['taxdetails'] = array();

		if (empty($taxprofile_id))
		{
			// Get tax profile id
			$taxprofile_id = $taxHelper->getProductProfileId($item_id);
		}

		// Get Application tax rate details
		$ItemTaxes = $taxHelper->getApplicableTaxRates($taxprofile_id, $address);

		// Get tax rate wise commulative total
		$taxRateWiseTotal = $taxHelper->getTaxRateWiseTotal($product_price, $ItemTaxes);

		foreach ($taxRateWiseTotal as $tax_rate)
		{
			$amount += $tax_rate['amount'];
		}

		$ret['item_id']  = $item_id;
		$ret['taxAmount']  = $amount;
		$ret['taxdetails'] = $taxRateWiseTotal;

		return $ret;
	}

	/**
	 * Method provides item wise tax details.
	 *
	 * @param   integer  $taxprofile_id  taxProfile id.
	 * @param   array    $address        Adress detail object
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getApplicableTaxRates($taxprofile_id, $address)
	{
		$tax_rates = array();
		$db        = JFactory::getDBO();

		if (isset($address->shipping_address))
		{
			$tax_query = "SELECT tr2.id AS taxrate_id, tr2.name AS name, tr2.percentage AS rate,tr1.address FROM
			#__kart_taxrules tr1" . " LEFT JOIN #__kart_taxrates tr2 ON ( tr1.taxrate_id = tr2.id ) " .
			" LEFT JOIN #__kart_zonerules zr ON ( tr2.zone_id = zr.zone_id )  " .
			" LEFT JOIN #__kart_zone z ON ( tr2.zone_id = z.id )  " . " WHERE tr1.taxprofile_id = " .
			(int) $taxprofile_id . " AND tr1.address = 'shipping' " . " AND zr.country_id = " . (int) $address->shipping_address['country'] .
			" AND (zr.region_id = 0 OR zr.region_id = " . (int) $address->shipping_address['state'] . ") ORDER BY tr1.ordering ASC";

			$db->setQuery($tax_query);
			$taxrates_items = $db->loadObjectList();

			// Get all taxrates
			if (isset($taxrates_items))
			{
				foreach ($taxrates_items as $trate)
				{
					$tax_rates[] = array(
						'taxrate_id' => $trate->taxrate_id,
						'name' => $trate->name,
						'rate' => $trate->rate,
						'address' => $trate->address
					);
				}
			}
		}

		if (isset($address->billing_address))
		{
			$tax_query = "SELECT tr2.id AS taxrate_id, tr2.name AS name, tr2.percentage AS rate,tr1.address 	FROM  #__kart_taxrules tr1" .
			" LEFT JOIN #__kart_taxrates tr2 ON ( tr1.taxrate_id = tr2.id ) " . " LEFT JOIN #__kart_zonerules zr ON ( tr2.zone_id = zr.zone_id )  " .
			" LEFT JOIN #__kart_zone z ON ( tr2.zone_id = z.id )  " . " WHERE tr1.taxprofile_id = " . (int) $taxprofile_id . " AND tr1.address = 'billing' " .
			" AND zr.country_id = " . (int) $address->billing_address['country'] . " AND (zr.region_id = 0 OR zr.region_id = " .
			(int) $address->billing_address['state'] . ") ORDER BY tr1.ordering ASC";

			$db->setQuery($tax_query);
			$taxrates_items = $db->loadObjectList();

			if (isset($taxrates_items))
			{
				foreach ($taxrates_items as $trate)
				{
					$tax_rates[] = array(
						'taxrate_id' => $trate->taxrate_id,
						'name' => $trate->name,
						'rate' => $trate->rate,
						'address' => $trate->address
					);
				}
			}
		}

		return $tax_rates;
	}

	/**
	 * Method provides item wise tax details.
	 *
	 * @param   float  $item_price  product price.
	 * @param   array  $ItemTaxes   applicable product rates.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getTaxRateWiseTotal($item_price, $ItemTaxes)
	{
		$item_tax_data = array();

		if (!empty($ItemTaxes))
		{
			foreach ($ItemTaxes as $tax_rate)
			{
				if (isset($item_tax_data[$tax_rate['taxrate_id']]))
				{
					$amount = $item_tax_data[$tax_rate['taxrate_id']]['amount'];
				}
				else
				{
					$amount = 0;
				}

				$amount += ($item_price / 100 * $tax_rate['rate']);

				$item_tax_data[$tax_rate['taxrate_id']] = array(
					'taxrate_id' => $tax_rate['taxrate_id'],
					'name' => $tax_rate['name'],
					'rate' => $tax_rate['rate'],
					'amount' => $amount
				);
			}
		}

		return $item_tax_data;
	}

	/**
	 * Method cCheck whether Taxrate is allowed to delete or not.  If not the enqueue error message accordingly..
	 *
	 * @param   string  $id  taxrateid .
	 *
	 * @since   2.2
	 * @return   boolean true or false.
	 */
	public function isAllowedToDelTaxrate($id)
	{
		$app   = JFactory::getApplication();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Check in tax related table
		$query->select('count(*)');
		$query->from('#__kart_taxrules AS z');
		$query->where('z.taxrate_id=' . $id);

		try
		{
			$db->setQuery($query);
			$taxEntry = $db->loadResult();

			if (!empty($taxEntry))
			{
				$errMsg = JText::sprintf('COM_QUICK2CART_TAXRATE_DEL_FOUND_AGAINST_TAXPROFILE', $id);
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
	 * Method cCheck whether TaxProfile is allowed to delete or not.  If not the enqueue error message accordingly..
	 *
	 * @param   string  $id  taxprofileid .
	 *
	 * @since   2.2
	 * @return   boolean true or false.
	 */
	public function isAllowedToDelTaxProfile($id)
	{
		$app   = JFactory::getApplication();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Check in tax related table
		$query->select('count(*)');
		$query->from('#__kart_items AS z');
		$query->where('z.taxprofile_id=' . $id);

		try
		{
			$db->setQuery($query);
			$count = $db->loadResult();

			if (!empty($count))
			{
				$errMsg = JText::sprintf('COM_QUICK2CART_TAXPROFILE_DEL_FOUND_AGAINST_PRODUCT', $id);
				$app->enqueueMessage($errMsg, 'error');

				return false;
			}
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');

			return false;
		}

		// Check in shipping mthods
		try
		{
			// Check in shipping method's rate table
			$query = $db->getQuery(true);
			$query->select('z.id');
			$query->from('#__kart_zoneShipMethods AS z');
			$query->where('z.taxprofileId=' . $id);
			$db->setQuery($query);
			$count = $db->loadResult();

			if (!empty($count))
			{
				$errMsg = JText::sprintf('COM_QUICK2CART_TAXPROFILE_DEL_FOUND_AGAINST_SHIP_METHODS', $id);
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
}
