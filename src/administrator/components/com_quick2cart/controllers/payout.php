<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

/**
 * Payout form controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerPayout extends JControllerForm
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
		parent::__construct($config);
		$this->view_list = 'payouts';
	}

	// @TODO - remove this when jform is used
	/**
	 * function to add
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function add()
	{
		$this->setRedirect('index.php?option=com_quick2cart&view=payout&layout=edit');
	}

	// @TODO - remove this when jform is used
	/**
	 * function to cancel.
	 *
	 * @param   STRING  $key  key
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function cancel($key = null)
	{
		$this->setRedirect('index.php?option=com_quick2cart&view=payouts');
	}

	// @TODO - remove this when jform is used
	/**
	 * function to edit.
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function edit()
	{
		$input = JFactory::getApplication()->input;

		// Get some variables from the request
		$cid = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);

		if (! count($cid))
		{
			$id  = $input->get('id', '', 'INT');
			$link = 'index.php?option=com_quick2cart&view=payout&layout=edit&id=' . $id;
		}
		else
		{
			$link = 'index.php?option=com_quick2cart&view=payout&layout=edit&id=' . $cid[0];
		}

		$this->setRedirect($link);
	}

	/**
	 * Overrides parent save method.
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
		$model = $this->getModel('Payout', 'Quick2cartModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get->post;

		// Attempt to save the data.
		$return = $model->save($data);
		$id = $return;

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_quick2cart.edit.payout.data', $data);

			// Tweak *important.
			$app->setUserState('com_quick2cart.edit.payout.id', $data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_quick2cart.edit.payout.id');
			$this->setMessage(JText::sprintf('COM_QUICK2CART_SAVE_MSG_ERROR', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&&view=payout&layout=edit&id=' . $id, false));

			return false;
		}

		// Tweak *important.
		$app->setUserState('com_quick2cart.edit.payout.id', $data->get('id', '', 'INT'));

		if ($task === 'apply')
		{
			if (!$id)
			{
				$id = (int) $app->getUserState('com_quick2cart.edit.payout.id');
			}

			$redirect = 'index.php?option=com_quick2cart&task=payout.edit&id=' . $id;
		}
		else
		{
			// Clear the profile id from the session.
			$app->setUserState('com_quick2cart.edit.payout.id', null);

			// Flush the data from the session.
			$app->setUserState('com_quick2cart.edit.payout.data', null);

			// Redirect to the list screen.
			$redirect = JRoute::_('index.php?option=com_quick2cart&view=payouts', false);
		}

		$msg = JText::_('COM_QUICK2CART_SAVE_SUCCESS');
		$this->setRedirect($redirect, $msg);
	}
}
