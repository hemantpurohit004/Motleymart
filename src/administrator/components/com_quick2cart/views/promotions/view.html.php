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
 * View class for a list of Quick2cart.
 *
 * @since  1.6
 */
class Quick2cartViewPromotions extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->comquick2cartHelper = new comquick2cartHelper;
		$storeHelper = $this->comquick2cartHelper->loadqtcClass(JPATH_SITE . "/components/com_quick2cart/helpers/storeHelper.php", "storeHelper");
		$this->comquick2cartHelper->loadqtcClass(JPATH_ADMINISTRATOR . "/components/com_quick2cart/models/products.php", 'Quick2cartModelProducts');
		$this->Quick2cartModelProducts = new Quick2cartModelProducts;

		$user = JFactory::getUser();
		$userId = $user->get('id');
		$this->storeList = (array) $storeHelper->getUserStore($userId);

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Setup toolbar
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Method to order fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'a.`id`' => JText::_('JGRID_HEADING_ID'),
			'a.`state`' => JText::_('COM_QUICK2CART_PROMOTIONS_PUBLISHED'),
			'a.`name`' => JText::_('COM_QUICK2CART_PROMOTIONS_NAME'),
			'a.`description`' => JText::_('COM_QUICK2CART_PROMOTIONS_DESCRIPTION'),
			'a.`from_date`' => JText::_('COM_QUICK2CART_PROMOTIONS_FROM_DATE'),
			'a.`exp_date`' => JText::_('COM_QUICK2CART_PROMOTIONS_EXP_DATE'),
			'a.`store_id`' => JText::_('COM_QUICK2CART_PROMOTIONS_STORE_ID'),
		);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = Quick2cartHelper::getActions();

		JToolBarHelper::title(JText::_('COM_QUICK2CART_TITLE_PROMOTIONS'), 'list');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/promotion';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('promotion.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('promotion.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('promotions.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('promotions.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'promotions.delete', 'JTOOLBAR_DELETE');
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'promotions.delete', 'JTOOLBAR_DELETE');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_quick2cart');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_quick2cart&view=promotions');

		$this->extra_sidebar = '';
	}
}
