<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

// Load Quick2cart Controller for list views
require_once __DIR__ . '/q2clist.php';

/**
 * Products list controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerProducts extends Quick2cartControllerQ2clist
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->productHelper = new productHelper;
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   array   $config  The array of config values.
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Product', $prefix = 'Quick2cartModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * For add new
	 *
	 * @return  ''
	 *
	 * @since	2.2
	 */
	public function addnew()
	{
		$this->setRedirect('index.php?option=com_quick2cart&view=products&layout=new');
	}

	/**
	 * For Edit
	 *
	 * @return  ''
	 *
	 * @since	2.2
	 */
	public function edit()
	{
		$input = JFactory::getApplication()->input;

		// Get some variables from the request
		$cid = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);

		$quick2cartBackendProductsHelper = new quick2cartBackendProductsHelper;
		$edit_link                       = $quick2cartBackendProductsHelper->getProductLink($cid[0], 'editLink');

		$this->setRedirect($edit_link);
	}
}
