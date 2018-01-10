<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Shipping profiles.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewShipprofiles extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   String  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
		$zoneHelper = new zoneHelper;
		$app        = JFactory::getApplication();

		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params     = JComponentHelper::getParams('com_quick2cart');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Publish states
		$this->publish_states = array(
			'' => JText::_('JOPTION_SELECT_PUBLISHED'),
			'1' => JText::_('JPUBLISHED'),
			'0' => JText::_('JUNPUBLISHED')
		);

		$comquick2cartHelper = new comquick2cartHelper;

		// Get all stores.
		$user        = JFactory::getUser();
		$storeHelper = new storeHelper;

		$this->storeFilters[] = array(
			'id' => 0,
			'title' => JText::_('COM_QUICK2CART_COUPONFORM_STORE_SELECT')
		);

		/*$this->stores = $comquick2cartHelper->getAllStoreDetails();*/
		$userStores           = $storeHelper->getUserStore($user->id);
		$this->stores         = array_merge($this->storeFilters, $userStores);

		// Setup toolbar
		$this->addToolbar();
		$this->_prepareDocument();

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_QUICK2CART_SHIPPROFILE'));
		}

		$title = $this->params->get('page_title', JText::_('COM_QUICK2CART_SHIPPROFILE'));

		// @TODO - hack * remove line below -when correct itemid is passed for this view
		$title = JText::_('COM_QUICK2CART_SHIPPROFILE');
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
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
		require_once JPATH_COMPONENT . '/helpers/quick2cart.php';

		$state = $this->get('State');
		$canDo = Quick2cartHelper::getActions();

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_SHIPPROFILE'), 'list');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_SHIPPROFILE'), 'shipping_48.png');
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/shipprofile';

		// @TODO use JForm for shipprofile creation
		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('shipprofile.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('shipprofile.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom('shipprofile.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('shipprofile.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}

		if (isset($this->items[0]))
		{
			if ($canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'shipprofile.delete', 'JTOOLBAR_DELETE');
			}
		}

		$this->extra_sidebar = '';
	}
}
