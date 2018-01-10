<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  1.0
 */
class Quick2cartViewZoneform extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   STRING  $tpl  template
	 *
	 * @return  view html
	 */
	public function display($tpl = null)
	{
		$zoneHelper = new zoneHelper;
		$comquick2cartHelper = new comquick2cartHelper;

		// Check whether view is accessible to user
		if (!$zoneHelper->isUserAccessible('zoneform', "default", 'form'))
		{
			return;
		}

		$app = JFactory::getApplication();
		$previousId = JFactory::getApplication()->input->get('id');
		$user = JFactory::getUser();
		$jinput = $app->input;
		$layout = $jinput->get('layout', '');
		$this->state = $this->get('State');
		$this->item = $this->get('Data');
		$this->params = $app->getParams('com_quick2cart');
		$this->form		= $this->get('Form');
		$model = $this->getModel('zoneform');

		if ($this->item)
		{
			// Getting countries
			$country = $model->getCountry();
			$this->country = $country;

			// Getting zone rules
			$this->geozonerules = $model->getZoneRules();

			// Check whether user is authorized for this zone ?
			if (!empty($this->item->store_id))
			{
				$status = $comquick2cartHelper->store_authorize('zoneform_default');

				if (!$status)
				{
					$zoneHelper->showUnauthorizedMsg();

					return false;
				}
			}
		}

		// For edit zone rules
		if ($layout === 'setrule')
		{
			$this->rule_id = $jinput->get('zonerule_id');

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
			$app->setUserState('com_quick2cart.edit.zone.id', $previousId);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return null
	 */
	protected function _prepareDocument()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_Q2C_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
