<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
/**
 * helper class for tjnotificationss
 *
 * @package     TJnotification
 * @subpackage  com_tjnotifications
 * @since       2.2
 */

JLoader::import('way2sms-api', JPATH_ADMINISTRATOR. '/components/com_ecomm/libraries/way2sms/');

class Way2SmsHelper
{
	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    0.0.1
	 */
	function getConfigData()
	{
		// Get the way2sms account details
		$params = JComponentHelper::getParams('com_ecomm');
		$username = $params->get('way2sms_username');
		$password = $params->get('way2sms_password');

		return array('username'=>$username, 'password'=>$password);
	}

	function sendSMS($receiver, $message)
	{
		$result = false;
		$count= 0 ;

		// Get the way2sms credentials
		$credientials = $this->getConfigData();

		// If credentials found
		if (!empty($credientials) && !empty($message))
		{
			$result = $this->sendWay2SMS($credientials['username'], $credientials['password'], $receiver, $message);
		}

		foreach ($result as $message)
		{
			if ($message['result'] == 1)
			{
				$count++;
			}
		}

		if ($count == count($result))
		{
			return true;
		}
		else
		{
			return false;
		}

		return $result;
	}


	/**
	 * Helper Function to send to sms to single/multiple people via way2sms
	 * @example sendWay2SMS ( '9000012345' , 'password' , '987654321,9876501234' , 'Hello World')
	 */

	public function sendWay2SMS($uid, $pwd, $phone, $msg)
	{
		$client = new WAY2SMSClient();
		$client->login($uid, $pwd);
		$result = $client->send($phone, $msg);
		$client->logout();
		return $result;
	}

}
