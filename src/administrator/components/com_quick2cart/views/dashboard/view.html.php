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
 * View class for dashboard.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class quick2cartViewDashboard extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
		// Get download id
		$params = JComponentHelper::getParams('com_quick2cart');
		$model = $this->getModel('Dashboard');

		$this->downloadid = $params->get('downloadid');

		// Get installed version from xml file
		$xml     = JFactory::getXML(JPATH_COMPONENT . '/quick2cart.xml');

		$version = (string) $xml->version;

		$this->version = $version;

		// Refresh update site

		$model->refreshUpdateSite();

		// Get new version

		$this->latestVersion = $model->getLatestVersion();

		$mainframe = JFactory::getApplication();
		$this->addToolbar();

		// Get data from the model
		$allincome = $this->get('AllOrderIncome');
		$MonthIncome = $this->get('MonthIncome');
		$AllMonthName = $this->get('Allmonths');
		//$orderscount = $this->get('orderscount');

		$tot_periodicorderscount = $this->get('periodicorderscount');
		$this->tot_periodicorderscount = $tot_periodicorderscount;

		// Calling line-graph function
		$statsforpie= $model->statsforpie();
		$this->statsforpie = $statsforpie;

		// Get data from the model
		$this->allincome = $allincome;
		$this->MonthIncome = $MonthIncome;
		$this->AllMonthName = $AllMonthName;

		// Get box stats
		$this->productsCount = $this->get('ProductsCount');
		$this->ordersCount = $this->get('OrdersCount');
		$this->storesCount = $this->get('StoresCount');

		// Get installed version from xml file
		$xml = JFactory::getXML(JPATH_COMPONENT . DS . 'quick2cart.xml');
		$this->version = (string) $xml->version;

		// Getting  not shipped prod /order
		$params = JComponentHelper::getParams('com_quick2cart');
		$this->multivendor_enable = $multivendor_enable=$params->get('multivendor');
		$this->notShippedDetails  = $model->notShippedDetails();

		if (!empty($multivendor_enable))
		{
			$this->getpendingPayouts = $model->getpendingPayouts();
		}

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		$this->showEasySocialMsg = $this->showEasySocialMsg();

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
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('QTC_DASHBOARD'), 'dashboard');
		}
		else
		{
			JToolbarHelper::title(JText::_('QTC_DASHBOARD'), 'dashboard.png');
		}

		// Adding option btn
		JToolbarHelper::preferences('com_quick2cart');
	}

	function showEasySocialMsg()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		if ($this->Checkifinstalled('com_easysocial'))
		{
			$quick2cartproducts = JFolder::exists(JPATH_SITE . DS .'media' . DS . 'com_easysocial' . DS . 'apps' . DS . 'user' . DS . 'quick2cartproducts');

			$quick2cartstores = JFolder::exists(JPATH_SITE . DS .'media' . DS . 'com_easysocial' . DS . 'apps' . DS . 'user' . DS . 'quick2cartstores');

			if (!$quick2cartproducts || !$quick2cartstores)
			{
				// IF any of app not present then show INTEGRATION link ON dashboard
				return 1;
			}
		}

		return 0;
	}

	function Checkifinstalled($folder)
	{
		$path = JPATH_SITE . '/' . 'components' . '/' . $folder;

		if (JFolder::exists($path))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
