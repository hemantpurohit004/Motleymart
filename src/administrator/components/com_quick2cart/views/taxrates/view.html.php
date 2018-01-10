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
 * View class for a list of Quick2cart.
 */
class Quick2cartViewTaxrates extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->params = JComponentHelper::getParams('com_quick2cart');
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();

		$this->publish_states = array(
			'' => JText::_('JOPTION_SELECT_PUBLISHED'),
			'1'  => JText::_('JPUBLISHED'),
			'0'  => JText::_('JUNPUBLISHED')
			//'-2' =>	JText::_('JTRASHED'),
			//'2'  => JText::_('JARCHIVED'),
			//'*'  => JText::_('JALL')
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
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		 require_once JPATH_COMPONENT.'/helpers/quick2cart.php';

		$state	= $this->get('State');
		$canDo	= Quick2CartHelper::getActions();

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_TITLE_TAXTRATES'), 'list');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_TITLE_TAXTRATES'), 'taxrates.png');
		}

		//Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/taxrate';

		if (file_exists($formPath))
		{

			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('taxrate.add','JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('taxrate.edit','JTOOLBAR_EDIT');
			}

		}

		if ($canDo->get('core.edit.state'))
		{

			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('taxrates.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('taxrates.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			/*elseif (isset($this->items[0]))
			{
				//If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'taxrates.delete','JTOOLBAR_DELETE');
			}*/


			if (isset($this->items[0]->checked_out))
			{
				JToolBarHelper::custom('taxrates.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		//Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			/*if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'taxrates.delete','JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			}*/
			/*elseif ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::trash('taxrates.trash','JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}*/
		}

		if (isset($this->items[0]))
		{
			if ($canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'taxrates.delete', 'JTOOLBAR_DELETE');
			}
		}

		//Set sidebar action - New in 3.0
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtmlSidebar::setAction('index.php?option=com_quick2cart&view=taxrates');
			$this->extra_sidebar = '';
		}
	}

	protected function getSortFields()
	{
		return array(
			'a.state' => JText::_('JSTATUS'),
			'a.name' => JText::_('COM_QUICK2CART_TAXTRATES_TAXRATE_NAME'),
			'a.percentage' => JText::_('COM_QUICK2CART_TAXTRATES_TAX_PERCENT'),
			'a.zone_id' => JText::_('COM_QUICK2CART_TAXTRATES_ZONE_ID'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
