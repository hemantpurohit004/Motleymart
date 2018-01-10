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

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Quick2cart records.
 *
 * @since  2.2
 **/
class Quick2cartModelZones extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'zone_id', 'a.id',
				'name', 'a.name',
				'store_id', 'a.store_id',
				'state', 'a.state',
				'ordering', 'a.ordering',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Load the store id.
		$sel_store = $app->getUserStateFromRequest($this->context . '.filter.sel_store', 'filter_store');
		$this->setState('filter.sel_store', $sel_store);

		// Load the filter search.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the filter ppublsihed.
		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_quick2cart');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'desc');
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
	 * @return  string  A store id.
	 *
	 * @since   1.6
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
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'a.*'
			)
		);
		$query->from('`#__kart_zone` AS a');

		$query->select("s.title");
		$query->JOIN("LEFT", '#__kart_store AS s ON s.id = a.store_id');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		$filter_store = $this->getState('filter.sel_store');

		if ($filter_store)
		{
			$query->where('a.store_id = ' . $filter_store);
		}
		else
		{
			// Getting user accessible store ids
			$storeHelper = new storeHelper;

			// Get all stores.
			$user = JFactory::getUser();
			$storeList = $storeHelper->getUserStore($user->id);
			$storeIds = array();

			foreach ($storeList as $store)
			{
				$storeIds[] = $store['id'];
			}

			$accessibleStoreIds = '';

			// Make string
			if (!empty($storeIds))
			{
				$accessibleStoreIds = implode(',', $storeIds);
				$query->where('(a.store_id IN (' . $accessibleStoreIds . '))');
			}
			else
			{
				$query->where('a.store_id = -1');
			}
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.name LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get a list of zones.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * This function manage items published or unpublished state
	 *
	 * @param   array  $items  delelte taxrate ids.
	 * @param   int    $state  1 for publish and 0 for unpublish.
	 *
	 * @since   2.2
	 * @return  void
	 */
	public function setItemState($items, $state)
	{
		$db = $this->getDbo();

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$db = JFactory::getDBO();
				$query = "UPDATE #__kart_zone SET state=" . $state . " WHERE id=" . $id;
				$db->setQuery($query);

				if (!$db->execute())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   array  $items  An array of primary key value to delete.
	 *
	 * @return  int  Returns count of success
	 */
	public function delete($items)
	{
		$user = JFactory::getUser();
		$db   = JFactory::getDBO();
		$ids  = '';
		$app  = JFactory::getApplication();
		$ids  = (count($items) > 1) ? implode(',', $items) : $items[0];

		$path = JPATH_SITE . '/components/com_quick2cart/helpers/zoneHelper.php';

		if (!class_exists('zoneHelper'))
		{
			JLoader::register('zoneHelper', $path);
			JLoader::load('zoneHelper');
		}

		$zoneHelper = new zoneHelper;

		if (is_array($items))
		{
			$successCount = 0;

			foreach ($items as $id)
			{
				// Check whether zone is allowed to delete or not.  If not the enqueue error message accordingly.
				$count_id = $zoneHelper->isAllowedToDelZone($id);

				if ($count_id === true)
				{
					try
					{
						$query = $db->getQuery(true)
							->delete('#__kart_zone')
							->where('id =' . $id);
						$db->setQuery($query);

						if (!$db->execute())
						{
							$this->setError($this->_db->getErrorMsg());

							return 0;
						}
					}
					catch (RuntimeException $e)
					{
						$this->setError($e->getMessage());
						throw new Exception($this->_db->getErrorMsg());

						return 0;
					}

					// For enqueue success msg along with error msg.
					$successCount += 1;
				}
			}

			return $successCount;
		}
	}
}
