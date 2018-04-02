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
class EcommApiResourceEcommFileUpload extends ApiResource
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
	 * Input is a path of image
	 *
	 * Out pot is path of image after saving it on server, Save this path in database.
	 *
	 * @return  avoid.
	 */
	public function post()
	{
		// Require helper file
		JLoader::register('EcommService', JPATH_SITE. '/administrator/components/com_ecomm/services/ecomm.php');

		$service  = new EcommService();

		// Get the request body and convert it into array
		$rawPost = file_get_contents('php://input');
		$path     = JPATH_SITE . '/tmp/';

		// Get prefix for store image name
		$params = JComponentHelper::getParams('com_ecomm');
		$store_image_prefix = $params->get ('store_image_prefix');

		// Create image name with prefix and time stamp
		$imagename = $store_image_prefix . '-' . time();

		$ext = '.png';

		$fullpath = $path . $imagename . $ext;

		$status = file_put_contents($fullpath,$rawPost);

		if($status){
				$this->returnData['success']  = 'true';
                $this->returnData['imagePath'] = $imagename.$ext;;
            } else {
                $this->returnData['message'] = 'Fails to upload image';
            }

		$result = $this->returnData;
		$this->plugin->setResponse($result);
		return true;
	}
}
