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
 * View class for a list of payouts.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewPayouts extends JViewLegacy
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

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Quick2cartHelper::addSubmenu('payouts');

		$this->addToolbar();

		if (JVERSION >= '3.0')
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
	protected function addToolbar ()
	{
		require_once JPATH_COMPONENT . '/helpers/quick2cart.php';

		$state = $this->get('State');
		$canDo = Quick2cartHelper::getActions($state->get('filter.category_id'));

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('COM_QUICK2CART_REPORTS'), 'list');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_QUICK2CART_REPORTS'), 'payouts.png');
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/payout';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('payout.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('payout.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'payouts.delete', 'JTOOLBAR_DELETE');
			}
		}

		// CSV EXPORT
		if (!empty($this->items))
		{
			if (JVERSION >= '3.0')
			{
				JToolBarHelper::custom('payouts.csvexport', 'download', 'download', 'COM_QUICK2CART_SALES_CSV_EXPORT', false);
			}
			else
			{
				$button = "<a href='#' onclick=\"javascript:document.getElementById('task').value =" .
				" 'payouts.csvexport';document.adminForm.submit();\" >" .
				"<span class='icon-32-save' title='Export'></span>" . JText::_('COM_QUICK2CART_SALES_CSV_EXPORT') . "</a>";

				$bar = JToolBar::getInstance();
				$bar->appendButton('Custom', $button);
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_quick2cart');
		}

		if (JVERSION >= '3.0')
		{
			// Set sidebar action - New in 3.0
			JHtmlSidebar::setAction('index.php?option=com_quick2cart&view=payouts');
		}

		$this->extra_sidebar = '';
	}
}
