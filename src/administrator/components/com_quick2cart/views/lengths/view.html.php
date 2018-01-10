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
 * View class for a list of lengths.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewLengths extends JViewLegacy
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
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();

		$this->publish_states = array(
			'' => JText::_('JOPTION_SELECT_PUBLISHED'),
			'1'  => JText::_('JPUBLISHED'),
			'0'  => JText::_('JUNPUBLISHED'),
			'-2' =>	JText::_('JTRASHED'),
			'2'  => JText::_('JARCHIVED'),
			'*'  => JText::_('JALL')
		);

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$this->sidebar = JHtmlSidebar::render();
		}

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
		$canDo = Quick2CartHelper::getActions();

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_TITLE_LENGTHS'), 'list');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_TITLE_LENGTHS'), 'lengths.png');
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/length';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('length.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('length.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('lengths.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('lengths.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'lengths.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
			}

			if (isset($this->items[0]->checked_out))
			{
				JToolBarHelper::custom('lengths.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'lengths.delete', 'JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			}
			elseif ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::trash('lengths.trash', 'JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_quick2cart');
		}

		// Set sidebar action - New in 3.0
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtmlSidebar::setAction('index.php?option=com_quick2cart&view=lengths');
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
	protected function getSortFields()
	{
		return array(
			'a.state' => JText::_('JSTATUS'),
			'a.title' => JText::_('COM_QUICK2CART_LENGTHS_LENGTH_TITLE'),
			'a.unit' => JText::_('COM_QUICK2CART_LENGTHS_LENGTH_UNIT'),
			'a.value' => JText::_('COM_QUICK2CART_LENGTHS_LENGTH_VALUE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
