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
 * Taxprofiles list controller class.
 *
 * @since  1.6
 */
class Quick2cartControllerTaxprofiles extends Quick2cartController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   String  $name    Name
	 * @param   String  $prefix  Prefix
	 *
	 * @since	1.6
	 *
	 * @return  void
	 */
	public function &getModel($name = 'Taxprofiles', $prefix = 'Quick2cartModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method use to delete taxprofile.
	 *
	 * @since	1.6
	 *
	 * @return  void
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$cid = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);
		$model = $this->getModel('taxprofiles');
		$successCount = $model->delete($cid);

		if (!empty($successCount))
		{
				$msg = JText::sprintf('COM_QUICK2CART_S_TAXPROFILE_DELETED_SUCCESSFULLY');
		}
		else
		{
			$msg = JText::_('COM_QUICK2CART_S_TAXPROFILE_ERROR_DELETE') . '</br>' . $model->getError();
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=taxprofiles&Itemid=' . $itemid, false);

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
		$model = $this->getModel('taxprofiles');

		if ($model->setItemState($cid, 1))
		{
			$msg = JText::sprintf('COM_QUICK2CART_S_TAXPROFILES_PUBLISH_SUCCESSFULLY', count($cid));
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=taxprofiles&Itemid=' . $itemid, false);
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
		$model = $this->getModel('taxprofiles');

		if ($model->setItemState($cid, 0))
		{
			$msg = JText::sprintf(JText::_('COM_QUICK2CART_S_TAXPROFILES_UNPUBLISH_SUCCESSFULLY'), count($cid));
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=taxprofiles&Itemid=' . $itemid, false);
		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}
}
