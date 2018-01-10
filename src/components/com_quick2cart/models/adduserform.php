<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Quick2cart model.
 *
 * @since  1.6
 */
class Quick2cartModelAddUserForm extends JModelForm
{
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
		$form = $this->loadForm('com_quick2cart.adduserform', 'adduserform', array(
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

		$fieldHtml = $this->getAddress($object->id);

		return $fieldHtml;
	}
}
