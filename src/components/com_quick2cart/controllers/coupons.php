<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

// Jimport('joomla.application.component.controlleradmin');
require_once JPATH_COMPONENT . '/controller.php';

/**
 * Coupons list controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 *
 * @since       2.2
 */
class Quick2cartControllerCoupons extends Quick2cartController
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		$comquick2cartHelper = new comquick2cartHelper;

		$this->my_coupons_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=coupons&layout=my');

		parent::__construct($config);
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
	public function getModel($name = 'Coupons', $prefix = 'Quick2cartModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Method to publish records.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function publish()
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$data = array(
			'publish' => 1,
			'unpublish' => 0
		);

		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');

		// Get some variables from the request
		if (empty($cid))
		{
			JLog::add(JText::_('COM_QUICK2CART_NO_COUPON_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				$model->setItemState($cid, $value);

				if ($value === 1)
				{
					$ntext = 'COM_QUICK2CART_N_COUPONS_PUBLISHED';
				}
				elseif ($value === 0)
				{
					$ntext = 'COM_QUICK2CART_N_COUPONS_UNPUBLISHED';
				}

				$this->setMessage(JText::plural($ntext, count($cid)));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		$link = JRoute::_('index.php?option=com_quick2cart&view=coupons&layout=my&Itemid=' . $this->my_coupons_itemid, false);

		$this->setRedirect($link);
	}

	/**
	 * Method to unpublish records.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function unpublish()
	{
		$this->publish();
	}

	/**
	 * Removes an item.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_('COM_QUICK2CART_NO_COUPON_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('coupons');

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(JText::plural('COM_QUICK2CART_N_COUPONS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		// Invoke the postDelete method to allow for the child class to access the model.
		// $this->postDeleteHook($model, $cid);

		$link = JRoute::_('index.php?option=com_quick2cart&view=coupons&layout=my&Itemid=' . $this->my_coupons_itemid, false);

		$this->setRedirect($link);
	}
}
