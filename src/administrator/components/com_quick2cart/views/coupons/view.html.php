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
 * View class for a list of coupons.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewCoupons extends JViewLegacy
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

		$this->addToolbar();

		$this->publish_states = array(
			'' => JText::_('JOPTION_SELECT_PUBLISHED'),
			'1'  => JText::_('JPUBLISHED'),
			'0'  => JText::_('JUNPUBLISHED')
		);

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
		$canDo = Quick2cartHelper::getActions();

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('QTC_COUPON'), 'list');
		}
		else
		{
			JToolbarHelper::title(JText::_('QTC_COUPON'), 'coupons.png');
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/coupon';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::addNew('coupon.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolbarHelper::editList('coupon.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->published))
			{
				JToolbarHelper::divider();
				JToolbarHelper::custom('coupons.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolbarHelper::custom('coupons.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				JToolbarHelper::deleteList('', 'coupons.delete', 'JTOOLBAR_DELETE');
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_quick2cart');
		}

		if (JVERSION >= '3.0')
		{
			// Set sidebar action - New in 3.0
			JHtmlSidebar::setAction('index.php?option=com_quick2cart&view=coupons');
		}

		$this->extra_sidebar = '';
	}
}
