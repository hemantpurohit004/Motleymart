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

jimport('joomla.application.component.controlleradmin');

/**
 * Globalattributes list controller class.
 *
 * @since  2.5
 */
class Quick2cartControllerGlobalattributes extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   STRING  $name    class name
	 *
	 * @param   STRING  $prefix  model prefix
	 *
	 * @param   STRING  $config  config
	 *
	 * @return  model object
	 *
	 * @since  2.5
	 */
	public function getModel($name = 'globalattribute', $prefix = 'Quick2cartModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	/**
	 * Function to dlete attribute
	 *
	 * @return  null
	 *
	 * @since  2.5
	 */
	public function delete()
	{
		$application = JFactory::getApplication();

		$globalAttributesModel = $this->getmodel('globalattributes');

		if ($globalAttributesModel->delete())
		{
			parent::delete();
		}

		$application->redirect("index.php?option=com_quick2cart&view=globalattributes");
	}
}
