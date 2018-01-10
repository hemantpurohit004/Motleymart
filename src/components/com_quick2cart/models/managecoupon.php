<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();
jimport('joomla.application.component.model');
/**
 * Quick2cartModelManagecoupon for Product Details page
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       1.6.7
 */
class Quick2cartModelManagecoupon extends JModelLegacy
{
	/*protected $_data;

	protected $_total = null;

	protected $_pagination = null;*/

	/**
	 * Constructor.
	 *
	 * @since   2.2
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe        = JFactory::getApplication();
		$jinput           = $mainframe->input;

		// Get the pagination request variables
		$limit            = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart       = $jinput->get('limitstart', 0, '', 'int');
		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', '', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		// Set the limit variable for query later on
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);

		// Set the limit variable for query later on
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get the extra fields information
	 *
	 * @param   Integer  $store_id  Stor Id
	 *
	 * @return	query
	 *
	 * @since	1.8.5
	 */
	public function _buildQuery($store_id = 0)
	{
		// Get the WHERE and ORDER BY clauses for the query
		$query = "SELECT * from #__kart_coupon as a";
		$query .= $this->_buildContentWhere($store_id);

		return $query;
	}

	/**
	 * Method to Develop Coupon
	 *
	 * @param   Integer  $zoneid  Zone id
	 *
	 * @return	boolean
	 *
	 * @since	1.8.5
	 */
	public function deletecoupon($zoneid)
	{
		if (!empty($zoneid))
		{
			$newzone = implode(',', $zoneid);
			$db      = JFactory::getDBO();
			$query   = "DELETE FROM #__kart_coupon where id IN (" . $newzone . ")";
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}
		/*else
		{
		$db=JFactory::getDBO();
		$query = "DELETE FROM #__kart_coupon where id=$zoneid[0]";
		$db->setQuery( $query );
		if (!$db->execute()) {
		$this->setError( $this->_db->getErrorMsg() );
		return false;
		}
		}*/
	}

	/**
	 * Method _buildContentWhere
	 *
	 * @param   Integer  $store_id  Store id
	 *
	 * @return	String
	 *
	 * @since	1.8.5
	 */
	public function _buildContentWhere($store_id = 0)
	{
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$db        = JFactory::getDBO();
		$where     = array();

		if (!empty($store_id))
		{
			$where[] = "  store_id=" . $store_id . " ";
		}

		$search = $mainframe->getUserStateFromRequest($option . 'search', 'search', '', 'string');

		if (trim($search) != '')
		{
			$query = "SELECT id FROM #__kart_coupon WHERE name LIKE '%" . $search . "%'";
			$db->setQuery($query);
			$createid = $db->loadResult();

			if ($createid)
			{
				$where[] = "name LIKE '%" . $search . "%'";
			}
		}

		return $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
	}

	/**
	 * Method GetManagecoupon
	 *
	 * @param   Integer  $store_id  Store id
	 *
	 * @return	String
	 *
	 * @since	1.8.5
	 */
	public function getManagecoupon($store_id = 0)
	{
		$db = JFactory::getDBO();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$jinput    = $mainframe->input;
		$option    = $jinput->get('option');
		$query     = $this->_buildQuery();
		$query .= $this->_buildContentWhere($store_id);

		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', '', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		if ($filter_order)
		{
			$qry = "SHOW COLUMNS FROM #__kart_coupon";
			$db->setQuery($qry);
			$exists = $db->loadobjectlist();

			foreach ($exists as $key => $value)
			{
				$allowed_fields[] = $value->Field;
			}

			if (in_array($filter_order, $allowed_fields))
			{
				$query .= " ORDER BY $filter_order $filter_order_Dir";
			}
		}

		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

		foreach ($this->_data as $data)
		{
			if ($data->item_id)
			{
				$data->item_id_name = $this->getautoname_DB($data->item_id, 'kart_items', 'item_id', 'name');
				$data->item_id_name = substr($data->item_id_name, 1, -1);
				$data->item_id_name = str_replace("||", ", ", $data->item_id_name);
			}

			if ($data->user_id)
			{
				$data->user_id_name = $this->getautoname_DB($data->user_id, 'users', 'id', 'name', 'id.block <> 1');
				$data->user_id_name = substr($data->user_id_name, 1, -1);
				$data->user_id_name = str_replace("||", ", ", $data->user_id_name);
			}
		}

		return $this->_data;
	}

	/**
	 * Method  Edit List
	 *
	 * @param   Integer  $zoneid  Zone id
	 *
	 * @return	Data
	 *
	 * @since	1.8.5
	 */
	public function Editlist($zoneid)
	{
		unset($this->_data);
		$query       = "SELECT * from #__kart_coupon where id=$zoneid";
		$this->_data = $this->_getList($query);

		if (!empty($this->_data[0]))
		{
			if ($this->_data[0]->item_id)
			{
				$this->_data[0]->item_id_name = $this->getautoname_DB($this->_data[0]->item_id, 'kart_items', 'item_id', 'name');
			}

			if ($this->_data[0]->user_id)
			{
				$this->_data[0]->user_id_name = $this->getautoname_DB($this->_data[0]->user_id, 'users', 'id', 'name', 'id.block <> 1');
			}
		}

		return $this->_data;
	}

	/**
	 * Method  Getautoname_DB
	 *
	 * @param   Integer  $autodata       Autodata
	 * @param   Integer  $element_table  Element_table
	 * @param   Integer  $element        Element
	 * @param   Integer  $element_value  Element_value
	 * @param   Integer  $extras         Extras
	 *
	 * @return	Data
	 *
	 * @since	1.8.5
	 */
	public function getautoname_DB($autodata, $element_table, $element, $element_value, $extras = '')
	{
		$autodata = str_replace("||", "','", $autodata);
		$autodata = str_replace('|', '', $autodata);

		$query_table[]      = '#__' . $element_table . ' as ' . $element;
		$element_table_name = $element;

		if (trim($autodata))
		{
			$query_condi[] = $element . "." . $element . " IN ('" . trim($autodata) . "')";
		}

		if (trim($extras))
		{
			$query_condi[] = $extras;
		}

		$tables = (count($query_table) ? ' FROM ' . implode("\n LEFT JOIN ", $query_table) : '');

		if ($tables)
		{
			$where = (count($query_condi) ? ' WHERE ' . implode("\n AND ", $query_condi) : '');

			if ($where)
			{
				$db    = JFactory::getDBO();
				$query = "SELECT " . $element_value . "
				\n " . $tables . " \n " . $where;

				$this->_db->setQuery($query);
				$loca_list = $this->_db->loadColumn();

				return ((!empty($loca_list)) ? "|" . implode('||', $loca_list) . "|" : '');
			}
		}
	}

	/**
	 * Method  GetTotal
	 *
	 * @param   Integer  $store_id  Store id
	 *
	 * @return	Data
	 *
	 * @since	1.8.5
	 */
	public function getTotal($store_id = 0)
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_total))
		{
			$query        = $this->_buildQuery($store_id);
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method  getPagination
	 *
	 * @param   Integer  $store_id  Store id
	 *
	 * @return	Pagination
	 *
	 * @since	1.8.5
	 */
	public function getPagination($store_id = 0)
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal($store_id), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method  SetItemState This model function manage items published or unpublished state
	 *
	 * @param   Array    $items  Items
	 * @param   Integer  $state  State
	 *
	 * @return	Boolean
	 *
	 * @since	1.8.5
	 */
	public function setItemState($items, $state)
	{
		$db = JFactory::getDBO();

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$db    = JFactory::getDBO();
				$query = "UPDATE  #__kart_coupon SET published=$state where id=" . $id;
				$db->setQuery($query);

				if (!$db->execute())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method  Store
	 *
	 * @param   Array  $data  Data
	 *
	 * @return	Boolean
	 *
	 * @since	1.8.5
	 */
	public function store($data)
	{
		$code = trim($data->get('code', '', 'RAW'));

		// If code does't exit
		if ($code == '')
		{
			return 0;
		}

		$db                 = JFactory::getDBO();
		$row1               = new stdClass;
		$coupon_id          = $data->get('coupon_id', '', 'INTEGER');
		$row1->name         = $data->get('coupon_name', '', 'RAW');
		$row1->published    = $data->get('published', '', 'INTEGER');
		$row1->code         = $db->escape($code);
		$row1->value        = $data->get('value', '', 'INTEGER');
		$row1->val_type     = $data->get('val_type', '', 'INTEGER');
		$row1->max_use      = $data->get('max_use', '', 'INTEGER');
		$row1->max_per_user = $data->get('max_per_user', '', 'INTEGER');
		$row1->description  = $data->get('description', '', 'RAW');
		$row1->params       = $data->get('params', '', 'STRING');
		$row1->from_date    = $data->get('from_date', '', 'RAW');
		$row1->exp_date     = $data->get('exp_date', '', 'RAW');

		// GET ITEM ID , USER ID FROM AUTO SUGGEST FORMAT
		$item_id = $this->sort_auto($data->get('item_id', '', 'STRING'));

		if ($item_id)
		{
			$row1->item_id = $item_id;
		}
		else
		{
			$row1->item_id = '';
		}

		$userId = $this->sort_auto($data->get('id', '', 'STRING'));

		$row1->store_id = $data->get('current_store');
		$row1->user_id  = $userId;

		if ($coupon_id)
		{
			$qry = "SELECT `id` FROM #__kart_coupon WHERE `id` = '{$coupon_id}'";
			$db->setQuery($qry);
			$exists = $db->loadResult();

			// Store the web link table to the database
			if ($exists)
			{
				$row1->id = $coupon_id;
				$db->updateObject('#__kart_coupon', $row1, 'id');
			}
		}
		else
		{
			$db->insertObject('#__kart_coupon', $row1, 'id');
		}

		return true;
	}

	/**
	 * Method  Sort_auto
	 *
	 * @param   Array  $data_auto  Data Auto
	 *
	 * @return	Boolean
	 *
	 * @since	1.8.5
	 */
	public function sort_auto($data_auto)
	{
		if ($data_auto)
		{
			$data_auto  = substr($data_auto, 1, -1);
			$data_autos = explode("||", $data_auto);
			sort($data_autos, SORT_NUMERIC);
			$data_auto = "|" . implode('||', $data_autos) . "|";

			return $data_auto;
		}
	}

	/**
	 * Method  Getcode
	 *
	 * @param   String  $code  Code
	 *
	 * @return	Boolean
	 *
	 * @since	1.8.5
	 */
	public function getcode($code)
	{
		$db  = JFactory::getDBO();
		$qry = "SELECT `id` FROM #__kart_coupon WHERE `code` = " . $db->quote($db->escape(trim($code)));
		$db->setQuery($qry);
		$exists = $db->loadResult();

		if ($exists)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Method  Getselectcode
	 *
	 * @param   String  $code  Code
	 * @param   String  $id    Id
	 *
	 * @return	Boolean
	 *
	 * @since	1.8.5
	 */
	public function getselectcode($code, $id)
	{
		$db = JFactory::getDBO();

		$qry = "SELECT `code` FROM #__kart_coupon WHERE id<>'{$id}' AND `code` = " . $db->quote($db->escape(trim($code)));
		$db->setQuery($qry);
		$exists = $db->loadResult();

		if ($exists)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/*
	This model function manage items published or unpublished state
	*/
	/*function setItemState($items,$state)
	{
	$db=JFactory::getDBO();

	if(is_array($items))
	{

	foreach($items as $id)
	{
	$db=JFactory::getDBO();
	$query="UPDATE #__kart_coupon SET published=".$state." WHERE id=".$id;
	$db->setQuery( $query );
	if (!$db->execute()) {
	$this->setError( $this->_db->getErrorMsg() );
	return false;
	}
	}
	}
	}*/
}
