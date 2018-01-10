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

jimport('joomla.application.component.view');

/**
 * View class for a list of taxrates.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewTaxrates extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$zoneHelper = new zoneHelper;

		// Check whether view is accessible to user
		if (!$zoneHelper->isUserAccessible())
		{
			return;
		}

		$app = JFactory::getApplication();
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = $app->getParams('com_quick2cart');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Get toolbar path
		$comquick2cartHelper = new comquick2cartHelper;
		$this->toolbar_view_path = $comquick2cartHelper->getViewpath('vendor', 'toolbar');

		// Publish states
		$this->publish_states = array(
			'' => JText::_('JOPTION_SELECT_PUBLISHED'),
			'1'  => JText::_('JPUBLISHED'),
			'0'  => JText::_('JUNPUBLISHED')
		);

		// Setup TJ toolbar
		$this->addTJtoolbar();

		$this->_prepareDocument();

		parent::display($tpl);
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
		$app = JFactory::getApplication();
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
			$this->params->def('page_heading', JText::_('COM_QUICK2CART_SETUP_TAXRATES'));
		}

		$title = $this->params->get('page_title', JText::_('COM_QUICK2CART_SETUP_TAXRATES'));

		// @TODO - hack * remove line below -when correct itemid is passed for this view
		$title = JText::_('COM_QUICK2CART_SETUP_TAXRATES');
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
			$tjbar->appendButton('taxrates.add', 'TJTOOLBAR_NEW', QTC_ICON_PLUS, 'class="btn btn-small btn-success"');
		}

		/*if ($canDo->get('core.edit') && isset($this->items[0]))
		{
			$tjbar->appendButton('taxrateform.edit', 'TJTOOLBAR_EDIT', '', 'btn btn-small btn-success');
		}*/

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				$tjbar->appendButton('taxrates.publish', 'TJTOOLBAR_PUBLISH', QTC_ICON_PUBLISH, 'class="btn btn-small btn-success"');
				$tjbar->appendButton('taxrates.unpublish', 'TJTOOLBAR_UNPUBLISH', QTC_ICON_UNPUBLISH, 'class="btn btn-small btn-warning"');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('taxrates.delete', 'TJTOOLBAR_DELETE', Q2C_ICON_TRASH, 'class="btn btn-small btn-danger"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}
}
