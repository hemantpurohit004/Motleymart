<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access.
defined('_JEXEC') or die();
jimport('joomla.application.component.view');
jimport('joomla.application.component.model');
/**
 * View class for list view of products.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewProduct extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->params              = JComponentHelper::getParams('com_quick2cart');
		$mainframe                 = JFactory::getApplication();
		$input                     = $mainframe->input;
		$this->comquick2cartHelper = new comquick2cartHelper;
		$this->productHelper       = new productHelper;
		$storeHelper               = new storeHelper;

		$this->product_types   = array();

		// Load component models
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_quick2cart/models');

		$Quick2cartModelWeights = JModelLegacy::getInstance('Weights', 'Quick2cartModel');
		$Quick2cartModelLengths = JModelLegacy::getInstance('Lengths', 'Quick2cartModel');

		$this->weightClasses = $Quick2cartModelWeights->getItems();
		$this->lengthClasses = $Quick2cartModelLengths->getItems();

		$this->product_types[1] = JHtml::_('select.option', 1, JText::_('QTC_PROD_TYPE_SIMPLE'));
		$this->product_types[2] = JHtml::_('select.option', 2, JText::_('QTC_PROD_TYPE_VARIABLE'));

		// @TODO ADD CONDITION :: LOGGED IN USER MUST HV STORE

		// Gettting store id if store is changed
		$user = JFactory::getUser();
		global $mainframe;
		$mainframe      = JFactory::getApplication();
		$change_storeto = $mainframe->getUserStateFromRequest('current_store', 'current_store', 0, 'INTEGER');

		// Get item_id from request from GET/POST
		$item_id = $mainframe->getUserStateFromRequest('item_id', 'item_id', '', 'STRING');

		// REMOVE FROM REQUEST
		$mainframe->setUserState('item_id', '');
		$this->client = $client = "com_quick2cart";
		$this->pid    = 0;

		// LOAD CART MODEL
		$Quick2cartModelcart = $this->comquick2cartHelper->loadqtcClass(JPATH_SITE . "/components/com_quick2cart/models/cart.php", "Quick2cartModelcart");

		// If item_id NOT found then SET TO ''
		$this->item_id = '';

		// If edit task then fetch item DETAILS
		if (!empty($item_id))
		{
			$input->set("content_id", $item_id);

			// Check whether called from backend
			$admin_call = $mainframe->getUserStateFromRequest('admin_call', 'admin_call', 0, 'INTEGER');

			if (!empty($admin_call))
			{
				// CHECK SPECIAL ACCESS
				$special_access = $this->comquick2cartHelper->isSpecialAccess();
			}
			// Load Attributes model
			$path        = '/components/com_quick2cart/models/attributes.php';
			$attri_model = $this->comquick2cartHelper->loadqtcClass(JPATH_SITE . $path, "quick2cartModelAttributes");

			// GET ITEM DETAIL
			$this->itemDetail = $itemDetail = $attri_model->getItemDetail(0, '', $item_id);

			// Load category_attribute_set_mapping detail
			$this->attributeSetList = $this->productHelper->getProductGlobalAttributeSet($this->itemDetail);

			// Getting attribure
			$this->item_id        = !empty($this->itemDetail) ? $itemDetail['item_id'] : '';
			$this->allAttribues   = $attri_model->getItemAttributes($this->item_id);

			$this->getMediaDetail = $this->productHelper->getMediaDetail($item_id);
			$this->isAllowedtoChangeProdCategory = $this->productHelper->isAllowedtoChangeProdCategory($item_id);

			if ($this->isAllowedtoChangeProdCategory)
			{
				$this->catName = $this->comquick2cartHelper->getCatName($this->itemDetail['category']);
			}

			$this->store_id       = $store_id = $this->store_role_list = $this->itemDetail['store_id'];

			$this->form_extra = array();
			$Quick2cartModelProduct = $this->getModel('product');

			// Call to extra fields
			$this->form_extra = $Quick2cartModelProduct->getFormExtra(
			array("category" => $this->itemDetail['category'],
				"clientComponent" => 'com_quick2cart',
				"client" => 'com_quick2cart.product',
				"view" => 'product',
				"layout" => 'new')
				);

			$this->form_extra = array_filter($this->form_extra);
		}
		else
		{
			$storeHelper    = new storeHelper;
			$storeList      = (array) $storeHelper->getUserStore($user->id);
			$this->store_id = !empty($storeList[0]['id'])?$storeList[0]['id']:'';
		}

		// IF ITEM_ID AND SPECIAL ACCESS EG ADMIN THEN FETCH STORE ID // means edit task
		// Else :
		if (!empty($item_id) && !empty($special_access))
		{
			// WE DONT WANT TO SHOW STORE SELECT LIST
			$this->store_id = $store_id = $this->store_role_list = $this->itemDetail['store_id'];
		}
		else
		{
			// As no NEED TO CHECK AUTHORIZATION AT ADMINSIDE
			$this->store_role_list = $store_role_list = $this->comquick2cartHelper->getStoreIds();
			$storeHelper           = new storeHelper;

			// Get all store ids of vendor
			$this->defaultStoreId  = $defaultStoreId = $storeHelper->getAdminDefaultStoreId();

			/*	$this->authorized_store_id = $comquick2cartHelper->store_authorize(
			"managecoupon_default",isset($change_storeto)?$change_storeto:$store_role_list[0]['store_id']);*/
			$this->store_id       = $store_id = (!empty($change_storeto)) ? $change_storeto : $defaultStoreId;
			$this->selected_store = $store_id;

			if (!$this->store_id)
			{
				$user        = JFactory::getUser();
				$storeHelper = $this->comquick2cartHelper->loadqtcClass(JPATH_SITE . "/components/com_quick2cart/helpers/storeHelper.php", "storeHelper");

				$storeList      = (array) $storeHelper->getUserStore($user->id);
				$this->store_id = $storeList[0]['id'];
			}
		}

		// ALL FETCH ALL CATEGORIES
		if (!empty($this->itemDetail['category']))
		{
			$this->cats = $this->comquick2cartHelper->getQ2cCatsJoomla($this->itemDetail['category'], 0, 'prod_cat', ' required ');
		}
		else
		{
			$this->cats = $this->comquick2cartHelper->getQ2cCatsJoomla('', 0, 'prod_cat', ' required ');
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JToolBarHelper::save($task = 'product.save', $alt = 'QTC_SAVE');
		JToolbarHelper::save('product.saveAndClose');
		JToolbarHelper::save2new('product.saveAndNew');

		JToolBarHelper::back('QTC_HOME', 'index.php?option=com_quick2cart&view=products');
		$isNew = ($this->item_id == 0);

		if ($isNew)
		{
			$viewTitle = JText::_('COM_QUICK2CART_ADD_PRODUCT');
		}
		else
		{
			$viewTitle = JText::_('COM_QUICK2CART_EDIT_PRODUCT');
		}

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title($viewTitle, 'pencil-2');
		}
		else
		{
			JToolBarHelper::title($viewTitle, 'product.png');
		}

		// Adding option btn
		JToolbarHelper::preferences('com_quick2cart');
	}
}
