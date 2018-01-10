<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2Cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();
jimport('joomla.application.component.model');
jimport('joomla.database.table.user');

/**
 * Registration model class.
 *
 * @package  Quick2cart
 * @since    2.7
 */
class Quick2cartModelregistration extends JModelLegacy
{
	/**
	 * Class constructor.
	 *
	 * @since   2.7
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
	}

	/**
	 * Method to store a client record
	 *
	 * @param   Array  $data  User Information Data
	 *
	 * @return  boolean true/false
	 *
	 * @since   2.7
	 */
	public function store($data)
	{
		global $mainframe;
		$mainframe = JFactory::getApplication();
		$jinput    = $mainframe->input;
		$id        = $jinput->get('cid');
		$session   = JFactory::getSession();
		$db        = JFactory::getDBO();

		// Send array from ckout model
		$user_email = $data['user_email'];
		$user_name  = $data['user_name'];

		$user = JFactory::getUser();

		if (!$user->id)
		{
			$Quick2cartModelregistration = new Quick2cartModelregistration;
			$query                       = "SELECT id FROM #__users WHERE email = '" . $user_email . "' or username = '" . $user_name . "'";
			$this->_db->setQuery($query);
			$userexist = $this->_db->loadResult();
			$userid    = "";
			$randpass  = "";

			if (!$userexist)
			{
							// Generate the random password & create a new user
							$randpass = $this->rand_str(6);
							$userid   = $this->createnewuser($data, $randpass);
			}
			else
			{
				$message = JText::_('USER_EXIST');
				$jinput->set('message', $message);

				return false;
			}

			if ($userid)
			{
				JPluginHelper::importPlugin('user');

				if (!$userexist)
				{
					$Quick2cartModelregistration->SendMailNewUser($data, $randpass);
				}

				$user                    = array();
				$remCk                   = $jinput->get('remember', false, 'BOOLEAN');
				$options                 = array('remember' => $remCk);

				// Tmp user details

				$user                    = array();
				$user['username']        = $data['user_name'];
				$options['autoregister'] = 0;
				$user['email']           = $user_email;
				$user['password']        = $randpass;
				$mainframe->login(
									array(
									'username' => $data['user_name'],
									'password' => $randpass
									),
									array(
											'silent' => true
											)
											);
			}
		}

		return true;
	}

	/**
	 * Create user
	 *
	 * @param   Array   $data      User Information
	 * @param   String  $randpass  Password
	 *
	 * @return  boolean true/false
	 *
	 * @since   2.7
	 */
	public function createnewuser($data, $randpass)
	{
		global $message;
		jimport('joomla.user.helper');
		$app = JFactory::getApplication();

		$authorize = JFactory::getACL();
		$user      = clone JFactory::getUser();
		$user->set('username', $data['user_name']);
		$user->set('password1', $randpass);
		$user->set('name', $data['user_name']);
		$user->set('email', $data['user_email']);

		// Password encryption
		$salt           = JUserHelper::genRandomPassword(32);
		$crypt          = JUserHelper::getCryptedPassword($user->password1, $salt);
		$user->password = "$crypt:$salt";

		// User group/type
		$user->set('id', '');
		$user->set('usertype', 'Registered');

		if (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			$userConfig       = JComponentHelper::getParams('com_users');

			// Default to Registered.
			$defaultUserGroup = $userConfig->get('new_usertype', 2);
			$user->set('groups', array($defaultUserGroup));
		}
		else
		{
			$user->set('gid', $authorize->get_group_id('', 'Registered', 'ARO'));
		}

		$date = JFactory::getDate();
		$user->set('registerDate', $date->toSQL());

		// True on success, false otherwise
		if (!$user->save())
		{
			echo $message = JText::_('COM_QUICK2CART_UNABLE_TO_CREATE_USER_BZ_OF') . $user->getError();

			return false;
		}
		else
		{
			$message = JText::sprintf('COM_QUICK2CART_CREATED_USER_AND_SEND_ACCOUNT_DETAIL_ON_EMAIL', $user->username);
		}

		$app->enqueueMessage($message);

		return $user->id;
	}

	/**
	 * Randam Password
	 *
	 * @param   Integer  $length  Length
	 * @param   String   $chars   Character
	 *
	 * @return  String
	 *
	 * @since   2.7
	 */
	public function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
		// Length of character list
		$chars_length = (strlen($chars) - 1);

		// Start our string
		$string = $chars{rand(0, $chars_length)};

		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			// Grab a random character from our list
			$r = $chars{rand(0, $chars_length)};

			// Make sure the same two characters don't appear next to each other
			if ($r != $string{$i - 1})
			{
				$string .= $r;
			}
		}

		// Return the string
		return $string;
	}

	/**
	 * Send Email To user.
	 *
	 * @param   Array   $data      User Information Data
	 * @param   String  $randpass  Password
	 *
	 * @return  Boolean
	 *
	 * @since   2.7
	 */
	public function SendMailNewUser($data, $randpass)
	{
		$app      = JFactory::getApplication();
		$mailfrom = $app->getCfg('mailfrom');
		$fromname = $app->getCfg('fromname');
		$sitename = $app->getCfg('sitename');

		$email    = $data['user_email'];
		$subject  = JText::_('SA_REGISTRATION_SUBJECT');
		$find1    = array(
						'{sitename}'
		);
		$replace1 = array(
						$sitename
		);
		$subject  = str_replace($find1, $replace1, $subject);

		$message = JText::_('SA_REGISTRATION_USER');
		$find    = array(
						'{firstname}',
						'{sitename}',
						'{register_url}',
						'{username}',
						'{password}'
		);
		$replace = array(
						$data['user_name'],
						$sitename,
						JUri::root(),
						$data['user_name'],
						$randpass
		);
		$message = str_replace($find, $replace, $message);

		JFactory::getMailer()->sendMail($mailfrom, $fromname, $email, $subject, $message);
		$messageadmin = JText::_('SA_REGISTRATION_ADMIN');
		$find2        = array(
						'{sitename}',
						'{username}'
		);
		$replace2     = array(
						$sitename,
						$data['user_name']
		);
		$messageadmin = str_replace($find2, $replace2, $messageadmin);

		JFactory::getMailer()->sendMail($mailfrom, $fromname, $mailfrom, $subject, $messageadmin);

		return true;
	}

	/**
	 * Get New User data.
	 *
	 * @param   Array  $data  User Information Data
	 *
	 * @return  Integer  $userid
	 *
	 * @since   2.7
	 */
	public function newUser($data)
	{
		global $mainframe;
		$mainframe = JFactory::getApplication();
		$jinput    = $mainframe->input;

		$user_data       = array();
		$user_data['id'] = null;

		$user_data['user_name']  = $data['username'];
		$user_data['password']   = $data['password1'];
		$user_data['user_name']  = $data['name'];
		$user_data['user_email'] = $data['emailid'];

		$user_email = $user_data['user_email'];
		$user_name  = $user_data['user_name'];

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select($db->qn(array('id')))
			->from($db->qn('#__users'))
			->where($db->qn('#__users.email') . ' = ' . $db->quote($user_email))
			->where($db->qn('#__users.username') . ' = ' . $db->quote($user_name));

		$db->setQuery($query);
		$userexist = $this->_db->loadResult();

		$userid    = "";
		$randpass  = "";

		if (!$userexist)
		{
			// Generate the random password & create a new user
			$randpass = $user_data['password'];
			$userid   = $this->createnewuser($user_data, $randpass);

			if ($userid)
			{
				$result = $this->SendMailNewUser($user_data, $randpass);
			}
		}

		return $result;
	}
}
