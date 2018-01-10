<?php
/**
 * @package     Joomla.API.Plugin
 * @subpackage  com_tjlms-API
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
jimport('joomla.user.helper');

/**
 * API Plugin
 *
 * @package     Joomla_API_Plugin
 * @subpackage  com_tjlms-API-create course
 * @since       1.0
 */
class EcommApiResourceEcommSendSms extends ApiResource
{
	/**
	 * API Plugin for get method
	 *
	 * @return  avoid.
	 */
	public function get()
	{
		$this->plugin->setResponse("Please Use Post method");
	}

	/**
	 * API Plugin for post method
	 *
	 * @return  avoid.
	 */
	public function post()
	{
		// Require helper file
		JLoader::register('EcommService', JPATH_SITE. '/administrator/components/com_ecomm/services/ecomm.php');

		$service  = new EcommService();

		// Get the request body and convert it into array
		$inputData = json_decode(file_get_contents('php://input'), true);

		// Get the required data
		$receiver = $inputData['receiver'];
		$message = $inputData['message'];

		$result = $service->ecommSendSms($receiver, $message);

		$this->plugin->setResponse($result);
		return true;
	}
}
