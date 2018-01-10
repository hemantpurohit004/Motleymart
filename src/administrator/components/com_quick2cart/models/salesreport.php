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

jimport('joomla.application.component.controller');

/**
 * SalesReport Model.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelSalesreport extends JModelLegacy
{
	// Changed by Deepa

	/*protected $_data;
	protected $_total = null;
	protected $_pagination = null;*/

	protected $data;

	protected $total = null;

	protected $pagination = null;

	/**
	 * Constructor.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe    = JFactory::getApplication();
		$jinput       = $mainframe->input;
		$this->option = $option = $jinput->get('option');
		$this->view   = $jinput->get('view');

		// Get pagination request variables
		$limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $jinput->get('limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		/*	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);*/
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// STORE FILTER search_store
		$search_store = $mainframe->getUserStateFromRequest($option . $this->view . 'search_store', 'search_store', 0, 'INTEGER');
		$this->setState('search_store', $search_store);
	}

	/**
	 * Method getSalesReport.
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function getSalesReport()
	{
		if (empty($this->_data))
		{
			$query       = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	/**
	 * Method _buildContentWhere.
	 *
	 * @return	condition
	 *
	 * @since	1.6
	 */
	public function _buildContentWhere()
	{
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$jinput    = $mainframe->input;
		$option    = $jinput->get('option');
		$db        = JFactory::getDBO();

		$where   = array();
		$where[] = '( o.`status`="C" || o.`status`="S" )';

		// From date FILTER
		$fromDate = $mainframe->getUserStateFromRequest($option . $this->view . 'salesfromDate', 'salesfromDate', '', 'RAW');
		$this->setState('salesfromDate', $fromDate);

		// To date FILTER
		$toDate = $mainframe->getUserStateFromRequest($option . $this->view . 'salestoDate', 'salestoDate', '', 'RAW');
		$this->setState('salestoDate', $toDate);

		if (!empty($toDate) && !empty($fromDate))
		{
			$where[] = '  DATE(o.`mdate`) BETWEEN \'' . $fromDate . '\' AND  \'' . $toDate . '\'';
		}

		$where[] = ' oi.`item_id`= i.`item_id` ';

		$search = $mainframe->getUserStateFromRequest($option . 'filter_search', 'filter_search', '', 'string');

		if (trim($search) != '')
		{
			/*	// check  where atleast one record is present
			$query="SELECT item_id FROM #__kart_items WHERE name LIKE '%".$search."%'";
			$db->setQuery($query);
			$createid=$db->loadResult();
			// if present
			if($createid)*/
			{
				$where[] = "i.name LIKE '%" . $search . "%'";
			}
		}

		// STORE FILTER
		$search_store = $mainframe->getUserStateFromRequest($option . $this->view . 'search_store', 'search_store', 0, 'INTEGER');

		if (trim($search_store) != 0)
		{
			$where[] = " i.`store_id`=" . $search_store . " ";
		}

		return $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
	}

	/**
	 * Method Buildquery.
	 *
	 * @return	query
	 *
	 * @since	1.6
	 */
	public function _buildQuery()
	{
		$db = JFactory::getDBO();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$jinput    = $mainframe->input;
		$option    = $jinput->get('option');
		$layout    = $jinput->get('layout', 'salesreport');

		// Get the WHERE and ORDER BY clauses for the query
		$where = '';

		$me      = JFactory::getuser();
		$user_id = $me->id;
		$where   = $this->_buildContentWhere();

		/*if($layout=='default')//payouts report //when called from front end
		{*/
		$query = "SELECT oi.`item_id`, SUM(oi.`product_quantity`) AS 'saleqty', i.`store_id`, i.`name` as item_name, i.`stock`, i.`mdate`, i.`state`
			 FROM `#__kart_order_item` AS oi
			 JOIN `#__kart_orders` AS o ON oi.`order_id` = o.`id`
			 JOIN `#__kart_items` AS i  ON oi.`item_id` = i.`item_id`
			" . $where . "
			GROUP BY `item_id` ";

		$filter_order     = $mainframe->getUserStateFromRequest($option . $this->view . '.filter_order', 'filter_order', 'saleqty', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . $this->view . 'salesreport.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		$orderByArray         = array();
		$orderByArray['name'] = "item_name";

		if ($filter_order)
		{
			if (!empty($orderByArray[$filter_order]))
			{
				$query .= " ORDER BY " . $orderByArray['name'] . " $filter_order_Dir";
			}
			else
			{
				$query .= " ORDER BY $filter_order $filter_order_Dir";
			}
		}
		/*}*/

		return $query;
	}

	/**
	 * Method getTotal.
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_total))
		{
			$query        = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method getPagination.
	 *
	 * @return	link
	 *
	 * @since	1.6
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method getCsvexportData.
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function getCsvexportData()
	{
		$query = $this->_buildQuery();
		$db    = JFactory::getDBO();
		$query = $db->setQuery($query);

		return $data = $db->loadAssocList();
	}
}
