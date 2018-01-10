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
 * Methods supporting a list of stores records.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelStores extends JModelList
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
				'title', 'a.title',
				'title', 'a.description',
				'vendor_name', 'u.name',
				'published', 'a.published',
				'email', 'a.store_email',
				'telephone', 'a.phone'
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

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_quick2cart');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.title', 'asc');
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
		$user = JFactory::getUser();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState('list.select',
			' DISTINCT(a.`id`), a.`owner`, a.`title`, a.`description`, a.`address`, a.`phone`' .
			', a.`store_email`, a.`store_avatar`, a.`fee`, a.`live` AS published, a.`cdate`, a.`mdate`' .
			', a.`extra`, a.`company_name`, a.`payment_mode`, a.`pay_detail`, a.`vanityurl`'
			)
		);
		$query->from('`#__kart_store` AS a');

		// Show only logged in user's stores
		$query->where('a.owner=' . $user->id);

		$query->select(' u.`username`, u.`name`, u.`email`');
		$query->join('LEFT', '`#__users` AS u ON a.owner=u.id');

		$query->select(' r.`role`');
		$query->join('LEFT', '`#__kart_role` AS r ON r.store_id=a.id');

		$query->where('r.user_id=' . $user->id);

		// Filter by published state.
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.live = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.live IN (0, 1))');
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
				$query->where('( a.title LIKE ' . $search .
					' OR  u.username LIKE ' . $search .
					' OR  u.name LIKE ' . $search .
					' OR  a.store_email LIKE ' . $search . ' )'
				);
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
	 * Method to get a list of stores.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get store roles
		$comquick2cartHelper = new comquick2cartHelper;

		foreach ($items as $item)
		{
			$item->role = $comquick2cartHelper->getRole($item->role);
		}

		return $items;
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
		$app = JFactory::getApplication();

		if ($state === 1)
		{
			$params = JComponentHelper::getParams('com_quick2cart');
			$admin_approval_stores = (int) $params->get('admin_approval_stores');

			// If admin approval is on for stores
			if ($admin_approval_stores === 1)
			{
				$app->enqueueMessage(JText::_('COM_QUICK2CART_ERR_MSG_ADMIN_APPROVAL_NEEDED_STORES'), 'error');

				return 0;
			}
		}

		$count = 0;

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$query = $db->getQuery(true);

				// Update the reset flag
				$query->update($db->quoteName('#__kart_store'))
					->set($db->quoteName('live') . ' = ' . $state)
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
	 * Send mail to owner on store approval
	 */
	/*function sendMailToOwnerOnStoreApproval($store)
	{
		$app = JFactory::getApplication();

		$fromname = $app->getCfg('fromname');
		$sitename = $app->getCfg('sitename');

		$sendto   = $store->store_email;

		$subject = JText::_('COM_Q2C_STORE_AAPROVED_SUBJECT');
		$subject = str_replace('{storename}', $store->title, $subject);
		$body    = JText::_('COM_Q2C_STORE_APPROVED_BODY');
		$body    = str_replace('{admin}', $fromname, $body);
		$body    = str_replace('{sitename}', $sitename, $body);

		$comquick2cartHelper = new comquick2cartHelper;
		$res = $comquick2cartHelper->sendmail($sendto, $subject, $body);
	}*/

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
				 FROM `#__kart_role`, `#__kart_store`
				 USING `#__kart_store`
				 INNER JOIN `#__kart_role`
				 WHERE `#__kart_store`.id = `#__kart_role`.store_id
				 AND `#__kart_store`.id = ' . $id;

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
