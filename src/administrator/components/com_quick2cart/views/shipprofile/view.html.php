<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a Shipprofile.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewShipprofile extends JViewLegacy
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
		$this->params        = JComponentHelper::getParams('com_quick2cart');
		$comquick2cartHelper = new comquick2cartHelper;
		$zoneHelper          = new zoneHelper;

		// Check whether view is accessible to user
		if (!$zoneHelper->isUserAccessible())
		{
			return;
		}

		$qtcshiphelper = new qtcshiphelper;
		$app           = JFactory::getApplication();
		$jinput        = $app->input;
		$user          = JFactory::getUser();
		$layout        = $jinput->get('layout', 'edit');
		$model         = $this->getModel('shipprofile');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		if ($layout == 'edit')
		{
			$this->state = $this->get('State');
			$this->item  = $this->get('Data');
			$this->form  = $this->get('Form');

			// Check whether user is authorized for this zone ?
			if (!empty($this->item->store_id))
			{
				$status = $comquick2cartHelper->store_authorize('shipprofileform_default', $this->item->store_id);

				if (!$status)
				{
					$zoneHelper->showUnauthorizedMsg();

					return false;
				}
			}

			// Get store name while edit view
			if (!empty($this->item->id) && !empty($this->item->store_id))
			{
				$comquick2cartHelper = new comquick2cartHelper;
				$this->storeDetails  = $comquick2cartHelper->getSoreInfo($this->item->store_id);
				$this->shipPluglist  = $model->getShipPluginListSelect();
			}

			// Get shipping profile_id
			$shipprofile_id = $app->input->get('id', 0);

			// Getting saved tax rules.
			if (!empty($shipprofile_id))
			{
				$this->shipMethods = $model->getShipMethods($shipprofile_id);
			}

			$this->addToolbar();
		}
		else
		{
			$this->qtcShipProfileId = $jinput->get('id');
			$this->shipmethId       = $jinput->get('shipmethId', 0);
			$shipProfileDetail      = $this->shipProfileDetail = $qtcshiphelper->getShipProfileDetail($this->qtcShipProfileId);

			// Getting saved tax rules.
			if (!empty($this->shipmethId) && !empty($shipProfileDetail['store_id']))
			{
				// GET PLUGIN DETAIL
				$this->plgDetail    = $qtcshiphelper->getPluginDetailByShipMethId($this->shipmethId);
				$this->shipPluglist = $model->getShipPluginListSelect($this->plgDetail['extension_id']);

				// Get plugin shipping methods
				$qtcshiphelper  = new qtcshiphelper;
				$this->response = $qtcshiphelper->qtcLoadShipPlgMethods(
				$this->plgDetail['extension_id'], $shipProfileDetail['store_id'], $this->plgDetail['methodId']
				);
			}
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		/* Because the application sets a default page title,
		we need to get it from the menu item itself*/
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_QUICK2CART_DEFAULT_PAGE_TITLE'));
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

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$mainframe = JFactory::getApplication();
		$input     = $mainframe->input;
		$option    = $input->get('option');
		$layout    = $input->get('layout', 'edit');

		// Get the toolbar object instance.
		$bar = JToolBar::getInstance('toolbar');

		if ($layout == "edit")
		{
			$viewTitle = JText::_('COM_QUICK2CART_SHIPPROFILE');
			$isNew     = $input->get('id', 0);
			JToolBarHelper::back('QTC_HOME', 'index.php?option=com_quick2cart&view=shipprofiles');
			JToolBarHelper::save($task = 'shipprofile.save', $alt = 'QTC_SAVE');

			if ($isNew)
			{
				JToolbarHelper::save('shipprofile.saveAndClose');
			}

			JToolBarHelper::cancel($task = 'shipprofile.cancel', $alt = 'QTC_CLOSE');

			if (JVERSION >= '3.0')
			{
				JToolBarHelper::title($viewTitle, 'pencil-2');
			}
			else
			{
				JToolBarHelper::title($viewTitle, 'shipping_48.png');
			}
		}
	}
}
