<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2Cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . DS . 'controller.php';

jimport('joomla.application.component.controller');

/**
 * Registration Controller class.
 *
 * @package  Quick2cart
 * @since    2.7
 */
class Quick2cartControllerregistration extends JControllerLegacy
{
	/**
	 * Save
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	public function save()
	{
		$jinput  = JFactory::getApplication()->input;
		$id      = $jinput->get('cid');
		$model   = $this->getModel('registration');
		$session = JFactory::getSession();

		// Get data from request
		$post             = $jinput->get('post');
		$socialadsbackurl = $session->get('socialadsbackurl');

		// Let the model save it
		$result           = $model->store($post);

		if ($result)
		{
			$message = JText::_('REGIS_USER_CREATE_MSG');
			$itemid  = $jinput->get('Itemid');
			$user    = JFactory::getuser();
			$cart    = $session->get('cart_temp');
			$session->set('cart' . $user->id, $cart);
			$session->clear('cart_temp');
			$cart1 = $session->get('cart' . $user->id);
			$this->setRedirect($socialadsbackurl, $message);
		}
		else
		{
			$message = $jinput->get('message', '', 'STRING');
			$itemid  = $jinput->get('Itemid');
			$this->setRedirect('index.php?option=com_quick2cart&view=registration&Itemid=' . $itemid, $message);
		}
	}

	/**
	 * Cancel
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	public function cancel()
	{
		$msg    = JText::_('Operation Cancelled');
		$jinput = JFactory::getApplication()->input;
		$itemid = $jinput->get('Itemid');
		$this->setRedirect('index.php', $msg);
	}

	/**
	 * Login
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	public function login()
	{
		$jinput   = JFactory::getApplication()->input;
		$pass     = $jinput->get('qtc_password');
		$username = $jinput->get('login_user_name');
		$itemid   = $jinput->get('Itemid');
		global $mainframe;
		$mainframe = JFactory::getApplication();
		$status    = $mainframe->login(
										array(
											'username' => $username,
											'password' => $pass
										),
										array(
											'silent' => true
										)
										);

		if ($status)
		{
			$mainframe->redirect(JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&Itemid=' . $Itemid, false));
		}
		else
		{
			$massage = JText::_('Q2C_LOGIN_FAIL');
			$mainframe->redirect(JRoute::_('index.php?option=com_quick2cart&view=registration&Itemid=' . $itemid, $massage, 'alert'));
		}
	}

	/**
	 * Login
	 *
	 * @param   array    $credentials  Login detail array
	 * @param   boolean  $remember     whether to remember or not
	 * @param   string   $return       Return URL
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function userLogin($credentials, $remember = true, $return = '')
	{
		$mainframe = JFactory::getApplication();

		if (strpos($return, 'http') !== false && strpos($return, JUri::base()) !== 0)
		{
			$return = '';
		}

		$options             = array();
		$options['remember'] = (boolean) $remember;

		// $options['return'] = $return;

		$success = $mainframe->login($credentials);

		if ($return)
		{
			$mainframe->redirect($return);
		}

		return $success;
	}

	/**
	 * Guest_checkout
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	public function guest_checkout()
	{
		$jinput = JFactory::getApplication()->input;
		$itemid = $jinput->get('Itemid');
		global $mainframe;
		$mainframe = JFactory::getApplication();
		$mainframe->redirect(JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&guestckout=1&Itemid=' . $itemid, false));
	}

	/**
	 * For one page checkout As it is copied from sagar file
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	public function login_validate()
	{
		$input        = JFactory::getApplication()->input;
		$app          = JFactory::getApplication();
		$user         = JFactory::getUser();
		$itemid       = $input->get('Itemid');
		$redirect_url = JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&Itemid=' . $itemid, false);

		$json = array();

		if ($user->id)
		{
			$json['redirect'] = $redirect_url;
		}

		if (!$json)
		{
			global $mainframe;
			$mainframe = JFactory::getApplication();

			$userLoginDetail['username'] = $input->get('email', '', 'STRING');
			$userLoginDetail['password'] = $input->get('password', '', 'STRING');
			$status    = $mainframe->login($userLoginDetail, array('silent' => true));

			// Now login the user
			if (empty($status))
			{
				// If not logged in then show error msg.
				$json['error']['warning'] = JText::_('COM_QUICK2CART_ERROR_LOGIN');
			}
		}

		$json['redirect'] = $redirect_url;

		echo json_encode($json);
		$app->close();
	}

	/**
	 * Get New User data.
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	public function newUser()
	{
		$post   = JRequest::get('post');
		$input  = JFactory::getApplication()->input;
		$model  = $this->getModel('registration');
		$result = $model->newUser($post);

		echo json_encode($result);
		jexit();
	}
}
