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
 * Taxprofiles controller class.
 *
 * @since  1.6
 */
class Quick2cartControllerTaxprofileForm extends Quick2cartController
{
	/**
	 * Method use when new taxprofile create
	 *
	 * @since	1.6
	 *
	 * @return void
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=taxprofileform');

		if (!empty($itemid))
		{
			$redirect = JRoute::_('index.php?option=com_quick2cart&view=taxprofileform&Itemid=' . $itemid, false);
		}
		else
		{
			$redirect = JRoute::_('index.php?option=com_quick2cart&view=taxprofileform', false);
		}

		$this->setRedirect($redirect);
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @since	1.6
	 *
	 * @return  void
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_quick2cart.edit.taxprofile.id');
		$editId = JFactory::getApplication()->input->getInt('id', null, 'array');

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_quick2cart.edit.taxprofile.id', $editId);

		// Get the model.
		$model = $this->getModel('TaxprofileForm', 'Quick2cartModel');

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
		$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=taxprofileform&id=' . $editId, false));
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app = JFactory::getApplication();
		$editId = $app->input->getInt('id', null, 'array');
		$task = $app->input->get('task', null, '');
		$model = $this->getModel('TaxprofileForm', 'Quick2cartModel');

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
			$app->setUserState('com_quick2cart.edit.taxprofile.data', $jform, array());

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.taxprofile.id');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=taxprofileform&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.taxprofile.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.taxprofile.id');
			$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=taxprofileform&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_quick2cart.edit.taxprofile.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_QUICK2CART_ITEM_SAVED_SUCCESSFULLY'));

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=taxprofileform');

		if ($task == "save")
		{
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=taxprofileform&id=' . $return . '&Itemid=' . $itemid, false));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=taxprofiles&id=' . $return . '&Itemid=' . $itemid, false));
		}

		// Flush the data from the session.
		$app->setUserState('com_quick2cart.edit.taxprofile.data', null);
	}

	/**
	 * Method to save and close.
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public Function saveAndClose()
	{
		$this->save();
	}

	/**
	 * Method Cancel.
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function cancel()
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=taxprofiles&Itemid=' . $itemid, false);
		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * Method to Remove.
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function remove()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app = JFactory::getApplication();
		$model = $this->getModel('TaxprofileForm', 'Quick2cartModel');

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
			$app->setUserState('com_quick2cart.edit.taxprofile.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.taxprofile.id');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=taxprofile&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->delete($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.taxprofile.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.taxprofile.id');
			$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=taxprofile&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_quick2cart.edit.taxprofile.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_QUICK2CART_ITEM_DELETED_SUCCESSFULLY'));
		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url = (empty($item->link) ? 'index.php?option=com_quick2cart&view=taxprofiles' : $item->link);
		$this->setRedirect(JRoute::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_quick2cart.edit.taxprofile.data', null);
	}

	/**
	 * Method to add tax rule against tax profile.
	 *
	 * @since   2.2
	 * @return   null Json response object.
	 */
	public function addTaxRule()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel('TaxprofileForm', 'Quick2cartModel');

		$response = array();
		$response['error'] = 0;

		if (!$model->saveTaxRule())
		{
			$response['error'] = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['taxrule_id'] = $app->input->get('taxrule_id');
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * This function delete the tax rule form perticular profile.
	 *
	 * @since	2.2
	 *
	 * @return  void
	 */
	public function deleteProfileRule()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');

		$model = $this->getModel('TaxprofileForm', 'Quick2cartModel');
		$ruleTable = $model->getTable('Taxrules');

		$response = array();
		$response['error'] = 0;

		if (!$ruleTable->delete(array($data['taxrule_id'])))
		{
			$response['error'] = 1;
			$response['errorMessage'] = $ruleTable->getError();
		}

		echo json_encode($response);
		$app->close();
	}

	/**
	 * This function Update tax rule associated with taxprofile.
	 *
	 * @since	2.2
	 *
	 * @return  void
	 */
	public function updateTaxRule()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');
		$model = $this->getModel('TaxprofileForm', 'Quick2cartModel');

		$response = array();
		$response['error'] = 0;

		if (!$model->saveTaxRule(1))
		{
			$response['error'] = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['taxrule_id'] = $app->input->get('taxrule_id');
		}

		echo json_encode($response);
		$app->close();
	}
}
