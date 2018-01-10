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

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Zones list controller class.
 *
 * @since  2.2
 */
class Quick2cartControllerZones extends quick2cartController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   STRING  $name    model name
	 * @param   STRING  $prefix  model prefix
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function &getModel($name = 'Zones', $prefix = 'quick2cartModel')
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
		$pks   = $input->post->get('cid', array(), 'array');
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
	 * Change state of an item.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function publish()
	{
		$input = JFactory::getApplication()->input;
		$cid   = $input->get('cid', '', 'array');

		JArrayHelper::toInteger($cid);
		$model = $this->getModel('zones');

		if ($model->setItemState($cid, 1))
		{
			$count = count($cid);

			if ($count > 1)
			{
				$msg = JText::sprintf(JText::_('COM_QUICK2CART_ZONE_PUBLISHED'), $count);
			}
			else
			{
				$msg = JText::sprintf(JText::_('COM_QUICK2CART_ZONE_PUBLISHED'), $count);
			}
		}
		else
		{
			$msg = $model->getError();
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid              = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=zones');
		$redirect            = JRoute::_('index.php?option=com_quick2cart&view=zones&Itemid=' . $itemid, false);

		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * Change state of an item.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function unpublish()
	{
		$input = JFactory::getApplication()->input;
		$cid   = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);
		$model = $this->getModel('zones');

		if ($model->setItemState($cid, 0))
		{
			$count = count($cid);

			if ($count > 1)
			{
				$msg = JText::sprintf(JText::_('COM_QUICK2CART_ZONE_UNPUBLISHED'), $count);
			}
			else
			{
				$msg = JText::sprintf(JText::_('COM_QUICK2CART_ZONE_UNPUBLISHED'), $count);
			}
		}
		else
		{
			$msg = $model->getError();
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid              = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=zones');
		$redirect            = JRoute::_('index.php?option=com_quick2cart&view=zones&Itemid=' . $itemid, false);

		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * Method use when new zone create
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	public function delete()
	{
		$app   = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$cid   = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);

		$model        = $this->getModel('zones');
		$successCount = $model->delete($cid);

		if ($successCount)
		{
			if ($successCount >= 1)
			{
				$msg = JText::sprintf(JText::_('COM_QUICK2CART_ZONE_DELETED'), $successCount);
			}
		}
		else
		{
			$msg = JText::_('COM_QUICK2CART_ZONE_ERROR_DELETE') . '</br>' . $model->getError();
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid              = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');

		$redirect = JRoute::_('index.php?option=com_quick2cart&view=zones&Itemid=' . $itemid, false);

		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}
}
