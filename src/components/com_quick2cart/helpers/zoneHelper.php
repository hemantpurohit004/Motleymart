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
class ZoneHelper
{
	/**
	 * Method to get the record form.
	 *
	 * @param   string  $zone_id  zone id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getZoneStoreId($zone_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('store_id')))->from('#__kart_zone')->where('id=' . $zone_id);
		$db->setQuery((string) $query);

		return $db->loadResult();
	}

	/**
	 * Method to get the zone id form tax rate id.
	 *
	 * @param   string  $taxrate_id  taxrate_id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getZoneFromTaxRateId($taxrate_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('zone_id')))->from('#__kart_taxrates')->where('id=' . $taxrate_id);
		$db->setQuery((string) $query);

		return $db->loadResult();
	}

	/**
	 * Method to get the users zone list ().
	 *
	 * @param   string  $user_id     zone id.
	 * @param   array   $statearray  states to fetch.
	 *
	 * @since   2.2
	 * @return   null array of zone with zone_id and name
	 */
	public function getUserZoneList($user_id = '', $statearray = array(0, 1))
	{
		$storeHelper = new storeHelper;

		// Getting user accessible store ids
		$storeIdList = $storeHelper->getuserStoreList($user_id = '');

		if (!empty($storeIdList))
		{
			$ids       = implode(',', $storeIdList);
			$db        = JFactory::getDBO();
			$statelist = implode(',', $statearray);

			// Build Query
			$query = $db->getQuery(true);
			$query->select("z.id,z.name,s.id AS store_id,s.title AS storeName")
			->from('#__kart_store AS s')->join('INNER', '#__kart_zone AS z ON s.id = z.store_id')
			->where('s.id IN(' . $ids . ')')->where('z.state IN(' . $statelist . ')');

			$db->setQuery((string) $query);

			return $db->loadAssocList();
		}

		return array();
	}

	/**
	 * Method to get the zone list associated to store_id().
	 *
	 * @param   string  $store_id  store_id id.
	 *
	 * @since   2.2
	 * @return   null array of zone with zone_id and name
	 */
	public function getStoreZoneList($store_id)
	{
		$storeHelper = new storeHelper;

		if (!empty($store_id))
		{
			$db = JFactory::getDBO();

			// Build Query
			$query = $db->getQuery(true);
			$query->select("z.id,z.name,s.id AS store_id,s.title AS storeName")
			->from('#__kart_store AS s')
			->join('INNER', '#__kart_zone AS z ON s.id = z.store_id')->where('s.id =' . $store_id);

			$db->setQuery((string) $query);

			return $db->loadAssocList();
		}

		return array();
	}

	/**
	 * Method to get the users zone ids.
	 *
	 * @param   string  $user_id  zone id.
	 *
	 * @since   2.2
	 * @return   null array of zone_id
	 */
	public function getUserZoneIds($user_id = '')
	{
		$storeHelper = new storeHelper;

		// Getting user accessible store ids
		$storeIdList = $storeHelper->getuserStoreList($user_id = '');

		if (!empty($storeIdList))
		{
			$ids = implode(',', $storeIdList);
			$db  = JFactory::getDBO();

			// Build Query
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')))->from('#__kart_zone')->where('store_id IN(' . $ids . ')');
			$db->setQuery((string) $query);

			return $db->loadColumn();
		}

		return array();
	}

	/**
	 * Method to get the users zone list.
	 *
	 * @param   string  $store_id  zonestore_idid.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getUserTaxRateList($store_id)
	{
		$zoneHelper = new zoneHelper;

		$db = JFactory::getDBO();

		// Build Query
		$query = $db->getQuery(true);
		$query->select('a.id,a.name,a.percentage,s.id AS store_id, s.title AS storeName');
		$query->from('#__kart_taxrates AS a');
		$query->join('INNER', '#__kart_zone AS z ON a.zone_id=z.id');
		$query->join('INNER', '#__kart_store AS s ON s.id=z.store_id');
		$query->where('z.store_id=' . $store_id);
		$query->where('a.state = 1 AND z.state = 1');
		$query->order('a.name');
		$db->setQuery($query);

		return $taxrates = $db->loadObjectList();
	}

	/**
	 * Method to get profiles tax rule(s) detail according to the tax profile id and tax rule id.
	 *
	 * @param   string  $taxprofile_id  tax profile id.
	 * @param   string  $taxRule_id     Tax rule id.
	 *
	 * @since   2.2
	 * @return   null object.
	 */
	public function getTaxRules($taxprofile_id = '', $taxRule_id = '')
	{
		$app   = JFactory::getApplication();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Here trule for  tax rule
		$query->select('trule.taxrule_id,trule.taxrate_id,trule.taxprofile_id,b.name,b.percentage, trule.address');
		$query->from('#__kart_taxrules AS trule');
		$query->join('INNER', '#__kart_taxrates AS b ON b.id= trule.taxrate_id');

		if (!empty($taxprofile_id))
		{
			$query->where('trule.taxprofile_id=' . $taxprofile_id);
		}

		if (!empty($taxRule_id))
		{
			$query->where('trule.taxrule_id=' . $taxRule_id);
		}

		$query->order('trule.ordering');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to return zone detail from zone id.
	 *
	 * @param   string  $zone_id  zone id.
	 *
	 * @since   2.2
	 * @return   null object.
	 */
	public function getZoneDetail($zone_id)
	{
		$app   = JFactory::getApplication();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Here trule for  tax rule
		$query->select('z.id,z.name,s.id AS store_id,s.title');
		$query->from('#__kart_zone AS z');
		$query->join('LEFT', '#__kart_store AS s ON z.store_id= s.id');
		$query->where('z.id=' . $zone_id);
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Method cCheck whether zone is allowed to delete or not.  If not the enqueue error message accordingly..
	 *
	 * @param   string  $zone_id  zone id.
	 *
	 * @since   2.2
	 * @return   boolean true or false.
	 */
	public function isAllowedToDelZone($zone_id)
	{
		$app   = JFactory::getApplication();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Check in tax related table
		$query->select('z.id, z.name');
		$query->from('#__kart_taxrates AS z');
		$query->where('z.zone_id	=' . $zone_id);
		$db->setQuery($query);
		$taxEntry = $db->loadAssoc();

		if (!empty($taxEntry))
		{
			$errMsg = JText::sprintf('COM_QUICK2CART_ZONES_DEL_TAXRATE_AGAINST_ZONES', $zone_id);
			$app->enqueueMessage($errMsg, 'error');

			return false;
		}

		// Check in shipping method's rate table
		$query = $db->getQuery(true);
		$query->select('z.id');
		$query->from('#__kart_zoneShipMethodRates AS z');
		$query->where('z.zone_id=' . $zone_id);
		$db->setQuery($query);
		$count = $db->loadResult();

		if (!empty($count))
		{
			$errMsg = JText::sprintf('COM_QUICK2CART_ZONES_DEL_TAXRATE_AGAINST_ZONES', $zone_id);
			$app->enqueueMessage($errMsg, 'error');

			return false;
		}

		// @TODO have to check for shipping related and etc table.

		return true;
	}

	/**
	 * Method  check whether view is accessible or not.
	 *
	 * @param   string  $view     view name.
	 * @param   string  $layout   layout name.
	 * @param   string  $viewTpe  viewTpe.
	 *
	 * @since   2.2
	 * @return   boolean true or false.
	 */
	public function isUserAccessible($view = '', $layout = "default", $viewTpe = "list")
	{
		$comquick2cartHelper = new comquick2cartHelper;

		// Getting user accessible store ids @TO DO use store_authorize FUNCTIPN HERE
		$storeList = $comquick2cartHelper->getStoreIds('', 1);

		if (empty($storeList))
		{
			?>
			<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >
				<div class="well" >
					<div class="alert alert-error alert-danger">
						<span ><?php echo JText::_('COM_QUICK2CART_TAXTRATES_S_STORE_NOT_FOUND');?> </span>
					</div>
				</div>
			</div>
			<?php

			return false;
		}

		return true;
	}

	/**
	 * Method  showUnauthorizedMsg.
	 *
	 * @since   2.2
	 * @return   boolean true or false.
	 */
	public function showUnauthorizedMsg()
	{
		?>
		<div class="<?php	echo Q2C_WRAPPER_CLASS; ?>" >
			<div class="well" >
				<div class="alert alert-error alert-danger">
					<span ><?php echo JText::_('COM_QUICK2CART_TAXTRATES_NOT_AUTHORIZED'); ?> </span>
				</div>
			</div>
		</div>
		<?php
	}
}
