<?php
/**
 * @version    SVN: <svn_id>
 * @package    Techjoomla_API
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.plugin.plugin');

$lang = JFactory::getLanguage();
$lang->load('plg_sms_sms91', JPATH_ADMINISTRATOR);

/**
 * Class for sending sms
 *
 * @package     SMS
 * @subpackage  component
 * @since       1.0
 */
class PlgSmsSms91 extends JPlugin
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
		$this->authKey = $this->params->get('sms91_auth_key');
		$this->senderId = $this->params->get('sms91_sender_id');
		$this->useXml = $this->params->get('sms91_use_xml_format');
		$this->baseUrl = $this->params->get('sms91_base_url');
		$this->route =  $this->params->get('sms91_route');
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
		
		// Prepare you post parameters
		$postData = array(
			'authkey' => $this->authKey,
			'mobiles' => $phone,
			'message' => $message,
			'sender' => $this->senderId,
			'route' => $this->route
		);

		// API URL
		$url = $this->baseUrl . "/sendhttp.php";

		// Init the resource
		$ch = curl_init();
		
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $postData
		));

		// Ignore SSL certificate verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		// Get response
		$result['result'] = curl_exec($ch);

		// Check error if any
		if(curl_errno($ch))
		{
			$result['errors'] = curl_error($ch);
		}
		
		curl_close($ch);
		
		return $result;
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
		// Get the inputed phone nos as array by seperating them by comma(,)
		$phoneNoArray = explode(',', $phone);
		$phoneNos = array();
		
		// Iterate over each inputed no
		foreach ($phoneNoArray as $phoneNo)
		{
			// Trim the whitespaces if any
			$p = trim($phoneNo);
			
			// If not empty then push in valid nos
			if (!empty($p))
			{
				$phoneNos[] = $p;  
			}
		}
		
		// Concat with comma seperated phone nos
		$reciepents = implode(',', $phoneNos);
		
		// Encode the message
		$encodedMessage = urlencode($message);
	
		$result = $this->send($reciepents, $encodedMessage);
		
		return $result;
	}
}
