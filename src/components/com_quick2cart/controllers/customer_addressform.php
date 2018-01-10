<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2Cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Customer_address controller class.
 *
 * @since  1.6
 */
class Quick2cartControllerCustomer_AddressForm extends JControllerForm
{
	/**
	 * Save
	 *
	 * @param   Integer  $key     Key
	 * @param   String   $urlVar  URLVar
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	public function save($key = null, $urlVar = null)
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$userId = $jinput->get('userid', '', 'INT');
		$model = $this->getModel('customer_addressform');
		$baseURL = JUri::base() . "index.php";
		$task = $jinput->get('task', '', 'STRING');
		$data = $jinput->get('jform', '', '');
		$data['country_code'] = $jinput->get('country_code', '', '');
		$data['state_code'] = $jinput->get('state_code', '', '');
		$data['user_id'] = $userId;

		$address = $model->save($data);

		echo $address;

		jexit();
	}

	/**
	 * User Address List
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	public function getUserAddressList()
	{
		$jinput = JFactory::getApplication()->input;
		$uid = $jinput->get('uid', '0', 'INT');
		$model = $this->getModel('customer_addressform');
		$address = $model->getUserAddressList($uid);

		echo $address;

		jexit();
	}

	/**
	 * Delete
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$addressId = $jinput->get('addressId', '', 'INT');
		$model = $this->getModel('customer_addressform');
		$result = $model->delete($addressId);

		if ($result == 1)
		{
			echo 1;
		}
		else
		{
			echo 0;
		}

		jexit();
	}
}
