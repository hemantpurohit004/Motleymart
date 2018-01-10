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
class Quick2cartControllerZone extends Quick2cartController
{
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
		$model = $this->getModel('Zone', 'quick2cartModel');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId)
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
	public function publish()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('Zone', 'quick2cartModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		// Attempt to save the data.
		$return = $model->publish($data['id'], $data['state']);

		// Check for errors.
		if ($return === false)
		{
			$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
		}
		else
		{
			// Check in the profile.
			if ($return)
			{
				$model->checkin($return);
			}

			// Clear the profile id from the session.
			$app->setUserState('com_entrusters.edit.bid.id', null);

			// Redirect to the list screen.
			$this->setMessage(JText::_('COM_QUICK2CART_ZONE_DELETED'));
		}

		// Clear the profile id from the session.
		$app->setUserState('com_quick2cart.edit.zone.id', null);

		// Flush the data from the session.
		$app->setUserState('com_quick2cart.edit.zone.data', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_QUICK2CART_ZONE_DELETED'));
		$menu =& JSite::getMenu();
		$item = $menu->getActive();

		if (empty($item))
		{
			$item->link = 'index.php?option=com_quick2cart&view=zones';
		}

		$this->setRedirect(JRoute::_($item->link, false));
	}

	/**
	 * Function used to remove zone
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	public function remove()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('Zone', 'quick2cartModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		// Attempt to save the data.
		$return = $model->delete($data['id']);

		// Check for errors.
		if ($return === false)
		{
			$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
		}
		else
		{
			// Check in the profile.
			if ($return)
			{
				$model->checkin($return);
			}

			// Clear the profile id from the session.
			$app->setUserState('com_quick2cart.edit.zone.id', null);

			// Flush the data from the session.
			$app->setUserState('com_quick2cart.edit.zone.data', null);

			$this->setMessage(JText::_('COM_QUICK2CART_ZONE_DELETED'));
		}

		// Redirect to the list screen.
		$menu =& JSite::getMenu();
		$item = $menu->getActive();

		// Code  added by sanjivani
		if (empty($item))
		{
			$item->link = 'index.php?option=com_quick2cart&view=zones';
		}

		$this->setRedirect(JRoute::_($item->link, false));
	}
}
