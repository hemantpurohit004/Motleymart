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
 * Class for a sales report view
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewSalesreport extends JViewLegacy
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
	public function display($tpl = null)
	{
		global $mainframe, $option;
		$comquick2cartHelper = new comquick2cartHelper;
		$mainframe           = JFactory::getApplication();
		$jinput              = $mainframe->input;
		$option              = $jinput->get('option');

		// Default layout is default
		$layout = $jinput->get('layout', 'default');
		$this->setLayout($layout);

		// SEARCH TEXT BOX VALUE
		$search = $mainframe->getUserStateFromRequest($option . 'filter_search', 'filter_search', '', 'string');
		$search = JString::strtolower($search);

		if ($search == null)
		{
			$search = '';
		}

		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_quick2cart.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_type      = $mainframe->getUserStateFromRequest('com_quick2cart.filter_order', 'filter_order', 'saleqty', 'string');

		// GET STORE DETAIL FOR FILTER
		$this->store_details = $comquick2cartHelper->getAllStoreDetails();

		// STORE FILTER search_store
		$search_store        = $mainframe->getUserStateFromRequest($option . 'search_store', 'search_store', 0, 'INTEGER');

		$model       = $this->getModel('salesreport');
		$this->items = $model->getSalesReport();

		// GET STORE DETAIL FOR FILTER
		$this->store_details = $comquick2cartHelper->getAllStoreDetails();

		$total       = $this->get('Total');
		$this->total = $total;

		$pagination       = $this->get('Pagination');
		$this->pagination = $pagination;

		// From date FILTER
		$fromDate = $mainframe->getUserStateFromRequest($option . 'salesfromDate', 'salesfromDate', '', 'RAW');

		// To date FILTER
		$toDate   = $mainframe->getUserStateFromRequest($option . 'salestoDate', 'salestoDate', '', 'RAW');

		$lists['salesfromDate'] = $fromDate;
		$lists['salestoDate']   = $toDate;
		$lists['order_Dir']     = $filter_order_Dir;
		$lists['order']         = $filter_type;
		$lists['search_store']  = $search_store;
		$lists['search']        = $search;
		$this->lists            = $lists;

		$payee_name = $mainframe->getUserStateFromRequest('com_quick2cart', 'payee_name', '', 'string');
		$this->addToolbar();

		// FOR DISPLAY SIDE FILTER
		if (JVERSION >= 3.0)
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
	protected function addToolbar()
	{
		$mainframe = JFactory::getApplication();
		$jinput    = $mainframe->input;

		$bar = JToolBar::getInstance('toolbar');

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_SALES_REPORT'), 'list');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_SALES_REPORT'), 'salesreport.png');
		}

		// FILTER FOR J3.0
		if (JVERSION >= 3.0)
		{
			JHtmlSidebar::setAction('index.php?option=com_quick2cart');
		}

		// CSV EXPORT
		if (!empty($this->items))
		{
			if (JVERSION >= '3.0')
			{
				JToolBarHelper::custom('salesreport.csvexport', 'download', 'download', 'COM_QUICK2CART_SALES_CSV_EXPORT', false);
			}
			else
			{
				$button = "<a href='#' onclick=\"javascript:document.getElementById('task').value =" .
				" 'salesreport.csvexport';document.adminForm.submit();\" >" .
				"<span class='icon-32-save' title='Export'></span>" . JText::_('COM_QUICK2CART_SALES_CSV_EXPORT') . "</a>";

				$bar->appendButton('Custom', $button);
			}
		}

		JToolBarHelper::back('QTC_HOME', 'index.php?option=com_quick2cart');
	}
}
