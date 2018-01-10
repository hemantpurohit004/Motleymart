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
 * Methods supporting a list of shipping records.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelShipprofiles extends JModelList
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
		$app = JFactory::getApplication('site');

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

		// Load the filter search.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the store filter
		$storeFilter = $app->getUserStateFromRequest($this->context . '.filter_store', 'filter_store');

		$this->setState('filter.stores', $storeFilter);

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
	 * @return	JDatabaseQuery
	 *
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$storeHelper = new storeHelper;

		// Get all stores.
		$user      = JFactory::getUser();
		$storeList = $storeHelper->getUserStore($user->id);
		$storeIds  = array();

		foreach ($storeList as $store)
		{
			$storeIds[] = $store['id'];
		}

		$accessibleStoreIds = '';

		if (!empty($storeIds))
		{
			$accessibleStoreIds = implode(',', $storeIds);
		}
		else
		{
			// Accessed view before creating the store.
			$accessibleStoreIds = '-1';
		}

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*, s.title as store_title'));

		$query->from('`#__kart_shipprofile` AS a');
		$query->join('INNER', '`#__kart_store` AS s ON s.id=a.store_id');

		$query->where('(a.store_id IN (' . $accessibleStoreIds . '))');

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
				$query->where('( a.name LIKE ' . $search . ' )');
			}
		}

		// Store filter
		$storeFilter = $this->getState('filter.stores');

		if (!empty($storeFilter))
		{
			$query->where('s.id =' . $storeFilter);
		}

		return $query;
	}

	/*function delete($items)
	{
	$user = JFactory::getUser();
	$db=JFactory::getDBO();
	$ids= '';
	$app = JFactory::getApplication();

	$ids = (count($items)>1)?implode(',',$items):$items[0];

	if(is_array($items) && !empty($items))
	{
	$shipprofile_ids = implode(',',$items);

	$query="DELETE FROM #__kart_shipprofile where id IN (".$shipprofile_ids.")";
	$db->setQuery( $query );
	$result = $db->execute();

	if (!$db->execute())
	{
	$this->setError( $this->_db->getErrorMsg() );
	return 0;
	}
	}
	return 1;
	}
	*/

	/**
	 * This function manage items published or unpublished state
	 *
	 * @param   array    $items  delelte taxrate ids.
	 * @param   boolean  $state  1 for publish and 0 for unpublish.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function setItemState($items, $state)
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
				$db->quoteName('id') . '  IN (' . $taxreate_ids . ')'
			);

			$query->update($db->quoteName('#__kart_shipprofile'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();

			if (!$result)
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		// Clean cache
		return true;
	}
}
