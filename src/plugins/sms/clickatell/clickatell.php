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
$lang->load('plg_sms_clickatell', JPATH_ADMINISTRATOR);

/**
 * Class for sending sms
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class PlgsmsClickatell extends JPlugin
{
	/**
	 * sending sms constructor
	 *
	 * @param   string  $subject  subject
	 * @param   array   $config   config
	 *
	 * @since   1.0
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
		$appUsername  = $this->params->get('appUsername');
		$appPassword  = $this->params->get('appPassword');
		$appkey       = $this->params->get('appkey');
		$this->errorlogfile = 'Clickatell_error_log.php';
		$this->user = JFactory::getUser();
		/*$this->db = JFactory::getDBO();*/
		$this->API_CONFIG = array(
		'appUsername' => trim($appUsername),
		'appPassword'    => trim($appPassword),
		'appkey'  => trim($appkey),
		);
	}

	/**
	 * Rending plugin HTML
	 *
	 * @param   string  $message  message
	 * @param   string  $vars     array data contains mobile no and other data
	 *
	 * @return  array  ticket types
	 *
	 * @since   1.0
	 */
	public function onSmsSendMessage($message, $vars = '')
	{
		// Check if keys are set
		if ($this->API_CONFIG['appkey'] == '' || $this->API_CONFIG['appUsername'] == '' || $this->API_CONFIG['appPassword'] == '')
		{
			return 0;
		}

		if (empty($message) || empty($vars->mobile_no))
		{
			return;
		}

		$user = $this->API_CONFIG['appUsername'];
		$password = $this->API_CONFIG['appPassword'];
		$api_id = $this->API_CONFIG['appkey'];
		$from = $this->params->get('from');
		$mob_flag = $this->params->get('mobileoriginated');
		$actual_Send_message = $this->send_SMS($user, $password, $api_id, $message, $vars->mobile_no, $from, $mob_flag);

		return $actual_Send_message;
	}

	/**
	 * Helper functions to send SMS
	 *
	 * @param   OBJECT  $user      USER
	 * @param   STRING  $password  Password
	 * @param   STRING  $api_id    API key
	 * @param   STRING  $text      TEXT in the SMS
	 * @param   INT     $to        Number to which SMS is to be sent
	 * @param   STRING  $from      source number
	 * @param   INT     $mob_flag  mobile oreiented flag
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	public function send_SMS($user, $password, $api_id, $text, $to, $from=0,$mob_flag=0)
	{
		$baseurl = "https://api.clickatell.com";

		// Test trext and phone number....should be pass from plugin
		$text = urlencode($text);

		// $to = 919970000526;
		// Auth URL
		$url = $baseurl . "/http/auth?user=" . $user . "&password=" . $password . "&api_id=" . $api_id;

		// Do auth call
		$ret = file($url);

		// Explode our response. return string is on first line of the data returned
		$sess = explode(":", $ret[0]);

		if ($sess[0] == "OK")
		{
			// Remove any whitespace
			$sess_id = trim($sess[1]);
			$url = $baseurl . "/http/sendmsg?user=" . $user . "&password=" . $password
			. "&api_id=" . $api_id . "&session_id=" . $sess_id . "&to=" . $to . "&text=" . $text . "&callback=6";

			if ($from)
			{
				$url .= "&from=" . $from;
			}

			if ($mob_flag == 1)
			{
				$url .= "&mo=1";
			}

			// Do sendmsg call
			$ret = file($url);
			$send = explode(":", $ret[0]);
		}
		else
		{
			echo "Authentication failure: " . $ret[0];
		}

		if ($send[0] == "ID")
		{
			$return[0] = 1;
			$return[1] = $send[1];

			return $return;
		}
		else
		{
			$return[0] = -1;
			$return[1] = $send[0] . $send[1];

			return $return;
		}
	}
}
