<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Shipprofiles list controller class
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerShipprofiles extends Quick2cartController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   String  $name    Name
	 * @param   String  $prefix  Prefix
	 *
	 * @see     JController
	 * @since   1.6
	 *
	 * @return void
	 */
	public function &getModel($name = 'Shipprofiles', $prefix = 'Quick2cartModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method use to delete shipprofile.
	 *
	 * @since	2.2
	 *
	 * @return void
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$cid = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);
		$model = $this->getModel('shipprofiles');
		$successCount = $model->delete($cid);

		if ($successCount)
		{
				$msg = JText::sprintf('COM_QUICK2CART_S_SHIPPROFILE_DELETED_SUCCESSFULLY');
		}
		else
		{
			$msg = JText::_('COM_QUICK2CART_S_SHIPPROFILE_ERROR_DELETE') . '</br>' . $model->getError();
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=shipprofiles&Itemid=' . $itemid, false);

		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * This function publishes taxrate.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function publish ()
	{
		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$cid = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);
		$model = $this->getModel('shipprofiles');

		if ($model->setItemState($cid, 1))
		{
			$msg = JText::sprintf('COM_QUICK2CART_S_SHIPPROFILE_PUBLISH_SUCCESSFULLY', count($cid));
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=shipprofiles&Itemid=' . $itemid, false);
		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * This function unpublishes taxrate.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function unpublish ()
	{
		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$cid = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);
		$model = $this->getModel('shipprofiles');

		if ($model->setItemState($cid, 0))
		{
			$msg = JText::sprintf(JText::_('COM_QUICK2CART_S_SHIPPROFILE_UNPUBLISH_SUCCESSFULLY'), count($cid));
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=shipprofiles&Itemid=' . $itemid, false);
		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}
}
