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
 * View to edit
 */
class Quick2cartViewZone extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$layout = $jinput->get('layout','edit');
		$model = $this->getModel('zone');

		if ($layout == 'edit')
		{
			$this->state	= $this->get('State');
			$this->item		= $this->get('Item');
			$this->form		= $this->get('Form');

			// Getting countries
			$country = $model->getCountry();
			$this->country = $country;

			// Getting zone rules
			$this->geozonerules = $model->getZoneRules();
			// Check for errors.
			if (count($errors = $this->get('Errors')))
			{
				throw new Exception(implode("\n", $errors));
			}

			$this->addToolbar();
		}
		else
		{
			$this->rule_id = $jinput->get('id');

			// Getting zone rule detail
			$this->ruleDetail = $model->getZoneRuleDetail($this->rule_id);

			if (!empty($this->ruleDetail->country_id))
			{
				// Getting Regions from country
				$this->getRegionList = $model->getRegionList($this->ruleDetail->country_id);
			}

			// Getting countries
			$country = $model->getCountry();
			$this->country = $country;
		}
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
			$viewTitle = JText::_('COM_QUICK2CART_ADD_ZONE');
		}
		else
		{
			$viewTitle = JText::_('COM_QUICK2CART_EDIT_ZONE');
		}

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title($viewTitle, 'pencil-2');
		}
		else
		{
			JToolBarHelper::title($viewTitle, 'zone.png');
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
			JToolBarHelper::apply('zone.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('zone.save', 'JTOOLBAR_SAVE');
		}
		if (!$checkedOut && ($canDo->get('core.create'))){
			JToolBarHelper::custom('zone.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		/*if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('zone.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}*/
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('zone.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('zone.cancel', 'JTOOLBAR_CLOSE');
		}

	}
}
