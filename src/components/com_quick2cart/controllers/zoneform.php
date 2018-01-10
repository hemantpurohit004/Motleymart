<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Zone controller class.
 *
 * @since  1.6
 */
class Quick2cartControllerZoneForm extends Quick2cartController
{
	/**
	 * Method use when new zone create
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$app->setUserState('com_quick2cart.edit.zone.id', '');

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid              = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=zoneform&layout=edit');

		if (!empty($itemid))
		{
			$redirect = JRoute::_('index.php?option=com_quick2cart&view=zoneform&layout=edit&Itemid=' . $itemid, false);
		}
		else
		{
			$redirect = JRoute::_('index.php?option=com_quick2cart&view=zoneform&layout=edit', false);
		}

		$this->setRedirect($redirect);
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_quick2cart.edit.zone.id');
		$editId     = JFactory::getApplication()->input->getInt('id', null, 'array');

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_quick2cart.edit.zone.id', $editId);

		// Get the model.
		$model = $this->getModel('ZoneForm', 'quick2cartModel');

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
		$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=zoneform&layout=edit', false));
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 *
	 * @since    1.6
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('ZoneForm', 'quick2cartModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');
		$task = JFactory::getApplication()->input->get('task', null, '');

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
			$app->setUserState('com_quick2cart.edit.zone.data', $jform, array());

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.zone.id');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=zoneform&layout=edit&id=' . $id, false));

			return false;
		}
		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.zone.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.zone.id');
			$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=zoneform&layout=edit&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_quick2cart.edit.zone.id', null);

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid              = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=cp');

		if ($task == "save")
		{
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=zoneform&layout=edit&id=' . $return . '&Itemid=' . $itemid, false));
		}
		else
		{
			// Task= save and close
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=zones&Itemid=' . $itemid, false));
		}

		// Flush the data from the session.
		$app->setUserState('com_quick2cart.edit.zone.data', null);
	}

	/**
	 * Function used to redirect on saveAndClose
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function saveAndClose()
	{
		$this->save();
	}

	/**
	 * Function used to redirect on cancel
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function cancel()
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$itemid              = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect            = JRoute::_('index.php?option=com_quick2cart&view=zones&Itemid=' . $itemid, false);
		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * Function used to remove
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function remove()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('ZoneForm', 'quick2cartModel');

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
			$app->setUserState('com_quick2cart.edit.zone.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.zone.id');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=zone&layout=edit&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->delete($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.zone.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.zone.id');
			$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=zone&layout=edit&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_quick2cart.edit.zone.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_QUICK2CART_ZONE_DELETED'));
		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_quick2cart&view=zones' : $item->link);
		$this->setRedirect(JRoute::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_quick2cart.edit.zone.data', null);
	}

	/**
	 * This function give state/region select box.
	 *
	 * @return  void
	 *
	 * @since    2.2
	 */
	public function getStateSelectList()
	{
		$app            = JFactory::getApplication();
		$data           = $app->input->post->get('jform', array(), 'array');
		$country_id     = isset($data['country_id']) ? $data['country_id'] : 0;
		$default_option = $data['default_option'];
		$field_name     = $data['field_name'];
		$fieldid       = $data['field_id'];

		// Based on the country, get state and generate a select box
		if (!empty($country_id))
		{
			$model     = $this->getModel('zoneform');
			$stateList = $model->getRegionList($country_id);

			$options   = array();
			$options[] = JHtml::_('select.option', 0, JTEXT::_('COM_QUICK2CART_ZONE_ALL_STATES'));

			if ($stateList)
			{
				foreach ($stateList as $state)
				{
					// This is only to generate the <option> tag inside select tag da i have told n times
					$options[] = JHtml::_('select.option', $state['id'], $state['region']);
				}
			}

			// Now we must generate the select list and echo that
			$stateList = JHtml::_('select.genericlist', $options, $field_name, ' class="qtc_regionListTOpMargin"', 'value', 'text', $default_option, $fieldid);
			echo $stateList;
		}

		$app->close();
	}

	/**
	 * This function add country/region in perticular zone.
	 *
	 * @return  json
	 *
	 * @since    2.2
	 */
	public function addZoneRule()
	{
		$app   = JFactory::getApplication();
		$data  = $app->input->post->get('jform', array(), 'array');
		$model = $this->getModel('zoneform');

		$response          = array();
		$response['error'] = 0;

		if (!$model->saveZoneRule())
		{
			$response['error']        = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['zonerule_id'] = $app->input->get('zonerule_id');
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * This function Update country/region in perticular zone.
	 *
	 * @return  json
	 *
	 * @since    2.2
	 */
	public function updateZoneRule()
	{
		$app   = JFactory::getApplication();
		$data  = $app->input->post->get('jform', array(), 'array');
		$model = $this->getModel('zoneform');

		$response          = array();
		$response['error'] = 0;

		if (!$model->saveZoneRule(1))
		{
			$response['error']        = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['zonerule_id'] = $app->input->get('zonerule_id');
		}

		echo json_encode($response);
		$app->close();
	}

	/**
	 * This function delete the rule form perticular zone.
	 *
	 * @return  json
	 *
	 * @since    2.2
	 */
	public function deleteZoneRule()
	{
		$app  = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');

		$model         = $this->getModel('zoneform');
		$zoneRuleTable = $model->getTable('Zonerule');

		$response          = array();
		$response['error'] = 0;

		if (!$zoneRuleTable->delete(array($data['zonerule_id'])))
		{
			$response['error']        = 1;
			$response['errorMessage'] = $zoneRuleTable->getError();
		}

		echo json_encode($response);
		$app->close();
	}
}
