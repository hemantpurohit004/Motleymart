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
 * Quick2cartControllerTaxrates Taxrates list controller class.
 *
 * @since  1.6
 */
class Quick2cartControllerTaxrates extends quick2cartController
{
	/**
	 * Method Delete.
	 *
	 * @param   String  $name    Name
	 * @param   String  $prefix  Prefix
	 *
	 * @since   2.2
	 * @return   void
	 */
	public function &getModel($name = 'Taxrates', $prefix = 'Quick2cartModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method Add.
	 *
	 * @since   2.2
	 * @return   void
	 */
	public function add()
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$this->setRedirect(JRoute::_('index.php?option=com_quick2cart&view=taxrateform&Itemid=' . $itemid, false));
	}

	/**
	 * Method Delete.
	 *
	 * @since   2.2
	 * @return   void
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$cid = $input->get('cid', '', 'array');
		$model = $this->getModel('taxrates');

		// Delete the items.
		try
		{
			JArrayHelper::toInteger($cid);
			$successCount = $model->delete($cid);

			if ($successCount)
			{
				$msg = JText::plural('COM_QUICK2CART_S_TAXRATES_DELETED_SUCCESSFULLY', $successCount);
			}
			else
			{
				$msg = JText::_('COM_QUICK2CART_S_TAXRATES_ERROR_DELETE') . '</br>' . $model->getError();
			}

			$this->setMessage($msg);
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=taxrates&Itemid=' . $itemid, false);

		$this->setMessage($msg);
		$this->setRedirect($redirect, $msg);
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
		$data = array(
				'publish' => 1,
				'unpublish' => 0
		);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');
		JArrayHelper::toInteger($cid);
		$model = $this->getModel('taxrates');

		try
		{
			$successCount = $model->setItemState($cid, $value);

			if ($successCount)
			{
				if ($value === 1)
				{
					$ntext = 'COM_QUICK2CART_N_STORES_PUBLISHED';
				}
				elseif ($value === 0)
				{
					$ntext = 'COM_QUICK2CART_N_STORES_UNPUBLISHED';
				}

				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=taxrates&Itemid=' . $itemid, false);
		$this->setRedirect($redirect);
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
}
