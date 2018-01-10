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
class Quick2cartModelTaxrates extends JModelList
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
				'taxrate_id', 'a.taxrate_id',
				'name', 'a.name',
				'percentage', 'a.percentage',
				'zone_id', 'a.zone_id',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',

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
		$app = JFactory::getApplication('site');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_quick2cart');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.name', 'asc');
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
		$comquick2cartHelper = new comquick2cartHelper;

		// Getting user accessible store ids
		$storeList = $comquick2cartHelper->getStoreIds();

		$storeIds = array();

		foreach ($storeList as $store)
		{
			$storeIds[] = $store['store_id'];
		}

		$accessibleStoreIds = '';

		if (!empty($storeIds))
		{
			$accessibleStoreIds = implode(',', $storeIds);
		}

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
				$this->getState(
						'list.select', 'a.*'
				)
		);
		$query->from('`#__kart_taxrates` AS a');

		// Join over the user field 'created_by'
		// $query->select('created_by.name AS created_by');
		$query->select('z.name as zonename', 'z.store_id');
		$query->select('s.title');

		// $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		$query->join('LEFT', '#__kart_zone AS z ON z.id = a.zone_id');
		$query->join('INNER', '#__kart_store AS s ON s.id = z.store_id');
		$query->where('(z.store_id IN (' . $accessibleStoreIds . '))');

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
				$query->where('( a.name LIKE ' . $search . '  OR  a.zone_id LIKE ' . $search . ')');
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
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   array  $items  An array of primary key value to delete.
	 *
	 * @return  int  Returns count of success
	 */
	public function delete($items)
	{
		$user         = JFactory::getUser();
		$db           = JFactory::getDBO();
		$query        = $db->getQuery(true);
		$successCount = 0;
		$app          = JFactory::getApplication();

		if (is_array($items))
		{
			$taxHelper = new taxHelper;

			foreach ($items as $id)
			{
				// Check whether zone is allowed to delete or not.  If not the enqueue error message accordingly.
				$count_id = $taxHelper->isAllowedToDelTaxrate($id);

				if ($count_id === true)
				{
					try
					{
						$query = $db->getQuery(true)
							->delete('#__kart_taxrates')
							->where('id =' . $id);
						$db->setQuery($query);

						if (!$db->execute())
						{
							$this->setError($this->_db->getErrorMsg());

							return $successCount;
						}
					}
					catch (RuntimeException $e)
					{
						$this->setError($e->getMessage());
						throw new Exception($this->_db->getErrorMsg());

						return $successCount;
					}

					$successCount++;
				}
			}
		}

		return $successCount;
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
		$db = JFactory::getDBO();

		if (is_array($items))
		{
			$taxreate_ids = implode(',', $items);
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('state') . ' =' . $state
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . '  IN (' . $taxreate_ids . ')',
			);

			$query->update($db->quoteName('#__kart_taxrates'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();

			if (!$result)
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}
}
