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

// Load Quick2cart Controller for list views
require_once __DIR__ . '/q2clist.php';

/**
 * Products list controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerProduct extends Quick2cartControllerQ2clist
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
		$this->setRedirect('index.php?option=com_quick2cart&view=product&layout=new');
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

	/**
	 * For cancel
	 *
	 * @return  ''
	 *
	 * @since	2.2
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_quick2cart&view=products');
	}

	/**
	 * For Save
	 *
	 * @param   integer  $saveClose  action
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function save($saveClose = 0)
	{
		$jinput   = JFactory::getApplication()->input;
		$cur_post = $jinput->post;
		$sku      = $cur_post->get('sku', '', "RAW");
		$sku      = trim($sku);
		global $mainframe;
		$mainframe = JFactory::getApplication();

		$current_store = $cur_post->get('current_store');

		if (!empty($current_store))
		{
			$mainframe->setUserState('current_store', $current_store);
		}

		$item_name = $jinput->get('item_name', '', 'STRING');

		// $currencydata = $cur_post['multi_cur'];
		$pid       = $jinput->get('pid', 0, 'INT');
		$client    = 'com_quick2cart';
		$stock     = $jinput->get('itemstock', '', 'INTEGER');
		$min_qty   = $jinput->get('min_item');
		$max_qty   = $jinput->get('max_item');
		$item_id   = $jinput->get('item_id', '', 'INTEGER');

		$link = JUri::base() . "index.php?option=com_quick2cart&view=product&layout=new&item_id=" . $item_id;

		if ($min_qty > $max_qty)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QUICK2CART_QUANTITY_ERROR'), 'error');

			$this->setRedirect($link);

			return false;
		}

		$cat          = $jinput->get('prod_cat', '', 'INTEGER');

		// $sku=$jinput->get('sku');
		$params       = JComponentHelper::getParams('com_quick2cart');
		$on_editor    = $params->get('enable_editor', 0);
		$youtubleLink = $jinput->get('youtube_link', '', "RAW");
		$store_id     = $jinput->get('current_store');

		// @TODO hard coded for now store // @if store id is empty then calculate from item_id
		$data         = array();

		// Get currency field count
		$multi_curArray = $cur_post->get('multi_cur', array(), 'ARRAY');
		$originalCount  = count($multi_curArray);
		$filtered_curr  = array_filter($multi_curArray, 'strlen');

		// Get currency field count after filter enpty allow 0
		$filter_count   = count($filtered_curr);

		if ($item_name && $originalCount == $filter_count)
		{
			$comquick2cartHelper = new comquick2cartHelper;
			$path                = JPATH_SITE . '/components/com_quick2cart/models/attributes.php';
			$attri_model         = $comquick2cartHelper->loadqtcClass($path, "quick2cartModelAttributes");

			// Whether have to save attributes or not
			$cur_post->set('saveAttri', 1);
			$cur_post->set('saveMedia', 1);

			$item_id = $comquick2cartHelper->saveProduct($cur_post);

			if (is_numeric($item_id))
			{
				// Load product model
				$path      = JPATH_SITE . '/components/com_quick2cart/models/product.php';
				$prodmodel = $comquick2cartHelper->loadqtcClass($path, 'quick2cartModelProduct');

				if ($saveClose == 1)
				{
					return 1;
				}

				$mainframe->setUserState('item_id', $item_id);
				$link = JUri::base() . "index.php?option=com_quick2cart&view=product&layout=new&item_id=" . $item_id;
				$this->setRedirect($link, JText::_('COM_QUICK2CART_SAVE_SUCCESS'));
			}
			else
			{
				// Save  attribute if any $msg = JText::_( 'C_SAVE_M_NS' );
				$this->setRedirect(JUri::base() . "index.php?option=com_quick2cart&view=product&layout=new", JText::_('C_SAVE_M_NS'));
			}
		}
		else
		{
			$this->setRedirect(JUri::base() . "index.php?option=com_quick2cart&view=product&layout=new", JText::_('C_FILL_COMPULSORY_FIELDS'));
		}
	}

	/**
	 * For checkSku
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function checkSku()
	{
		$jinput = JFactory::getApplication()->input;
		$sku    = $jinput->get('sku');
		$model  = $this->getModel('product');
		$itemid = $model->getItemidFromSku($sku);

		if (!empty($itemid))
		{
			echo '1';
		}
		else
		{
			echo '';
		}

		jexit();
	}

	/**
	 * For saveAndClose
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function saveAndClose()
	{
		$Quick2cartControllerProduct = new Quick2cartControllerProduct;
		$Quick2cartControllerProduct->save(1);
		$this->setRedirect(JUri::base() . "index.php?option=com_quick2cart&view=products", JText::_('COM_QUICK2CART_SAVE_SUCCESS'));
	}

	/**
	 * For save and new
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function saveAndNew()
	{
		$Quick2cartControllerProduct = new Quick2cartControllerProduct;
		$Quick2cartControllerProduct->save(1);
		$this->setRedirect(JUri::base() . "index.php?option=com_quick2cart&view=product&layout=new", JText::_('COM_QUICK2CART_SAVE_SUCCESS'));
	}

	/**
	 * Method to get globle option for global attribute
	 *
	 * @return  json formatted option data
	 *
	 * @since	2.5
	 */
	public function loadGlobalAttriOptions()
	{
		$app   = JFactory::getApplication();
		$post = $app->input->post;
		$response          = array();
		$response['error'] = 0;
		$response['goption'] = '';
		$response['errorMessage'] = '';

		$globalAttId = $post->get("globalAttId", '', "INTEGER");

		// Get global options
		$goptions = $this->productHelper->getGlobalAttriOptions($globalAttId);

		// Generate option select box
		$layout = new JLayoutFile('addproduct.attribute_global_options');
		$response['goptionSelectHtml'] = $layout->render($goptions);

		if (empty($goptions))
		{
			$response['error']        = 1;
			$response['errorMessage'] = JText::_('COM_QUICK2CART_GLOBALOPTION_NOT_FOUND');
		}
		else
		{
			$response['goption'] = $goptions;
		}

		echo json_encode($response);
		$app->close();
	}

	/**
	 * Method to save the extra fields data.
	 *
	 * @param   array  $data              data
	 * @param   array  $extra_jform_data  Extra fields data
	 * @param   INT    $item_id           Id of the record
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since  1.6
	 */
	public function saveExtraFields($data, $extra_jform_data, $item_id)
	{
		$modelProduct = $this->getModel();
		$modelProduct->saveExtraFields($data, $extra_jform_data, $item_id);
	}
}
