<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die(';)');

jimport('joomla.application.component.model');
jimport('joomla.database.table.user');

/**
 * Methods Downloads Model.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelDownloads extends JModelLegacy
{
	/**
	 * Constructor.
	 *
	 * @since   2.2
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;
		$option = $jinput->get('option');
		$view = $jinput->get('view');

		// Get the pagination request variables
		$limit = $mainframe->getUserStateFromRequest(
		'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $jinput->get('limitstart', 0, '', 'int');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method _buildQuery.
	 *
	 * @param   Integer  $userId   User ID
	 * @param   Integer  $orderid  Order ID.
	 *
	 * @return  condition
	 *
	 * @since   1.6
	 */
	public function _buildQuery($userId,$orderid='')
	{
		global $mainframe, $option;
		$db = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;
		$option = $jinput->get('option');
		$view = $jinput->get('view');

		$where = $this->_buildContentWhere($userId, $orderid);
		$query = "SELECT 	pf.`file_display_name`,oi.`order_id`,o.`prefix`,o.`user_info_id`,
		f.`id`,f.`product_file_id`,f.`order_item_id`,f.`download_count`,
		f.`download_limit`,f.`cdate`,f.`expirary_date`
		FROM #__kart_orderItemFiles AS f
		INNER JOIN `#__kart_order_item` AS oi ON f.order_item_id = oi.order_item_id
		INNER JOIN `#__kart_orders` AS o ON o.`id` = oi.`order_id`
		INNER JOIN `#__kart_itemfiles` AS pf ON pf.`file_id` = f.`product_file_id`
		" . $where . ' ';

		// Get filter order
		$filter_order = $mainframe->getUserStateFromRequest(
		$option . '$view.filter_order', 'filter_order', 'oi.order_id', 'cmd'
		);
		$filter_order_Dir = $mainframe->getUserStateFromRequest(
		$option . '$view.filter_order_Dir', 'filter_order_Dir', 'desc', 'word'
		);

		if ($filter_order)
		{
			$qry1 = "SHOW COLUMNS FROM #__kart_order_item";
			$db->setQuery($qry1);
			$exists1 = $db->loadobjectlist();

			foreach ($exists1 as $key1 => $value1)
			{
				$allowed_fields[] = $value1->Field;
			}

			if (in_array('order_id', $allowed_fields))
			{
				$query .= " ORDER BY $filter_order $filter_order_Dir,f.`product_file_id`";
			}
		}

		return $query;
	}

	/**
	 * Method _buildContentWhere.
	 *
	 * @param   Integer  $userId   User ID
	 * @param   Integer  $orderid  Order ID.
	 *
	 * @return  condition
	 *
	 * @since   1.6
	 */
	public function _buildContentWhere($userId,$orderid='')
	{
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$db     = JFactory::getDBO();
		$jinput = $mainframe->input;
		$option = $jinput->get('option');
		$view = $jinput->get('view');

		$where = array();

		// 	CHECK FOR parent
		if (!empty($userId))
		{
			$where[] = ' o.`user_info_id`= ' . $userId;
		}

		if (!empty($orderid))
		{
			$where[] = ' o.`id`= ' . $orderid;
		}

		// Added by Sneha
		$search = $mainframe->getUserStateFromRequest($option . "$view.search_list", 'search_list', '', 'string');

		if ($search)
		{
			$where[] = "(pf.file_display_name like \"%" . $search . "%\")";
		}

		return $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
	}

	/**
	 * Method getTotal.
	 *
	 * @param   Integer  $userId   User ID
	 * @param   Integer  $orderid  Order ID.
	 *
	 * @return  total
	 *
	 * @since   1.6
	 */
	public function getTotal($userId,$orderid='')
	{
		/*if (empty($this->_total))*/
		{
			$query = $this->_buildQuery($userId, $orderid);

			/*$query 	   .= $this->_buildContentWhere($store_id,$client);*/

		$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method getPagination.
	 *
	 * @param   Integer  $userId   User ID
	 * @param   Integer  $orderid  Order ID.
	 *
	 * @return  pagination
	 *
	 * @since   1.6
	 */
	public function getPagination($userId,$orderid='')
	{
		{
			jimport('joomla.html.pagination');

			$this->_pagination = new JPagination($this->getTotal($userId, $orderid), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method getAllDownloads.
	 *
	 * @param   Integer  $userId   User ID
	 * @param   Integer  $orderid  Order ID.
	 *
	 * @return  data
	 *
	 * @since   1.6
	 */
	public function getAllDownloads($userId, $orderid='')
	{
		$query = $this->_buildQuery($userId, $orderid);
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

		return $this->_data;
	}
}
