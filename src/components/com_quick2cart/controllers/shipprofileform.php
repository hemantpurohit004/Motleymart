<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Shipping Profile controller class
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerShipprofileform extends Quick2cartController
{
	/**
	 * Method use when new zone create
	 *
	 * @since	2.2
	 *
	 * @return  void
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=shipprofileform');

		if (!empty($itemid))
		{
			$redirect = JRoute::_('index.php?option=com_quick2cart&view=shipprofileform&Itemid=' . $itemid, false);
		}
		else
		{
			$redirect = JRoute::_('index.php?option=com_quick2cart&view=shipprofileform', false);
		}

		$this->setRedirect($redirect);
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @since	1.6
	 *
	 * @return void
	 */
	public function edit()
	{
		$app = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_quick2cart.edit.shipprofile.id');
		$editId = $jinput->getInt('id', null, 'array');

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_quick2cart.edit.shipprofile.id', $editId);

		// Get the model.
		$model = $this->getModel('Shipprofileform', 'Quick2cartModel');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(
		JRoute::_(
		'index.php?option=com_quick2cart&view=shipprofileform&id=' . $editId, false
		)
		);
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$jinput = JFactory::getApplication()->input;

		// Initialise variables.
		$app = JFactory::getApplication();
		$editId = $app->input->getInt('id', null, 'array');
		$task = $app->input->get('task', null, '');
		$model = $this->getModel('Shipprofileform', 'Quick2cartModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		// Add current id
		$data['id'] = $editId;

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.shipprofile.data', $jform, array());

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.shipprofile.id');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=shipprofileform&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.shipprofile.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.shipprofile.id');
			$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=shipprofileform&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_quick2cart.edit.shipprofile.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_QUICK2CART_ITEM_SAVED_SUCCESSFULLY'));

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=shipprofileform');

		if ($task == "save")
		{
			$this->setRedirect(
			JRoute::_(
			'index.php?option=com_quick2cart&view=shipprofileform&id=' . $return . '&Itemid=' . $itemid, false
			)
			);
		}
		else
		{
			$this->setRedirect(
			JRoute::_(
			'index.php?option=com_quick2cart&view=shipprofiles&id=' . $return . '&Itemid=' . $itemid, false
			)
			);
		}

		// Flush the data from the session.
		$app->setUserState('com_quick2cart.edit.shipprofile.data', null);
	}

	/**
	 * Method to Save and Close.
	 *
	 * @since   2.2
	 *
	 * @return   null Json response object.
	 */
	public function saveAndClose()
	{
		$this->save();
	}

	/**
	 * Method to Cancel.
	 *
	 * @since   2.2
	 *
	 * @return   null Json response object.
	 */
	public function cancel()
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=shipprofiles&Itemid=' . $itemid, false);
		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * Method to Remove.
	 *
	 * @since   2.2
	 *
	 * @return   null Json response object.
	 */
	public function remove()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app = JFactory::getApplication();
		$model = $this->getModel('Shipprofileform', 'Quick2cartModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.shipprofile.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.shipprofile.id');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=shipprofile&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->delete($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.shipprofile.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.shipprofile.id');
			$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=shipprofile&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_quick2cart.edit.shipprofile.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_QUICK2CART_ITEM_DELETED_SUCCESSFULLY'));
		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url = (empty($item->link) ? 'index.php?option=com_quick2cart&view=shipprofiles' : $item->link);
		$this->setRedirect(JRoute::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_quick2cart.edit.shipprofile.data', null);
	}

	/**
	 * Method to add shipping method.
	 *
	 * @since   2.2
	 * @return   null Json response object.
	 */
	public function addShipMethod()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel('Shipprofileform', 'Quick2cartModel');

		$response = array();
		$response['error'] = 0;

		if (!$model->addShippingPlgMeth())
		{
			$response['error'] = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['shipProfileMethodId'] = $app->input->get('shipMethodId');
		}

		/*$response = array ("error"=>0,"shipProfileMethodId"=>"3");*/
		echo json_encode($response);

		$app->close();
	}

	/**
	 * Method This function delete the tax rule form perticular profile.
	 *
	 * @since   2.2
	 *
	 * @return   Json Plugin shipping methods list.
	 */
	public function deleteShipProfileMethod()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');

		$model = $this->getModel('Shipprofileform', 'Quick2cartModel');
		$ruleTable = $model->getTable('Shipmethods');

		$response = array();
		$response['error'] = 0;

		if (!$ruleTable->delete($data['shipMethodId']))
		{
			$response['error'] = 1;
			$response['errorMessage'] = $ruleTable->getError();
		}

		echo json_encode($response);
		$app->close();
	}

	/**
	 * Method Update Ship .
	 *
	 * @since   2.2
	 *
	 * @return   Json Plugin shipping methods list.
	 */
	public function updateShipMethod()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');
		$model = $this->getModel('Shipprofileform', 'Quick2cartModel');

		$response = array();
		$response['error'] = 0;

		if (!$model->addShippingPlgMeth(1))
		{
			$response['error'] = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['shipProfileMethodId'] = $app->input->get('shipMethodId');
		}

		echo json_encode($response);
		$app->close();
	}

	/**
	 * Method to Load Plug Method.
	 *
	 * @since   2.2
	 *
	 * @return   Json Plugin shipping methods list.
	 */
	public function qtcLoadPlgMethods()
	{
		$app = JFactory::getApplication();
		$extension_id = $app->input->post->get('qtcShipPluginId', 0, "INTEGER");
		$store_id = $app->input->post->get('store_id', 0, "INTEGER");
		$qtcshiphelper = new qtcshiphelper;
		$response = $qtcshiphelper->qtcLoadShipPlgMethods($extension_id, $store_id);
		echo json_encode($response);

		$app->close();
	}
}
