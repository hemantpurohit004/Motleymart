<?php

/**
 * @package    Com_Tjnotification
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Feedbacks list controller class.
 *
 * @since  0.0.1
 */
class EcommControllerFeedbacks extends JControllerAdmin
{
/**
	* Proxy for getModel.
	*
	* @param   string  $name    Optional. Model name
	* @param   string  $prefix  Optional. Class prefix
	* @param   array   $config  Optional. Configuration array for model
	*
	* @return  object	The Model
	*
	* @since    1.6
	*/
	public function getModel($name = 'Feedback', $prefix = 'EcommModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
