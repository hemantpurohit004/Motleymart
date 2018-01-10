<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Attribute controller class.
 *
 * @since  2.5
 */
class Quick2cartControllerGlobalAttribute extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @since  2.5
	 */
	public function __construct()
	{
		$this->view_list = 'globalattributes';
		parent::__construct();
	}

	/**
	 * Function to dlete options
	 *
	 * @return  model object
	 *
	 * @since  2.5
	 */
	public function deleteoption()
	{
		$globalAttributeModel = $this->getmodel('globalattribute');
		$result = $globalAttributeModel->deleteoption();

		if ($result == false)
		{
			$message = JText::_("COM_QUICK2CART_ATTRIBUTE_OPTION_REMOVE_ERROR");
			$c[] = array("error" => $message);
		}
		else
		{
			$c[] = array("success" => 'ok');
		}

		echo json_encode($c);

		jexit();
	}
}
