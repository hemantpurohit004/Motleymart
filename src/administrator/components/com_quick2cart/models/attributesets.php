<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
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
class Quick2cartModelAttributesets extends JModelList
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
				'global_attribute_ids', 'a.`global_attribute_ids`',
				'global_attribute_set_name', 'a.`global_attribute_set_name`',
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
		$query->select($this->getState('list.select', 'DISTINCT a.*'));
		$query->from('`#__kart_global_attribute_set` AS a');

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
	 * Function to delete attribute sets
	 *
	 * @return  null
	 *
	 * @since  2.5
	 *
	 * */
	public function delete()
	{
		$input = JFactory::getApplication()->input;
		$application = JFactory::getApplication();

		$cid = $input->get('cid', '', 'array');
		$db = JFactory::getDbo();

		if (!empty($cid))
		{
			foreach ($cid as $attributeSetId)
			{
				$count_products = $this->checkForProductsInAttributeSetId($attributeSetId);

				if (!empty($count_products))
				{
					$application->enqueueMessage(
					sprintf(JText::_('COM_QUICK2CART_ATTRIBUTE_SET_DELETE_ERROR'), $attributeSetId, implode(',', $count_products)), 'Error'
					);
				}
				else
				{
					return true;
				}
			}
		}

		$application->redirect("index.php?option=com_quick2cart&view=attributesets");
	}

	/**
	 * Function to check if products present for perticulat attribute set
	 *
	 * @param   Int  $attributeSetId  attribute set id
	 *
	 * @return  count
	 *
	 * @since  2.5
	 */
	public function checkForProductsInAttributeSetId($attributeSetId)
	{
		$attributesetModel = JModelLegacy::getInstance('Attributeset', 'Quick2cartModel');
		$categorys = $attributesetModel->getCategorysForAttributeSet($attributeSetId);

		if (!empty($categorys))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.item_id');
			$query->from($db->quoteName('#__kart_items', 'a'));
			$query->where($db->quoteName('a.category') . ' IN (' . implode(',', $categorys) . ')');

			$db->setQuery($query);
			$count = $db->loadColumn();
		}

		return $count;
	}
}
