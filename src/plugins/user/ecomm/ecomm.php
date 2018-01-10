<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * An example custom profile plugin.
 *
 * @since  1.6
 */
class PlgUserEcomm extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was succesfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				// Delete register OTP
				$db->setQuery(
					'DELETE FROM #__ecomm_mobile_otp_map WHERE user_id = ' . $userId
				);
				$db->execute();

				// Delete reset password OTP
				$db->setQuery(
					'DELETE FROM #__ecomm_mobile_otp_map_reset_password WHERE user_id = ' . $userId
				);
				$db->execute();

				// Delete rating
				$db->setQuery(
					'DELETE FROM #__ecomm_ratings WHERE user_id = ' . $userId
				);
				$db->execute();

				// Delete subscription
				$db->setQuery(
					'DELETE FROM #__ecomm_users WHERE user_id = ' . $userId
				);
				$db->execute();

				// Delete address
				$db->setQuery(
					'DELETE FROM #__kart_customer_address WHERE user_id = ' . $userId
				);
				$db->execute();
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}
}
