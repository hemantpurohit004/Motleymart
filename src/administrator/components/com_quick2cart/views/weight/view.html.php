<?php
/**
 * @version     1.0.0
 * @package     com_quick2cart
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      sanjivani <sanjivani_p@tekdi.net> - http://
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class Quick2cartViewWeight extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);

		if ($isNew)
		{
			$viewTitle = JText::_('COM_QUICK2CART_ADD_WEIGHT');
		}
		else
		{
			$viewTitle = JText::_('COM_QUICK2CART_EDIT_WEIGHT');
		}

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title($viewTitle, 'pencil-2');
		}
		else
		{
			JToolBarHelper::title($viewTitle, 'weight.png');
		}

		if (isset($this->item->checked_out)) {
			$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		} else {
			$checkedOut = false;
		}
		$canDo		= Quick2CartHelper::getActions();

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{

			JToolBarHelper::apply('weight.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('weight.save', 'JTOOLBAR_SAVE');
		}
		if (!$checkedOut && ($canDo->get('core.create'))){
			JToolBarHelper::custom('weight.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('weight.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('weight.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('weight.cancel', 'JTOOLBAR_CLOSE');
		}

	}
}
