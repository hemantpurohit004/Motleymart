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
 * View to edit
 *
 * @since  2.5
 */
class Quick2cartViewAttributeset extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

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
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$model = $this->getModel();

		if ($this->item->id)
		{
			$this->attributeLists = $model->getAttributeListInAttributeSet($this->item->id);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$attributeList = JFormHelper::loadFieldType('attributelist', false);
		$this->attributeList = $attributeList->getInput();

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  null
	 *
	 * @since  2.5
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user = JFactory::getUser();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = Quick2cartHelper::getActions();

		JToolBarHelper::title(JText::_('COM_QUICK2CART_TITLE_ATTRIBUTESET'), 'pencil-2');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			JToolBarHelper::apply('attributeset.apply', 'JTOOLBAR_APPLY');

			if (!empty($this->item->id))
			{
				JToolBarHelper::save('attributeset.save', 'JTOOLBAR_SAVE');
			}
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			if (!empty($this->item->id))
			{
				JToolBarHelper::custom('attributeset.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
		}

		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('attributeset.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('attributeset.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
