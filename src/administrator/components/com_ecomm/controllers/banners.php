<?php

/**
 * @package    Com_Tjnotification
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Subscriptions list controller class.
 *
 * @since  0.0.1
 */
class EcommControllerBanners extends JControllerAdmin
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
	public function getModel($name = 'Banner', $prefix = 'EcommModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
