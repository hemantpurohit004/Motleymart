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

jimport('joomla.application.component.modeladmin');

/**
 * Item Model for an Coupon.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelCoupon extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_QUICK2CART';

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object
	 */
	public function getTable($type = 'Coupon', $prefix = 'Quick2cartTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional ordering field.
	 * @param   boolean  $loadData  An optional direction (asc|desc).
	 *
	 * @return  JForm    $form      A JForm object on success, false on failure
	 *
	 * @since   2.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_quick2cart.coupon', 'coupon', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	$data  The data for the form.
	 *
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_quick2cart.edit.coupon.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  $item  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if ($item->item_id)
			{
				$item->item_id_name = $this->getautoname_DB($item->item_id, 'kart_items', 'item_id', 'name');
			}

			if ($item->user_id)
			{
				$item->user_id_name = $this->getautoname_DB($item->user_id, 'users', 'id', 'name', 'id.block <> 1');
			}
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__tj_coupon');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return   mixed		The user id on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function save($data)
	{
		$db  = JFactory::getDBO();
		$obj = new stdClass;

		// Sj change start
		$coupon_id     = $data->get('coupon_id', '', 'RAW');
		$obj->store_id = $data->get('current_store');

		// Sj change end

		// GET ITEM ID , USER ID FROM AUTO SUGGEST FORMAT
		$item_id = $this->sort_auto($data->get('item_id', '', 'STRING'));

		if ($item_id)
		{
			$obj->item_id = $item_id;
		}
		else
		{
			$obj->item_id = '';
		}

		$obj->name         = $data->get('coupon_name', '', 'RAW');
		$obj->published    = $data->get('published');
		$obj->code         = $db->escape(trim($data->get('code', '', 'RAW')));
		$obj->value        = $data->get('value');
		$obj->val_type     = $data->get('val_type');
		$obj->max_use      = $data->get('max_use');
		$obj->max_per_user = $data->get('max_per_user');
		$obj->description  = $data->get('description', '', 'RAW');
		$obj->extra_params = $data->get('params', '', 'RAW');
		$obj->from_date    = $data->get('from_date', '', 'RAW');
		$obj->exp_date     = $data->get('exp_date', '', 'RAW');

		if ($coupon_id)
		{
			$qry = "SELECT `id` FROM #__kart_coupon WHERE `id` = '{$coupon_id}'";
			$db->setQuery($qry);
			$exists = $db->loadResult();

			if ($exists)
			{
				$obj->id = $coupon_id;

				if (!$db->updateObject('#__kart_coupon', $obj, 'id'))
				{
					echo $db->stderr();

					return false;
				}

				return $obj->id;
			}
		}
		else
		{
			if (!$db->insertObject('#__kart_coupon', $obj, 'id'))
			{
				echo $db->stderr();

				return false;
			}

			return $db->insertid();
		}

		return false;
	}

	/**
	 * Method to sort data
	 *
	 * @param   array  $data_auto  The form data.
	 *
	 * @return   array  sorted  data
	 *
	 * @since	1.6
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
	 * Method to get name of field in database
	 *
	 * @param   array   $autodata       The form data.
	 * @param   string  $element_table  table name
	 * @param   string  $element        element name
	 * @param   string  $element_value  element value
	 * @param   string  $extras         extra
	 *
	 * @return   string  get name of field in database
	 *
	 * @since	1.6
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
	 * Method to check if coupon exists
	 *
	 * @param   string  $code  coupon code
	 *
	 * @return  string  get name of field in database
	 *
	 * @since	1.6
	 */
	public function getcode($code)
	{
		$db  = JFactory::getDBO();
		$qry = "SELECT `id`
		 FROM #__kart_coupon WHERE `code` = " . $db->quote($db->escape(trim($code)));
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
	 * Method to get other codes
	 *
	 * @param   string  $code  coupon code
	 * @param   string  $id    id of coupon table
	 *
	 * @return  int  1 or 0
	 *
	 * @since	1.6
	 */
	public function getselectcode($code, $id)
	{
		$db = JFactory::getDBO();

		$qry = "SELECT `code`
		 FROM #__kart_coupon
		 WHERE id<>'" . $id . "'
		 AND `code` = " . $db->quote($db->escape(trim($code)));

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
}
