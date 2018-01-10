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

		$this->addToolbar();

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		// Creating status filter.
		$statuses = array();
		$statuses[] = JHtml::_('select.option', '', JText::_('JOPTION_SELECT_PUBLISHED'));
		$statuses[] = JHtml::_('select.option', 1, JText::_('JPUBLISHED'));
		$statuses[] = JHtml::_('select.option', 0, JText::_('JUNPUBLISHED'));
		$this->statuses = $statuses;

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
		require_once JPATH_COMPONENT . '/helpers/quick2cart.php';

		$state = $this->get('State');
		$canDo = Quick2cartHelper::getActions();

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_TITLE_STORES'), 'cart');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_TITLE_STORES'), 'stores.png');
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/store';

		//@TODO use JForm for store creation
		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('store.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('store.edit', 'JTOOLBAR_EDIT');
			}
		}
		else
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('vendor.addNew', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('vendor.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->published))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('stores.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('stores.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			/*elseif (isset($this->items[0]))
			{
				//If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'stores.delete', 'JTOOLBAR_DELETE');
			}*/

			/*if (isset($this->items[0]->published))
			{
				JToolBarHelper::divider();
				JToolBarHelper::archiveList('stores.archive', 'JTOOLBAR_ARCHIVE');
			}*/

			if (isset($this->items[0]->checked_out))
			{
				JToolBarHelper::custom('stores.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		/*
		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->published))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'stores.delete','JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			}
			elseif ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::trash('stores.trash','JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}*/

		if (isset($this->items[0]))
		{
			if ($canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'stores.delete', 'JTOOLBAR_DELETE');
			}
		}

		JToolBarHelper::back('QTC_HOME', 'index.php?option=com_quick2cart');

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_quick2cart');
		}

		if (JVERSION >= '3.0')
		{
			// Set sidebar action
			JHtmlSidebar::setAction('index.php?option=com_quick2cart&view=stores');
		}

		$this->extra_sidebar = '';
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields ()
	{
		return array(
			'a.title' => JText::_('STORE_TITLE'),
			'published' => JText::_('JSTATUS'),
			'u.name' => JText::_('VENDOR_NAME'),
			'a.store_email' => JText::_('STORE_EMAIL'),
			'a.phone' => JText::_('STORE_PHONE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
