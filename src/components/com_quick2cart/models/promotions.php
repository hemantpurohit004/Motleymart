<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Quick2cart records.
 *
 * @since  1.6
 */
class Quick2cartModelPromotions extends JModelList
{
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.`id`',
				'store_id', 'a.`store_id`',
				'state', 'a.`state`',
				'name', 'a.`name`',
				'description', 'a.`description`',
				'from_date', 'a.`from_date`',
				'exp_date', 'a.`exp_date`',
				'code', 'a.`code`',
				'value', 'a.`value`',
				'val_type', 'a.`val_type`',
				'max_use', 'a.`max_use`',
				'max_per_user', 'a.`max_per_user`',
				'max_discounts', 'a.`max_discounts`',
				'extra_params', 'a.`extra_params`',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$store_filter = $app->getUserStateFromRequest($this->context . '.filter.store', 'filter_store', '', 'string');
		$this->setState('filter.store', $state);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_quick2cart');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);

		$query->from('`#__kart_promotions` AS a');

		// Only promotions of stores owned by users should be visible
		$user = JFactory::getUser();
		$userId = $user->id;
		$this->comquick2cartHelper = new comquick2cartHelper;
		$storeHelper = $this->comquick2cartHelper->loadqtcClass(JPATH_SITE . "/components/com_quick2cart/helpers/storeHelper.php", "storeHelper");
		$userStores = (array) $storeHelper->getUserStore($userId);

		if (!empty($userStores))
		{
			$storeIds = array();

			foreach ($userStores as $store)
			{
				$storeIds[] = $store['id'];
			}

			if (!empty($storeIds))
			{
				$query->where($db->quoteName('store_id') . 'IN (' . implode(',', $storeIds) . ')');
			}
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		$store = $this->getState('filter.store');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . ')');
			}
		}

		if (!empty($store))
		{
			$query->where('a.store_id = ' . $store);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Function to delete promotion related data
	 *
	 * @param   ARRAY  $cid  array of promotion ids
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	public function delete($cid)
	{
		if (!empty($cid))
		{
			foreach ($cid as $id)
			{
				if (!empty($id))
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__kart_promotions_rules'));
					$query->where($db->quoteName('promotion_id') . " = " . $id);
					$db->setQuery($query);
					$db->execute();

					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__kart_promotion_discount'));
					$query->where($db->quoteName('promotion_id') . " = " . $id);
					$db->setQuery($query);
					$db->execute();
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}
}
