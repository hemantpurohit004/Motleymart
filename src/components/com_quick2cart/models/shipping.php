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

require_once JPATH_SITE . '/components/com_quick2cart/helpers/qtcshiphelper.php';

/**
 * Methods supporting a list of Quick2cart records.
 *
 * @since  2.2
 **/
class Quick2cartModelShipping extends JModelList
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
				'created_by', 'a.created_by'
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
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$qtcshiphelper = new qtcshiphelper;

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$folder = 'tjshipping';
		$folder = strtolower($folder);

		// Select the required fields from the table.
		$query->select('extension_id,name,type,element,folder,client_id,enabled	access');
		$query->from('`#__extensions` AS a');
		$query->where('a.enabled =1');
		$query->where("LOWER(`folder`)='{$folder}'");

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
				$query->where('( a.name LIKE ' . $search . '  OR  a.id LIKE ' . $search . ')');
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

		if (!empty($items))
		{
			foreach ($items as $row)
			{
				if (is_object($row) && !empty($row->element))
				{
					JPluginHelper::importPlugin('tjshipping', $row->element);
					$dispatcher = JDispatcher::getInstance();
					$result = $dispatcher->trigger('_shipGetInfo', array($row));

					if (!empty($result))
					{
						$row = $result[0];
					}
				}
			}
		}

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
	public function setItemState( $items, $state)
	{
		$db = JFactory::getDBO();

		if (is_array($items))
		{
			$taxreate_ids = implode(',', $items);
			$db           = JFactory::getDbo();
			$query        = $db->getQuery(true);

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

	/**
	 * Method To to removed and add in getStoreinfo
	 *
	 * @param   Integer  $store_id  Store ID
	 *
	 * @since   2.2
	 *
	 * @return  void
	 */
	public function getStorePincode($store_id)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('pincode');
		$query->from('#__kart_store');
		$query->where('id = ' . $store_id);
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}
}
