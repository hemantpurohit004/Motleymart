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

$lang = JFactory::getLanguage();

/*$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);*/

jimport('joomla.application.component.controlleradmin');

/**
 * Proucts list controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerCategory extends quick2cartController
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

		$this->my_products_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=category&layout=my');

		parent::__construct($config);
	}

	/**
	 * This function delete item / product
	 *
	 * @since	2.2
	 *
	 * @return void
	 */
	public function deleteProduct()
	{
		// Load model attribute

		/*JLoader::import('attributes', JPATH_SITE.DS.'components'.DS.'com_quick2cart'.DS.'models');
		$attrmodel =  new quick2cartModelAttributes();
		*/
		$productHelper = new productHelper;
		$jinput = JFactory::getApplication()->input;
		$item_id = $jinput->get('item_id', '', 'INTEGETR');
		$res = $productHelper->deleteWholeProduct($item_id);

		$productHelper = new productHelper;
		$productHelper->deleteNotReqProdImages($item_id, '');

		if (!empty($res))
		{
			echo 1;
		}
		else
		{
			echo 0;
		}

		jexit();
	}

	/**
	 * This function change state of products
	 *
	 * @since	2.2
	 *
	 * @return void
	 */
	public function changeState()
	{
		$prod_model = $this->getModel('product');
		$jinput = JFactory::getApplication()->input;
		$item_id = $jinput->get('item_id', '', 'INTEGETR');
		$item_id = (array) $item_id;
		$current_state = $jinput->get('current_state', 0, 'INTEGETR');

		// Find out new state
		$new_state = ($current_state == 0)?1:0;

		$prod_model->setItemState($item_id, $new_state);
		echo $new_state;
		jexit();
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
			JLog::add(JText::_('COM_QUICK2CART_NO_PRODUCT_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('category');

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				$successCount = $model->setItemState($cid, $value);

				if ($successCount)
				{
					if ($value === 1)
					{
						$ntext = 'COM_QUICK2CART_N_PRODUCTS_PUBLISHED';
					}
					elseif ($value === 0)
					{
						$ntext = 'COM_QUICK2CART_N_PRODUCTS_UNPUBLISHED';
					}

					$this->setMessage(JText::plural($ntext, count($cid)));
				}
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		$link = JRoute::_('index.php?option=com_quick2cart&view=category&layout=my&qtcStoreOwner=1&Itemid=' . $this->my_products_itemid, false);

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
	 * Method to publish records.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function delete()
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get some variables from the request
		if (empty($cid))
		{
			JLog::add(JText::_('COM_QUICK2CART_NO_PRODUCT_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('category');

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Delete the items.
			try
			{
				$successCount = $model->delete($cid);

				if ($successCount)
				{
					$ntext = 'COM_QUICK2CART_N_PRODUCTS_DELETED';
					$this->setMessage(JText::plural($ntext, count($cid)));
				}
			}
			catch (Exception $e)
			{
				$this->setMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
			}
		}

		$link = JRoute::_('index.php?option=com_quick2cart&view=category&layout=my&qtcStoreOwner=1&Itemid=' . $this->my_products_itemid, false);

		$this->setRedirect($link);
	}
}
