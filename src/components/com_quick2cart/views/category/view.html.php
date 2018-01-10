<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * View class for a list of products.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewCategory extends JViewLegacy
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
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params     = JComponentHelper::getParams('com_quick2cart');
		$model            = $this->getModel('category');
		$this->searchkey  = $model->getState('filter.search');

		$this->product_sorting = array(
		'' => JText::_('COM_QUICK2CART_SELECT_SORTING_FILTER'),
		'PRICE_ASC'  => JText::_('COM_QUICK2CART_SORTING_PRICE_LOW_TO_HIGH'),
		'PRICE_DESC'  => JText::_('COM_QUICK2CART_SORTING_PRICE_HIGH_TO_LOW'),
		'CREATED_DESC'  => JText::_('COM_QUICK2CART_SORTING_LATEST_FIRST'),
		'CREATED_ASC'  => JText::_('COM_QUICK2CART_SORTING_OLDEST_FIRST'),
		'FEATURED'  => JText::_('COM_QUICK2CART_SORTING_FEATURED'),
		);

		$user                = JFactory::getUser();
		$this->logged_userid = $user->id;

		// Check for errors.
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $errors));
		}

		$jinput     = JFactory::getApplication()->input;

		$layout     = $jinput->get('layout', 'default', 'STRING');
		$option     = $jinput->get('option', '', 'STRING');
		$storeOwner = $jinput->get('qtcStoreOwner', 0, 'INTEGER');

		$comquick2cartHelper = new comquick2cartHelper;

		/* $client='com_quick2cart';
		$client='*';
		$products = $model->getProducts($client);
		*/

		// Get all stores.
		$this->store_details = $comquick2cartHelper->getAllStoreDetails();

		$this->categoryPage = 1;

		/*	// check for multivender COMPONENT PARAM
		$isMultivenderOFFmsg=$comquick2cartHelper->isMultivenderOFF();
		if(!empty($isMultivenderOFFmsg))
		{
		print $isMultivenderOFFmsg;
		return false;
		}*/
		// if($layout=='default')
		{
			global $mainframe;
			$mainframe = JFactory::getApplication();

			// Sstore_id is changed from  STORE view
			$change_storeto = $mainframe->getUserStateFromRequest('$option.current_store', 'current_store', '', 'INTEGER');
			$storeOwner     = $jinput->get('qtcStoreOwner', 0, 'INTEGER');
			$this->qtcShowCatStoreList = $mainframe->getParams()->get('qtcShowCatStoreList', 1);

			// FOR STORE OWNER
			if (!empty($storeOwner))
			{
				$storehelper    = new storehelper;
				$change_storeto = $storehelper->isVendorsStoreId($change_storeto);
			}

			$this->change_prod_cat = $jinput->get('prod_cat', 0, 'INTEGER');

			// Retrun store_id,role etc with order by role,store_id
			$this->store_role_list = $store_role_list = $comquick2cartHelper->getStoreIds();
			$this->store_list      = array();

			foreach ($this->store_role_list as $store)
			{
				$this->store_list[] = $store['store_id'];
			}

			$this->products = $this->items = $this->get('Items');

			// $mainframe->setUserState('$option.current_store', '0');  // VM:commentted  for store owner product view

			// When chage store,get latest storeid otherwise( on first load) set first storeid as default
			$this->store_id = $store_id = (!empty($change_storeto)) ? $change_storeto : '';

			$pagination       = $model->getPagination();

			// ALL FETCH ALL CATEGORIES
			$this->cats       = $comquick2cartHelper->getQ2cCatsJoomla($this->change_prod_cat);
			$this->pagination = $pagination;

			// Added by Sneha
			$filter_state         = $mainframe->getUserStateFromRequest($option . 'search_list', 'search_list', '', 'string');
			$lists['search_list'] = $filter_state;
			$this->assignRef('lists', $lists);

			// End added by Sneha
			$this->_setToolBar();
		}

		// Get toolbar path
		$this->toolbar_view_path = $comquick2cartHelper->getViewpath('vendor', 'toolbar');

		if ($layout == 'my')
		{
			if (!$this->logged_userid)
			{
				$msg = JText::_('QTC_LOGIN');
				$uri = JFactory::getApplication()->input->get('REQUEST_URI', '', 'server', 'string');
				$url = base64_encode($uri);
				$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url), $msg);
			}

			// Creating status filter.
			$statuses       = array();
			$statuses[]     = JHtml::_('select.option', '', JText::_('COM_QUICK2CART_SELONE'));
			$statuses[]     = JHtml::_('select.option', 1, JText::_('COM_QUICK2CART_PUBLISH'));
			$statuses[]     = JHtml::_('select.option', 0, JText::_('COM_QUICK2CART_UNPUBLISH'));
			$this->statuses = $statuses;

			// Setup toolbar
			$this->addTJtoolbar();
		}

		$this->_prepareDocument();
		$this->productPageTitle = $this->getTitle();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  null
	 *
	 * @since  2.0
	 */
	protected function _prepareDocument()
	{
		global $mainframe;
		$mainframe = JFactory::getApplication();
		$menus     = $mainframe->getMenu();
		$menu      = $menus->getActive();
		$title     = null;

		// Getting menu Param
		$menuParam = $mainframe->getParams();

		/* Because the application sets a default page title,
		 we need to get it from the menu item itself
		 @TODO Need to uncomment this when a menu for single product item can be created.*/
		/*
		$menu = $menus->getActive();

		if($menu)
		{
		$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
		$this->params->def('page_heading', JText::_('QTC_PRODUCTPAGE_PAGE'));
		}

		$title = $this->params->get('page_title', '');
		*/

		// @TODO Need to comment this if when a menu for single product item can be created.
		// Getting menu Param
		$menuParam = $mainframe->getParams();
		$title     = $menuParam->get('page_title');

		if (empty($title))
		{
			$title = $mainframe->getCfg('sitename');
		}
		elseif ($mainframe->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $mainframe->getCfg('sitename'), $title);
		}
		elseif ($mainframe->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $mainframe->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		// Setting meta description
		$meta_description = $menuParam->get('metadesc');

		if ($meta_description)
		{
			$meta_description = $menuParam->get('metadesc');
		}
		elseif ($menuParam->get('menu-meta_description'))
		{
			$meta_description = $menuParam->get('menu-meta_description');
		}

		$this->document->setDescription($meta_description);

		// Setting meta_keywords
		$meta_keywords = $menuParam->get('menu-meta_keywords');
		$this->document->setMetadata('keywords', $meta_keywords);

		if ($menuParam->get('robots'))
		{
			$this->document->setMetadata('robots', $menuParam->get('robots'));
		}
	}

	/**
	 * Function to se tool bar
	 *
	 * @return  toolbar
	 *
	 * @since 2.0
	 *
	 * */
	public function _setToolBar()
	{
		// Added by aniket for task #25690
		$document = JFactory::getDocument();

		global $mainframe;
		$mainframe = JFactory::getApplication();

		/*$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_quick2cart/assets/css/quick2cart.css');
		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::title( JText::_('QTC_SETT'), 'icon-48-quick2cart.png');
		JToolBarHelper::save('save',JText::_('QTC_SAVE') );
		JToolBarHelper::cancel('cancel', JText::_('QTC_CLOSE') );*/
	}

	/**
	 * Setup ACL based tjtoolbar
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	protected function addTJtoolbar()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_quick2cart/helpers/quick2cart.php';
		$canDo = Quick2cartHelper::getActions();

		// Add toolbar buttons
		jimport('techjoomla.tjtoolbar.toolbar');
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right');

		if ($canDo->get('core.create'))
		{
			$tjbar->appendButton('product.addNew', 'TJTOOLBAR_NEW', QTC_ICON_PLUS, 'class="btn btn-small btn-success"');
		}

		if ($canDo->get('core.edit') && isset($this->items[0]))
		{
			$tjbar->appendButton('product.edit', 'TJTOOLBAR_EDIT', QTC_ICON_EDIT, 'class="btn btn-small btn-success"');
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				$tjbar->appendButton('category.publish', 'TJTOOLBAR_PUBLISH', QTC_ICON_PUBLISH, 'class="btn btn-small btn-success"');
				$tjbar->appendButton('category.unpublish', 'TJTOOLBAR_UNPUBLISH', QTC_ICON_UNPUBLISH, 'class="btn btn-small btn-warning"');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('category.delete', 'TJTOOLBAR_DELETE', Q2C_ICON_TRASH, 'class="btn btn-small btn-danger"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}

	/**
	 * Function to get title for product page according to category selected
	 *
	 * @return  product page title
	 *
	 * @since  2.5
	 * */
	public function getTitle()
	{
		$input = JFactory::getApplication()->input;

		// Get cat from URL
		$prod_cat = $input->get('prod_cat', '', 'int');

		// Get all Quick2cart categorys in array
		$all_categorys = JHtml::_('category.options', 'com_quick2cart', array('filter.published' => array(1)));

		$app       = JFactory::getApplication();

		// Load the JMenuSite Object
		$menu      = $app->getMenu();

		// Load the Active Menu Item as an stdClass Object
		$activeMenuItem    = $menu->getActive();

		// If product category not found in URL then assign product category according menu
		if (empty($prod_cat) && !empty($activeMenuItem))
		{
			$prod_cat = $activeMenuItem->params->get('defaultCatId', '', 'INT');
		}

		$flag = 0;
		$lagend_title = '';

		foreach ($all_categorys as $cats)
		{
			if ($prod_cat == $cats->value)
			{
				$lagend_title = $cats->text;
				$lagend_title = str_replace("-", "", $lagend_title);
				$flag = 1;
			}
		}

		if ($flag == 0)
		{
			if ($activeMenuItem == null)
			{
				$lagend_title = "QTC_PRODUCTS_CATEGORY_ALL_BLOG_VIEW";
			}
			else
			{
				$lagend_title = $activeMenuItem->title;
			}
		}

		return ucfirst($lagend_title);
	}
}
