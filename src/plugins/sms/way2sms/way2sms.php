<?php
/**
 * @version    SVN: <svn_id>
 * @package    Techjoomla_API
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.plugin.plugin');

$lang = JFactory::getLanguage();
$lang->load('plg_sms_way2sms', JPATH_ADMINISTRATOR);

/**
 * Class for sending sms
 *
 * @package     SMS
 * @subpackage  component
 * @since       1.0
 */
class PlgSmsWay2sms extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   string  $subject  subject
	 * @param   array   $config   config
	 *
	 * @since   1.0
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
		$this->username = $this->params->get('way2sms_username');
		$this->password = $this->params->get('way2sms_password');
		$this->timeout = 30;
		$this->curl = '';
		$this->jstoken = '';
		$this->refurl = '';
	}

	/**
	 * Function to login to the way2sms
	 *
	 * @return  mixed  Returns true on success and error message of failure
	 *
	 * @since  1.0
	 */
	protected function login()
	{
		$this->curl = curl_init();

		if (empty($this->username))
		{
			return array("error" => JText::_('PLG_WAY2SMS_ERROR_EMPTY_USERNAME'));
		}

		if (empty($this->password))
		{
			return array("error" =>JText::_('PLG_WAY2SMS_ERROR_EMPTY_PASSWORD'));
		}

		// Get the username and password
		$username = urlencode($this->username);
		$password = urlencode($this->password);

		// Follow the server
		curl_setopt($this->curl, CURLOPT_URL, "http://way2sms.com");
		curl_setopt($this->curl, CURLOPT_HEADER, true);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		$a = curl_exec($this->curl);

		if (preg_match('#Location: (.*)#', $a, $r))
		{
			$this->way2smsHost = trim($r[1]);
		}

		// Setup for login
		$browsers = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36";
		curl_setopt($this->curl, CURLOPT_URL, $this->way2smsHost . "Login1.action");
		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, "username=" . $username . "&password=" . $password . "&button=Login");
		curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, "cookie_way2sms");
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->curl, CURLOPT_MAXREDIRS, 20);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_USERAGENT, $browsers);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($this->curl, CURLOPT_REFERER, $this->way2smsHost);
		$text = curl_exec($this->curl);

		// Check if any error occured
		if (curl_errno($this->curl))
		{
			return array("error" => JText::_('PLG_WAY2SMS_ERROR_ACCESS_ERROR') . " : " . curl_error($this->curl));
		}

		// Check for proper login
		$pos = stripos(curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL), "main.action");

		if ($pos === "FALSE" || $pos == 0 || $pos == "")
		{
			return array("error" => JText::_('PLG_WAY2SMS_ERROR_INVALID_LOGIN'));
		}

		// Set the home page from where we can send message
		$this->refurl = curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL);

		// Extract the token from the URL
		$tokenLocation = strpos($this->refurl, "Token");
		$this->jstoken = substr($this->refurl, $tokenLocation + 6, 37);

		return true;
	}

	/**
	 * Function to send the message
	 *
	 * @param   string  $phone     phone (if multiple phone numbers then comma seperated numbers)
	 * @param   string  $message   message
	 *
	 * @return  array  Returns array containing keys as phone, message and status
	 *
	 * @since  1.0
	 */
	protected function send($phone, $message)
	{
		$result = array();

		// Check the message
		if (trim($message) == "" || strlen($message) == 0)
		{
			return array("error" => JText::_('PLG_WAY2SMS_ERROR_INVALID_MESSAGE'));
		}

		// Take only the first 140 characters of the message
		$message = substr($message, 0, 140);

		// Store the numbers from the string to an array
		$phoneList = explode(",", $phone);

		// Send SMS to each number
		foreach ($phoneList as $phone)
		{
			// Check the mobile number
			if (strlen($phone) != 10 || !is_numeric($phone) || strpos($phone, ".") != false)
			{
				$result[] = array('phone' => $phone, 'message' => $message, 'result' => false, 'error' => array("error" => JText::_('PLG_WAY2SMS_ERROR_INVALID_NUMBER')));
				continue;
			}

			// Setup to send SMS
			curl_setopt($this->curl, CURLOPT_URL, $this->way2smsHost . 'smstoss.action');
			curl_setopt($this->curl, CURLOPT_REFERER, curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL));
			curl_setopt($this->curl, CURLOPT_POST, 1);

			$url = "ssaction=ss&Token=" . $this->jstoken . "&mobile=" . $phone . "&message=" . $message . "&button=Login";
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $url);
			$contents = curl_exec($this->curl);

			// Check message status
			$pos = strpos($contents, "Message has been submitted successfully");
			$res = ($pos !== false) ? true : false;
			$result[] = array('phone' => $phone, 'message' => $message, 'result' => $res);
		}

		return $result;
	}

	/**
	 * Function to logout
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	protected function logout()
	{
		curl_setopt($this->curl, CURLOPT_URL, $this->way2smsHost . "LogOut");
		curl_setopt($this->curl, CURLOPT_REFERER, $this->refurl);
		$text = curl_exec($this->curl);
		curl_close($this->curl);

		return true;
	}

	/**
	 * Functions to send SMS
	 *
	 * @param   string  $phone      phone (if multiple phone numbers then comma seperated numbers)
	 * @param   string  $message    message
	 *
	 * @return  array  Returns array containing keys as phone, message and status
	 *
	 * @since  1.0
	 */
	public function send_SMS($phone, $message)
	{
		// Login first
		$status = $this->login();

		// If no error then go ahead
		if ($status === true)
		{
			// Send the message to the phone no
			$result = $this->send($phone, $message);

			// Logout
			$this->logout();

			// Return the result
			return $result;
		}

		return $status;
	}
}
