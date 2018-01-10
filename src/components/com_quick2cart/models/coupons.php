<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of coupons records.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelCoupons extends JModelList
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
				'id', 'a.id',
				'name', 'a.name',
				'published', 'a.published',
				'code', 'a.code',
				'value', 'a.value',
				'val_type', 'a.val_type',
				'max_use', 'a.max_use',
				'from_date', 'a.from_date',
				'exp_date', 'a.exp_date',
				'store_id', 'a.store_id'
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
		$app = JFactory::getApplication('administrator');

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.id';
		}

		$this->setState('list.ordering', $orderCol);

		// Set ordering direction.
		$listOrder = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		// Load the filter state.
		$published = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);

		// Load the filter search
		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Filter store.
		$store = $app->getUserStateFromRequest($this->context . '.filter.store', 'current_store', '', 'string');
		$this->setState('filter.store', $store);

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
		$user  = JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'a.*'
			)
		);

		$query->from('`#__kart_coupon` AS a');

		// Filter by published state.
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
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
				$query->where('( a.name LIKE ' . $search .
					'  OR  a.code LIKE ' . $search .
					'  OR  a.value LIKE ' . $search . ' )'
				);
			}
		}

		// Filter by store.
		$filter_store = $this->state->get("filter.store");

		// Get all stores by logged in user
		$comquick2cartHelper = new comquick2cartHelper;
		$my_stores = $comquick2cartHelper->getStoreIds($user->id);

		if (count($my_stores))
		{
			// Get all store ids
			foreach ($my_stores as $key => $value)
			{
				$stores[] = $value["store_id"];
			}

			// If store filter is selected, check it in my stores array
			if ($filter_store)
			{
				if (in_array($filter_store, $stores))
				{
					$query->where("a.store_id = '" . $db->escape($filter_store) . "'");
				}
			}
			// If selected store filter is not found in my stores array, show coupons from all stores for logged in user
			else
			{
				$stores = implode(',', $stores);

				if (!empty($stores))
				{
					$query->where(" a.store_id IN (" . $stores . ")");
				}
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
	 * Method to get a list of coupons.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$db = $this->getDbo();

		foreach ($items as $item)
		{
			$item->item_id_name = '';
			$item->user_id_name = '';

			if ($item->item_id)
			{
				$item->item_id_name = $this->getautoname_DB($item->item_id, 'kart_items', 'item_id', 'name');
				$item->item_id_name = substr($item->item_id_name, 1, -1);
				$item->item_id_name = str_replace('||', ', ', $item->item_id_name);
			}

			if ($item->user_id)
			{
				$item->user_id_name = $this->getautoname_DB($item->user_id, 'users', 'id', 'name', 'id.block <> 1');
				$item->user_id_name = substr($item->user_id_name, 1, -1);
				$item->user_id_name = str_replace('||', ', ', $item->user_id_name);
			}
		}

		return $items;
	}

	/**
	 * Generates a list of pipe separated tokens for autosuggest
	 *
	 * @param   string  $autodata       Autosuggest search string
	 * @param   string  $element_table  Database table name
	 * @param   string  $element        Database coulmn name
	 * @param   string  $element_value  Database coulmn name
	 * @param   string  $extras         Extra
	 *
	 * @return  string
	 */
	public function getautoname_DB($autodata, $element_table, $element, $element_value, $extras = '')
	{
		$autodata = str_replace("||", "','", $autodata);
		$autodata = str_replace('|', '', $autodata);

		$query_table[] = '#__' . $element_table . ' as ' . $element;
		$element_table_name = $element;

		if (trim($autodata))
		{
			$query_condi[] = $element . "." . $element . " IN ('" . trim($autodata) . "')";
		}

		if (trim($extras))
		{
			$query_condi[] = $extras;
		}

		$tables = (count($query_table) ? ' FROM ' . implode("\n LEFT JOIN ", $query_table) : '');

		if ($tables)
		{
			$where = (count($query_condi) ? ' WHERE ' . implode("\n AND ", $query_condi) : '');

			if ($where)
			{
				$db    = JFactory::getDBO();
				$query = "SELECT " . $element_value . "
				\n " . $tables . " \n " . $where;

				$this->_db->setQuery($query);
				$loca_list = $this->_db->loadColumn();

				return ((!empty($loca_list)) ? "|" . implode('||', $loca_list) . "|" : '');
			}
		}
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   array    $items  The array of record ids.
	 * @param   integer  $state  The value of the property to set or null.
	 *
	 * @return  integer  The number of records updated.
	 *
	 * @since   2.2
	 */
	public function setItemState($items, $state)
	{
		$db = $this->getDbo();
		$count = 0;

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$query = $db->getQuery(true);

				// Update the reset flag
				$query->update($db->quoteName('#__kart_coupon'))
					->set($db->quoteName('published') . ' = ' . $state)
					->where($db->quoteName('id') . ' = ' . $id);

				$db->setQuery($query);

				try
				{
					$db->execute();
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
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   array  $items  An array of primary key value to delete.
	 *
	 * @return  int  Returns count of success
	 */
	public function delete($items)
	{
		$db = $this->getDbo();
		$count = 0;

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$query = $db->getQuery(true);

				// @TODO use query builder
				$query = 'DELETE
				 FROM `#__kart_coupon`
				 WHERE id = ' . $id;

				$db->setQuery($query);

				try
				{
					$db->execute();
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
}
