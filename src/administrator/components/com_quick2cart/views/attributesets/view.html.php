<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Quick2cart.
 *
 * @since  2.5
 */
class Quick2cartViewAttributesets extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  null
	 *
	 * @since  2.5
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

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  null
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/quick2cart.php';

		$state = $this->get('State');
		$canDo = Quick2cartHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_QUICK2CART_TITLE_ATTRIBUTESETS'), 'list');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/attributeset';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('attributeset.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('attributeset.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('attributesets.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('attributesets.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);

				// If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'attributesets.delete', 'JTOOLBAR_DELETE');
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_quick2cart');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_quick2cart&view=attributesets');

		$this->extra_sidebar = '';
	}

	/**
	 * function to get sorted fields
	 *
	 * @return  null
	 */
	protected function getSortFields()
	{
		return array(
		'a.`id`' => JText::_('JGRID_HEADING_ID'),
		'a.`state`' => JText::_('JSTATUS'),
		'a.`global_attribute_set_name`' => JText::_('COM_QUICK2CART_ATTRIBUTESETS_GLOBAL_ATTRIBUTE_SET_NAME'),
		);
	}
}
