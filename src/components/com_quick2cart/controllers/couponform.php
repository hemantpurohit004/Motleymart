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

jimport('joomla.application.component.controllerform');

/**
 * Coupon form controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerCouponform extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		$comquick2cartHelper = new comquick2cartHelper;

		$this->my_coupons_itemid    = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=coupons&layout=my');
		$this->create_coupon_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=couponform');

		parent::__construct($config);
		$this->view_list = 'coupons';
	}

	/**
	 * Called on add button @TODO - remove this when jform is used
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function addNew()
	{
		$link = JRoute::_('index.php?option=com_quick2cart&view=couponform&Itemid=' . $this->create_coupon_itemid, false);

		$this->setRedirect($link);
	}

	//

	/**
	 * Called on cancel button @TODO - remove this when jform is used
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function cancel($key = null)
	{
		$link = JRoute::_('index.php?option=com_quick2cart&view=coupons&layout=my&Itemid=' . $this->my_coupons_itemid, false);

		$this->setRedirect($link);
	}

	/**
	 * Find the auto suggestion according the db. @TODO - remove this when jform is used
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the primary key of the URL variable.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function edit($key = null, $urlVar = null)
	{
		$input = JFactory::getApplication()->input;

		// Get some variables from the request
		$cid = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);

		if (!count($cid))
		{
			$id   = $input->get('id', '', 'INT');
			$link = 'index.php?option=com_quick2cart&view=couponform&id=' . $id;
		}
		else
		{
			$link = 'index.php?option=com_quick2cart&view=couponform&id=' . $cid[0];
		}

		$link = JRoute::_($link . '&Itemid=' . $this->create_coupon_itemid, false);

		$this->setRedirect($link);
	}

	/**
	 * Overrides parent save method.@TODO - remove this when jform is used, as it might not be needed
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$task = $this->getTask();

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('Couponform', 'Quick2cartModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get->post;

		// Attempt to save the data.
		$return = $model->save($data);
		$id     = $return;

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.coupon.data', $data);

			// Tweak *important.
			$app->setUserState('com_quick2cart.edit.coupon.id', $data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.coupon.id');
			$this->setMessage(JText::sprintf('COM_QUICK2CART_SAVE_MSG_ERROR', $model->getError()), 'warning');
			$this->setRedirect('index.php?option=com_quick2cart&&view=coupon&layout=edit&id=' . $id);

			return false;
		}

		// Tweak *important.
		$app->setUserState('com_quick2cart.edit.coupon.id', $data->get('id', '', 'INT'));

		if ($task === 'apply')
		{
			if (!$id)
			{
				$id = (int) $app->getUserState('com_quick2cart.edit.coupon.id');
			}

			$redirect = 'index.php?option=com_quick2cart&task=coupon.edit&id=' . $id;
		}
		else
		{
			// Clear the profile id from the session.
			$app->setUserState('com_quick2cart.edit.coupon.id', null);

			// Flush the data from the session.
			$app->setUserState('com_quick2cart.edit.coupon.data', null);

			// Redirect to the list screen.
			$redirect = JRoute::_('index.php?option=com_quick2cart&view=coupons&layout=my&Itemid=' . $this->my_coupons_itemid, false);
		}

		$msg = JText::_('COM_QUICK2CART_SAVE_SUCCESS');
		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Find the auto suggestion according the db
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function findauto()
	{
		$jinput      = JFactory::getApplication()->input;
		$element     = $jinput->get('element', '', 'STRING');
		$element_val = $jinput->get('request_term', '', ' STRING');

		$autodata = '';

		if (isset($_POST[$element]))
		{
			$autodata = $_POST[$element];
		}

		$query_condi = array();
		$query_table = array();
		$loca_list   = array();

		$autodata      = str_replace("||", "','", $autodata);
		$autodata      = str_replace('|', '', $autodata);
		$element_table = "kart_items";

		if ($element == "item_id")
		{
			$element_field = "name";
			$store_id      = $jinput->get('store');
			$query_condi[] = $element . ".store_id = " . $store_id;
		}
		elseif ($element == "id")
		{
			$element_table = "users";
			$element_field = "name";
			$query_condi[] = $element . ".block <> 1";
		}

		$query_table[]      = '#__' . $element_table . ' as ' . $element;
		$element_table_name = $element;
		$query_condi[]      = $element . "." . $element_field . " LIKE '%" . trim($element_val) . "%'";

		if (trim($autodata))
		{
			$query_condi[] = $element . "." . $element . " NOT IN ('" . trim($autodata) . "')";
		}

		$tables = (count($query_table) ? ' FROM ' . implode("\n LEFT JOIN ", $query_table) : '');

		if ($tables)
		{
			$where = (count($query_condi) ? ' WHERE ' . implode("\n AND ", $query_condi) : '');

			if ($where)
			{
				$db    = JFactory::getDBO();
				$query = "SELECT distinct(" . $element_table_name . "." . $element . ")," . $element_table_name . "." . $element_field . "
				\n " . $tables . " \n " . $where;

				$db->setQuery($query);
				$loca_list = $db->loadRowList();
			}
		}

		$data = array();

		if ($loca_list)
		{
			foreach ($loca_list as $row)
			{
				$json          = array();

				// Name of the location
				$json['label'] = $row['1'];

				// Id of the location
				$json['value'] = $row['0'];
				$data[]        = $json;

				// $data[] = $row['0']; //name of the location
			}
		}

		echo json_encode($data);
		jexit();
	}

	/**
	 * Get get coupon code.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function getcode()
	{
		$jinput       = JFactory::getApplication()->input;
		$selectedcode = $jinput->get('selectedcode');

		$model       = $this->getModel('couponform');
		$coupon_code = $model->getcode(trim($selectedcode));

		echo $coupon_code;
		exit();
	}

	/**
	 * Get selectcode.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public	function getselectcode()
	{
		$jinput       = JFactory::getApplication()->input;
		$selectedcode = $jinput->get('selectedcode');
		$couponid     = $jinput->get('couponid');

		$model       = $this->getModel('couponform');
		$coupon_code = $model->getselectcode(trim($selectedcode), $couponid);

		echo $coupon_code;
		exit();
	}
}
