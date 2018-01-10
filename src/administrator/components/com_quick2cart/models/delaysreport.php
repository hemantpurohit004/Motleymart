<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.application.component.controller');

/**
 * Methods supporting delays report.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelDelaysreport extends JModelLegacy
{
	protected $protected_data;

	protected $protected_total = null;

	protected $protected_pagination = null;

	/**
	 * Cnstructor
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$option = JFactory::getApplication()->input->get('option');

		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// STORE FILTER search_store
		$search_store = $mainframe->getUserStateFromRequest($option . 'search_store', 'search_store', 0, 'INTEGER');
		$this->setState('search_store', $search_store);
	}

	/**
	 * Function to get delay report
	 *
	 * @return  query
	 *
	 * @since   1.6
	 */
	public function getDelaysReport()
	{
		if (empty($this->protected_data))
		{
			$query = $this->_buildQuery();
			$this->protected_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		foreach ($this->protected_data as $ind => $obj)
		{
			global $mainframe, $option;
			$mainframe = JFactory::getApplication();
			$storeHelper = new storeHelper;
			$delay = $storeHelper->GetDelaysInOrder($obj->id);
			$this->protected_data[$ind]->delay = $delay;
		}

		return $this->protected_data;
	}

	/**
	 * Function to build where condition query
	 *
	 * @return  query
	 *
	 * @since   1.6
	 */
	public function _buildContentWhere()
	{
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;
		$option = $jinput->get('option');
		$db = JFactory::getDbo();
		$fromDate = $toDate = $search = '';
		$status = $mainframe->getUserStateFromRequest($option . 'search_select', 'search_select', '', 'string');
		$delayday = $mainframe->getUserStateFromRequest($option . 'search_select_delay', 'search_select_delay', '', 'INTEGER');

		$where = array();
		$where[] = " i.status != 'P'";

		// From date FILTER
		$fromDate = $mainframe->getUserStateFromRequest($option . 'salesfromDate', 'salesfromDate', '', 'RAW');
		$this->setState('salesfromDate', $fromDate);

		// To date FILTER
		$toDate = $mainframe->getUserStateFromRequest($option . 'salestoDate',  'salestoDate', '', 'RAW');
		$this->setState('salestoDate', $toDate);

		if (!empty($toDate) && !empty($fromDate))
		{
			$where[] = "  DATE(i.`cdate`) BETWEEN '" . $fromDate . "' AND  '" . $toDate . "'";
		}

		$search = $mainframe->getUserStateFromRequest($option . 'search', 'search', '',  'string');

		if (trim($search) != '')
		{
			// $where[] = $list .'llll'. $search;
			$where[] = "i.name LIKE '%" . $search . "%' OR i.id LIKE '%" . $search . "%' OR i.prefix LIKE '%" . $search . "%'";
		}

		if ($status == 'E' || $status == 'C' || $status == 'S')
		{
			$where[] = 'i.status = ' . $this->_db->Quote($status);
		}

		$delayday = $mainframe->getUserStateFromRequest($option . 'search_select_delay', 'search_select_delay', '', 'INTEGER');

		if ($delayday)
		{
			$orders_array = array();
			$query = "SELECT i.id FROM #__kart_orders AS i where DATE(i.`cdate`) BETWEEN '" . $fromDate . "' AND  '" . $toDate . "'";

			$db->setQuery($query);
			$korders = $db->loadColumn();

			foreach ($korders as $orderid)
			{
				$storeHelper = new storeHelper;
				$delay = $storeHelper->GetDelaysInOrder($orderid);

				if ($delay >= $delayday)
				{
					$orders_array[] = $orderid;
				}
			}

			if ($orders_array)
			{
				$orders_array = array_values($orders_array);
				$orders_array = array_map('trim', $orders_array);
				$orders_str = implode("','", $orders_array);
				$orders_str = "'{$orders_str}'";

				$where[] = ' i.id IN ( ' . $orders_str . ') ';
			}
		}

		return $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
	}

	/**
	 * Function to build query
	 *
	 * @return  query
	 *
	 * @since   1.6
	 */
	public function _buildQuery()
	{
		$db = JFactory::getDbo();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$option = JFactory::getApplication()->input->get('option');
		$layout = JRequest::getVar('layout', 'default');

		// Get the WHERE and ORDER BY clauses for the query
		$where = '';
		$where = $this->_buildContentWhere();

		// Payouts report //when called from front end
		if ($layout == 'salesreport')
		{
			$query = "SELECT oi.`item_id` , SUM( oi.`product_quantity` ) AS 'saleqty', i . `store_id`,i.`name` as item_name,i.`stock`,i.`mdate`,i.`state`
										FROM  `#__kart_order_item` AS oi
												JOIN  `#__kart_orders` AS o ON oi.`order_id` = o.`id`
												JOIN  `#__kart_items` AS i  ON oi.`item_id` = i.`item_id`
										" . $where . "
										GROUP BY  `item_id` ";
			$filter_order = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', '', 'cmd');
			$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', '', 'word');

			$orderByArray = array();
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
		}

		$query = "SELECT i.processor, i.amount, i.cdate, i.payee_id,i.status,i.id,i.prefix,i.email,i.currency,i.name FROM #__kart_orders AS i"
		. $where . "GROUP BY mdate DESC";

	/*	if($layout=='default')
		{
		$query="SELECT i.processor, i.amount, i.cdate, i.payee_id,i.status,i.id,i.prefix,i.email,i.currency,i.name FROM #__kart_orders AS i".$where."
		GROUP BY mdate DESC";

			$filter_order=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','mdate','cmd');
 			$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir','desc','word');

			$orderByArray = array();
			$orderByArray['name'] = "item_name";

			if($filter_order)	{
				if(!empty($orderByArray[$filter_order])){
					$query.=" ORDER BY ".$orderByArray['name']." $filter_order_Dir";
				}else {
					$query.=" ORDER BY $filter_order $filter_order_Dir";
				}
			}
		}
		*/

		return $query;
	}

	/**
	 * Function to get total count.
	 *
	 * @return  int
	 *
	 * @since   1.6
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->protected_total))
		{
			$query = $this->_buildQuery();
			$this->protected_total = $this->_getListCount($query);
		}

		return $this->protected_total;
	}

	/**
	 * Function to get pagination.
	 *
	 * @return  pagination
	 *
	 * @since   1.6
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->protected_pagination))
		{
			jimport('joomla.html.pagination');
			$this->protected_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->protected_pagination;
	}

	/**
	 * Function to get CSV data.
	 *
	 * @return  ARRAY
	 *
	 * @since   1.6
	 */
	public function getCsvexportData()
	{
		$query = $this->_buildQuery();
		$db = JFactory::getDBO();
		$query = $db->setQuery($query);

		return $data = $db->loadAssocList();
	}
}
