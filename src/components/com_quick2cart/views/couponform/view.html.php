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
 * View class for editing coupon.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewCouponform extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

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
		$this->comquick2cartHelper = new comquick2cartHelper;
		$this->params              = JComponentHelper::getParams('com_quick2cart');

		// $this->state = $this->get('State');
		$this->item = $this->get('Data');

		// $this->form = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Get toolbar path
		$this->toolbar_view_path = $this->comquick2cartHelper->getViewpath('vendor', 'toolbar');

		// $this->addToolbar();
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
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user  = JFactory::getUser();
		$isNew = ($this->item->id == 0);

		if ($isNew)
		{
			$viewTitle = JText::_('COM_QUICK2CART_ADD_COUPON');
		}
		else
		{
			$viewTitle = JText::_('COM_QUICK2CART_EDIT_COUPON');
		}

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = Quick2cartHelper::getActions();

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title($viewTitle, 'pencil-2');
		}
		else
		{
			JToolbarHelper::title($viewTitle, 'coupon.png');
		}

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			JToolbarHelper::apply('coupon.apply', 'JTOOLBAR_APPLY');
			JToolbarHelper::save('coupon.save', 'JTOOLBAR_SAVE');
		}
		/*
		 * if (!$checkedOut && ($canDo->get('core.create'))){
		 * JToolbarHelper::custom('coupon.save2new', 'save-new.png',
		 * 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false); }
		 */
		/* If an existing item, can save to a copy.*/
		/*
		 * if (!$isNew && $canDo->get('core.create')) {
		 * JToolbarHelper::custom('coupon.save2copy', 'save-copy.png',
		 * 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false); }
		 */
		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('coupon.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolbarHelper::cancel('coupon.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
