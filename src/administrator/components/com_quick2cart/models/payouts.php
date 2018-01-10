<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of payouts records.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelPayouts extends JModelList
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
				'username', 'u.username',
				'payee_name', 'a.payee_name',
				'transaction_id', 'a.transaction_id',
				'date', 'a.date',
				'email_id', 'a.email_id',
				'amount', 'a.amount',
				'status', 'a.status'
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
		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . 'filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);

		$country = $app->getUserStateFromRequest($this->context . 'filter.country', 'filter_country', '', 'string');
		$this->setState('filter.country', $country);

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

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'a.id, a.user_id, a.payee_name, a.transaction_id, a.date, a.email_id, a.amount, a.status'
			)
		);

		$query->from('`#__kart_payouts` AS a');

		$query->select('u.username');
		$query->join('LEFT', '`#__users` AS u ON u.id=a.user_id');

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
				$query->where('( a.payee_name LIKE ' . $search .
					'  OR  a.transaction_id LIKE ' . $search .
					'  OR  a.email_id LIKE ' . $search . ' )'
				);
			}
		}

		// Filter by published state.

		/*$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.status = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.status IN (0, 1))');
		}*/

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
	 * Method to get a list of payouts.
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
	 * Function to CSV export data
	 *
	 * @return  null
	 */
	public function getCsvexportData()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query = $this->getListQuery();
		$db->setQuery($query);

		return $data = $db->loadAssocList();
	}

	/**
	 * Function to get payout from datas
	 *
	 * @return  null
	 */
	public function getPayoutFormData()
	{
		// GETTING ALL COMMISSION should be deducted from user including all store
		$query = 'SELECT SUM( fee ) AS fee, `owner` as user_id
		 FROM `#__kart_store`
		 GROUP BY `owner`';
		$this->_db->setQuery($query);
		$payouts = $this->_db->loadObjectList();

		foreach ($payouts as $index => $key)
		{
			// Calculate total total sales

			// HERE USER_ID = STORE OWNER USERID
			$query1 = "SELECT SUM( i.product_final_price ) AS total_amount,o.`email`
			FROM `#__kart_orders` AS o
			LEFT JOIN `#__kart_order_item` AS i ON o.id = i.order_id
			LEFT JOIN `#__kart_store` AS s ON s.id = i.store_id
			WHERE o.status='C' AND s.owner = " . $key->user_id;

			$this->_db->setQuery($query1);
			$userPriceDetail = $this->_db->loadAssoc();

			// Calculate old payout given
			if (!empty($userPriceDetail['total_amount']))
			{
				// Owner as user_id
				$query = "SELECT SUM(amount)
				 FROM #__kart_payouts
				 WHERE user_id = " . $key->user_id . "  AND status = 1";
				$this->_db->setQuery($query);
				$paid_amount = $this->_db->loadresult();
				$key->total_amount = $userPriceDetail['total_amount'] - $paid_amount;
				$key->email = $userPriceDetail['email'];
			}
			else
			{
				unset($payouts[$index]);
			}
		}

		return $payouts;
	}
}
