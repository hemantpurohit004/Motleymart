<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');
require_once JPATH_SITE . '/components/com_quick2cart/models/cartcheckout.php';

use Joomla\Utilities\ArrayHelper;
/**
 * Quick2cart model.
 *
 * @since  1.6
 */
class Quick2cartModelCustomer_AddressForm extends JModelForm
{
	private $item = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since  1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('com_quick2cart');

		// Load state from the request userState on edit or from the passed variable on default
		if (JFactory::getApplication()->input->get('layout') == 'edit')
		{
			$id = JFactory::getApplication()->getUserState('com_quick2cart.edit.customer_address.id');
		}
		else
		{
			$id = JFactory::getApplication()->input->get('id');
			JFactory::getApplication()->setUserState('com_quick2cart.edit.customer_address.id', $id);
		}

		$this->setState('customer_address.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('customer_address.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function &getData($id = null)
	{
		if ($this->item === null)
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('customer_address.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table !== false && $table->load($id))
			{
				$user = JFactory::getUser();
				$id   = $table->id;
				$canEdit = $user->authorise('core.edit', 'com_quick2cart') || $user->authorise('core.create', 'com_quick2cart');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_quick2cart'))
				{
					$canEdit = $user->id == $table->created_by;
				}

				if (!$canEdit)
				{
					throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 500);
				}

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->item = ArrayHelper::toObject($properties, 'JObject');
			}
		}

		return $this->item;
	}

	/**
	 * Method to get the table
	 *
	 * @param   string  $type    Name of the JTable class
	 * @param   string  $prefix  Optional prefix for the table class name
	 * @param   array   $config  Optional configuration array for JTable object
	 *
	 * @return  JTable|boolean JTable if found, boolean false on failure
	 */
	public function getTable($type = 'Customer_address', $prefix = 'Quick2cartTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_quick2cart/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get an item by alias
	 *
	 * @param   string  $alias  Alias string
	 *
	 * @return int Element id
	 */
	public function getItemIdByAlias($alias)
	{
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_quick2cart.customer_address', 'customer_addressform', array(
			'control'   => 'jform',
			'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_quick2cart.edit.customer_address.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function save($data)
	{
		$object = (object) $data;

		if (empty($object->user_id))
		{
			$object->user_id = JFactory::getUser()->id;
		}

		// Update their details in the users table using id as the primary key.
		if (!empty($object->id))
		{
			$result = JFactory::getDbo()->updateObject('#__kart_customer_address', $object, 'id');

			if ($result == 1)
			{
				$msg = JText::_("COM_QUICK2CART_CUSTOMER_ADDRESS_UPDATE_MSG");
			}
		}
		else
		{
			$result = JFactory::getDbo()->insertObject('#__kart_customer_address', $object);
			$object->id = JFactory::getDbo()->insertid();

			if ($result == 1)
			{
				$msg = JText::_("COM_QUICK2CART_CUSTOMER_ADDRESS_ADD_MSG");
			}
		}

		$fieldHtml = $this->getAddressHtml($object->id);

		return $fieldHtml;
	}

	/**
	 * Check if data can be saved
	 *
	 * @return bool
	 */
	public function getCanSave()
	{
		$table = $this->getTable();

		return $table !== false;
	}

	/**
	 * Method to get address stored aginst provided user id
	 *
	 * @param   INT  $uid  user id
	 *
	 * @return List of addresses
	 */
	public function getUserAddressList($uid)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__kart_customer_address');
		$query->where('user_id = ' . $uid);

		// Get the options.
		$db->setQuery($query);
		$address = $db->loadObjectList();

		$fieldHtml = "";

		if (!empty($uid))
		{
			$cartCheckoutModel = new Quick2cartModelcartcheckout;
			$userCountry = array();
			$userState = array();

			$billing_flag = 0;
			$shipping_flag = 0;

			// Check if address is used as billing or shipping order
			if (!empty($address))
			{
				foreach ($address as $item)
				{
					if (!empty($item->last_used_for_shipping))
					{
						$shipping_flag = 1;
					}

					if (!empty($item->last_used_for_billing))
					{
						$billing_flag = 1;
					}
				}

				// Pre select first address as shipping address
				if (empty($shipping_flag))
				{
					$address[0]->last_used_for_shipping = 1;
				}

				// Pre select first address as billing address
				if (empty($billing_flag))
				{
					$address[0]->last_used_for_billing = 1;
				}

				foreach ($address as $item)
				{
					if (!array_key_exists($item->country_code, $userCountry))
					{
						if (!empty($item->country_code))
						{
							$userCountry[$item->country_code] = $cartCheckoutModel->getCountryName($item->country_code);
						}
					}

					$item->country_name = $userCountry[$item->country_code];

					if (!array_key_exists($item->state_code, $userState))
					{
						if (!empty($item->state_code))
						{
							$userState[$item->state_code] = $cartCheckoutModel->getStateName($item->state_code);
						}
					}

					if (isset($userState[$item->state_code]))
					{
						$item->state_name = $userState[$item->state_code];
					}
					else
					{
						$item->state_name = '';
					}

					$layout = new JLayoutFile('customer_address', $basePath = JPATH_ROOT . '/components/com_quick2cart/layouts/address');
					$fieldHtml .= $layout->render($item);
				}
			}
		}

		return $fieldHtml;
	}

	/**
	 * Method to get address div html
	 *
	 * @param   INT  $id  address id
	 *
	 * @return address
	 */
	public function getAddressHtml($id)
	{
		$address = $this->getAddress($id);

		$fieldHtml = "";

		if (!empty($address))
		{
			$cartCheckoutModel = new Quick2cartModelcartcheckout;

			$address->country_name = $cartCheckoutModel->getCountryName($address->country_code);
			$address->state_name = $cartCheckoutModel->getStateName($address->state_code);

			$layout = new JLayoutFile('customer_address', $basePath = JPATH_ROOT . '/components/com_quick2cart/layouts/address');
			$fieldHtml = $layout->render($address);
		}

		return $fieldHtml;
	}

	/**
	 * Method to get address
	 *
	 * @param   INT  $id  address id
	 *
	 * @return address
	 */
	public function getAddress($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__kart_customer_address');
		$query->where('id = ' . $id);

		// Get the options.
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to delete address data
	 *
	 * @param   INT  $addressId  ID of address to be deleted
	 *
	 * @return bool|int If success returns the id of the deleted item, if not false
	 *
	 * @throws Exception
	 */
	public function delete($addressId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$conditions = array($db->quoteName('id') . ' = ' . $addressId);

		$query->delete($db->quoteName('#__kart_customer_address'));
		$query->where($conditions);

		$db->setQuery($query);

		$result = $db->execute();

		return $result;
	}
}
