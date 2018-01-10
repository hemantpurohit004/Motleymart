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
class Quick2cartModelTaxprofiles extends JModelList
{
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

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

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
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$taxHelper = new taxHelper;

		// Getting user accessible store ids
		$storeList = $taxHelper->getStoreListForTaxprofile();
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

		$query->select('s.title as storeName');
		$query->from('`#__kart_taxprofiles` AS a');
		$query->JOIN('INNER', ' `#__kart_store` AS s ON s.id=a.store_id');

		if (!empty($accessibleStoreIds))
		{
			$query->where('(a.store_id IN (' . $accessibleStoreIds . '))');
		}
		else
		{
			// If tax rates are not set then Dont fetch data;
			$query->where('a.store_id = -1');
		}

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
				$query->where('( a.name LIKE ' . $search . ')');
			}
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
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$count = 0;

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				try
				{
					$table = $this->getTable();
					$table->delete($id);
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());

					return 0;
				}

				$count++;
			}
		}

		return $count;
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
	public function getTable($type = 'taxprofile', $prefix = 'Quick2cartTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
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
	public function setItemState( $items, $state)
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

			$query->update($db->quoteName('#__kart_taxprofiles'))->set($fields)->where($conditions);
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
