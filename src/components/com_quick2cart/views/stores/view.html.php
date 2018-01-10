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
 * View class for a list of stores.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewStores extends JViewLegacy
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
	public function display ($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = JComponentHelper::getParams('com_quick2cart');

		// Check for errors.
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Creating status filter.
		$statuses = array();
		$statuses[] = JHtml::_('select.option', '', JText::_('COM_QUICK2CART_SELONE'));
		$statuses[] = JHtml::_('select.option', 1, JText::_('COM_QUICK2CART_PUBLISH'));
		$statuses[] = JHtml::_('select.option', 0, JText::_('COM_QUICK2CART_UNPUBLISH'));
		$this->statuses = $statuses;

		// Get itemid
		$comquick2cartHelper = new comquick2cartHelper;
		$this->createstore_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=createstore');

		// Get toolbar path
		$this->toolbar_view_path = $comquick2cartHelper->getViewpath('vendor', 'toolbar');

		// Get other vars
		$storeHelper = new storeHelper;
		$this->allowToCreateStore = $storeHelper->isAllowedToCreateNewStore();

		// Setup toolbar
		$this->addTJtoolbar();

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Setup ACL based tjtoolbar
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	protected function addTJtoolbar ()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_quick2cart/helpers/quick2cart.php';
		$canDo = Quick2cartHelper::getActions();

		// Add toolbar buttons
		jimport('techjoomla.tjtoolbar.toolbar');
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right');

		if ($canDo->get('core.create'))
		{
			if (!empty($this->allowToCreateStore))
			{
				$tjbar->appendButton('vendor.addNew', 'TJTOOLBAR_NEW', QTC_ICON_PLUS, 'class="btn btn-small btn-success"');
			}
		}

		if ($canDo->get('core.edit') && isset($this->items[0]))
		{
			$tjbar->appendButton('vendor.edit', 'TJTOOLBAR_EDIT', QTC_ICON_EDIT, 'class="btn btn-small btn-success"');
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->published))
			{
				$tjbar->appendButton('stores.publish', 'TJTOOLBAR_PUBLISH', QTC_ICON_PUBLISH, 'class="btn btn-small btn-success"');
				$tjbar->appendButton('stores.unpublish', 'TJTOOLBAR_UNPUBLISH', QTC_ICON_UNPUBLISH, 'class="btn btn-small btn-warning"');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('stores.delete', 'TJTOOLBAR_DELETE', Q2C_ICON_TRASH, 'class="btn btn-small btn-danger"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}

	/**
	 * Function To Prepare Document
	 *
	 * @return  void
	 *
	 * @since   2.7
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_QUICK2CART_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

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
}
