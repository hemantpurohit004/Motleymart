<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

// Load Quick2cart Controller for list views
require_once __DIR__ . '/q2clist.php';

/**
 * Shipprofiles list controller class.
 *
 * @since  2.2
 */
class Quick2cartControllerShipprofiles extends  Quick2cartControllerQ2clist
{
	/**
	 * construcor.
	 *
	 * @param   ARRAY  $config  config
	 *
	 * @since 2.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$lang = JFactory::getLanguage();
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   STRING  $name    model name
	 * @param   STRING  $prefix  model prefix
	 *
	 * @since	1.6
	 *
	 * @return  model object
	 */
	public function &getModel($name = 'Shipprofiles', $prefix = 'Quick2cartModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
}
