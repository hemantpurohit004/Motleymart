<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.modelform');

/**
 * Methods supporting a list of all products.
 *
 * @package  Quick2Cart
 *
 * @since    1.0
 */
class Quick2cartModelProducts extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.2
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'a.item_id',
				'name',
				'a.name',
				'state',
				'a.state',
				'featured',
				'a.featured',
				'parent',
				'a.parent',
				'category',
				'a.category',
				'store_id',
				'a.store_id',
				'cdate',
				'a.cdate',
				'item_id',
				'a.item_id',
				'published',
				'a.state',
				'store',
				'a.store_id',
				'client',
				'a.parent',
				'ordering',
				'a.ordering'
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

		// List state information.
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);

		if ($limit == 0)
		{
			$this->setState('list.start', 0);
		}
		else
		{
			$this->setState('list.start', $limitstart);
		}

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . 'filter_order', 'filter_order', "a.item_id");

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.ordering';
		}

		$this->setState('list.ordering', $orderCol);

		// Set ordering direction.
		$listOrder = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir', 'DESC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'DESC';
		}

		$this->setState('list.direction', $listOrder);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);

		// Filter client.
		$client = $app->getUserStateFromRequest($this->context . '.filter.client', 'filter_client', '', 'string');
		$this->setState('filter.client', $client);

		// Filter category.
		$category = $app->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '', 'string');
		$this->setState('filter.category', $category);

		// Filter store.
		$store = $app->getUserStateFromRequest($this->context . '.filter.store', 'filter_store', '', 'string');
		$this->setState('filter.store', $store);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_quick2cart');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.ordering', 'DESC');
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
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'));

		$query->from('`#__kart_items` AS a');

		$query->where('a.display_in_product_catlog = 1');

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.item_id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . ' )');
			}
		}

		// Filter by published state.
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by category.
		$filter_client = $this->state->get("filter.client");

		if ($filter_client)
		{
			$query->where("a.parent = '" . $db->escape($filter_client) . "'");
		}

		// Filter by category.
		$filter_category = $this->state->get("filter.category");

		if ($filter_category)
		{
			$query->where("a.category = '" . $db->escape($filter_category) . "'");
		}

		// Filter by store.
		$filter_store = $this->state->get("filter.store");

		if ($filter_store)
		{
			$query->where("a.store_id = '" . $db->escape($filter_store) . "'");
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
	 * Method to get a list of products.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$comquick2cartHelper             = new comquick2cartHelper;
		$quick2cartBackendProductsHelper = new quick2cartBackendProductsHelper;
		$store_details = $comquick2cartHelper->getAllStoreDetails();

		foreach ($items as $item)
		{
			// Get product category
			$catname        = $comquick2cartHelper->getCatName($item->category);
			$item->category = !empty($catname) ? $catname : $item->category;

			// Get store name
			$item->store_name = '';

			if (!empty($store_details[$item->store_id]))
			{
				$item->store_name = $store_details[$item->store_id]['title'];
			}

			// Get store owner
			$item->store_owner = '';

			if (!empty($store_details[$item->store_id]))
			{
				$item->store_owner = $store_details[$item->store_id]['firstname'];
			}

			$item->edit_link = $quick2cartBackendProductsHelper->getProductLink($item->item_id, 'editLink');
			$item->parent    = $quick2cartBackendProductsHelper->getProductParentName($item->item_id);
		}

		return $items;
	}

	/**
	 * Method to edit list
	 *
	 * @param   string  $zoneid  An optional ordering field.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function Editlist($zoneid)
	{
		unset($this->_data);
		$query       = "SELECT * from #__kart_coupon where id=$zoneid";
		$this->_data = $this->_getList($query);

		return $this->_data;
	}

	/**
	 * Method to toggle the featured setting of products.
	 *
	 * @param   Array    $pks    PKS
	 * @param   Integer  $value  Value
	 *
	 * @return  boolean  True on success.
	 */
	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->update($db->quoteName('#__kart_items'))
						->set('featured = ' . (int) $value)
						->where('item_id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to get store name
	 *
	 * @param   string  $store_id  An optional store_id
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function getStoreNmae($store_id)
	{
		if (!empty($store_id))
		{
			$db  = JFactory::getDBO();
			$qry = "SELECT `title` FROM #__kart_store WHERE id=" . $store_id;
			$db->setQuery($qry);

			return $exists = $db->loadResult();
		}
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__kart_items');
				$max = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published, 2=archived, -2=trashed]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function publish ($pks = null, $state = 1, $userId = 0)
	{
		$table = $this->getTable();
		$table->publish($pks, $state);
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/delete
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function delete ($pk = null)
	{
		$table = $this->getTable();
		$table->delete($pk);
	}
}
