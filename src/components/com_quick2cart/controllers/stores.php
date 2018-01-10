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

jimport('joomla.application.component.controlleradmin');

/**
 * Stores list controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerStores extends JControllerAdmin
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

		$this->my_stores_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=stores&layout=my');

		parent::__construct($config);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   array   $config  A named array of configuration variables.
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Store', $prefix = 'Quick2cartModel', $config = array('ignore_request' => true))
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
			JLog::add(JText::_('COM_QUICK2CART_NO_STORE_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('stores');

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
		}

		$link = JRoute::_('index.php?option=com_quick2cart&view=stores&layout=my&Itemid=' . $this->my_stores_itemid, false);

		$this->setRedirect($link);
	}

	/**
	 * Method to delete records.
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
			JLog::add(JText::_('COM_QUICK2CART_NO_STORE_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('stores');

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Delete the items.
			try
			{
				$model->delete($cid);
				$ntext = 'COM_QUICK2CART_N_STORES_DELETED';
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
			catch (Exception $e)
			{
				$this->setMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
			}
		}

		$link = JRoute::_('index.php?option=com_quick2cart&view=stores&layout=my&Itemid=' . $this->my_stores_itemid, false);
		$this->setRedirect($link);
	}

	/**
	 * Method getAllStoreProducts.
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function getAllStoreProducts()
	{
		$model = $this->getModel('store');
		$store_id = JFactory::getApplication()->input->get('storeId', '', 'INT');

		// FETCH ALL STORE PRODUCT

		$model = new Quick2cartModelstore;
		$items = $model->getAllStoreProducts('', $store_id);

		echo json_encode($items);

		jexit();
	}

	/**
	 * Method getAllProductsFromStore.
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function getAllProductsFromStore()
	{
		$store_id = JFactory::getApplication()->input->get('storeId', '', 'INT');
		$model = $this->getModel('store');

		if (!empty($store_id))
		{
			$items = $model->getAllProductsFromStore($store_id);

			$options = array();
			$options[] = JHtml::_('select.option', '', JTEXT::_('COM_QUICK2CART_SELECT_PRODUCT'));

			if (!empty($items))
			{
				foreach ($items as $item)
				{
					// This is only to generate the <option> tag inside select tag
					$options[] = JHtml::_('select.option', $item->item_id, $item->name);
				}
			}

			// Now generate the select list and echo that
			$productList = JHtml::_('select.genericlist', $options, 'qtcstorestate', ' class="qtc_store_products"', 'value', 'text');

			echo $productList;
		}

		jexit();
	}
}
