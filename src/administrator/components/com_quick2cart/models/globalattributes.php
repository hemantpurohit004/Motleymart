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
 * @since  2.5
 *
 */
class Quick2cartModelGlobalattributes extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   2.5
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
								'id', 'a.`id`',
				'state', 'a.`state`',
				'attribute_name', 'a.`attribute_name`',

			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   STRING  $ordering   record ordering
	 *
	 * @param   STRING  $direction  record direction
	 *
	 * @return null
	 *
	 * @since  2.5
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

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
	 * @since   2.5
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
	 * @return	JDatabaseQuery
	 *
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
				$this->getState(
						'list.select', 'DISTINCT a.*'
				)
		);
		$query->from('`#__kart_global_attribute` AS a');

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
			$query->where('(a.attribute_name LIKE ' . "'%" . $search . "%' OR a.id LIKE '" . $search . "')");
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
	 * Method to get a records.
	 *
	 * @return  mixed	Object on success, false on failure.
	 *
	 * @since   2.5
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Function to dlete attribute
	 *
	 * @return  null
	 *
	 * @since  2.5
	 */
	public function delete()
	{
		$input = JFactory::getApplication()->input;
		$application = JFactory::getApplication();

		$cid = $input->get('cid', '', 'array');
		$db = JFactory::getDbo();

		if (!empty($cid))
		{
			foreach ($cid as $attributeid)
			{
				$count_products = $this->checkForProductsWithAttributeId($attributeid);

				if (!empty($count_products))
				{
					$application->enqueueMessage(sprintf(JText::_('COM_QUICK2CART_ATTRIBUTE_DELETE_ERROR'), $attributeid, implode(',', $count_products)), 'Error');
				}
				else
				{
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__kart_global_attribute_option'));
					$query->where($db->quoteName('attribute_id') . " = " . $attributeid);
					$db->setQuery($query);
					$db->execute();

					return true;
				}
			}
		}
	}

	/**
	 * Function to get count of products which are using provided attribute
	 *
	 * @param   INT  $attributeid  Global attribute id
	 *
	 * @return  count
	 *
	 * @since  2.5
	 *
	 */
	public function checkForProductsWithAttributeId($attributeid)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.item_id');
		$query->from($db->quoteName('#__kart_items', 'a'));
		$query->join('INNER', $db->quoteName('#__kart_itemattributes', 'ia') . 'ON' . $db->quoteName('ia.item_id') . '=' . $db->quoteName('a.item_id'));
		$query->where($db->quoteName('ia.global_attribute_id') . ' = ' . $attributeid);
		$db->setQuery($query);
		$count = $db->loadColumn();

		return $count;
	}
}
