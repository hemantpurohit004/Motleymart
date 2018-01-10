<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  2.5
 */
class Quick2cartViewDelaysreport extends JViewLegacy
{
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
		global $mainframe, $option;
		$comquick2cartHelper = new comquick2cartHelper;
		$mainframe           = JFactory::getApplication();
		$option              = JFactory::getApplication()->input->get('option');

		// Default layout is default
		$layout = JRequest::getVar('layout', 'default');
		$this->setLayout($layout);
		$model = $this->getModel('delaysreport');

		$sstatus   = array();
		$sstatus[] = JHtml::_('select.option', 'C', JText::_('QTC_CONFR'));
		$sstatus[] = JHtml::_('select.option', 'S', JText::_('QTC_SHIP'));

		// $sstatus[] = JHtml::_('select.option','P',  JText::_('QTC_PENDIN'));
		$sstatus[] = JHtml::_('select.option', 'E', JText::_('QTC_ERR'));
		$this->assignRef('sstatus', $sstatus);

		$delay   = array();
		$delay[] = JHtml::_('select.option', 1, JText::_('QTC_ONE'));
		$delay[] = JHtml::_('select.option', 2, JText::_('QTC_TWO'));
		$delay[] = JHtml::_('select.option', 5, JText::_('QTC_FIVE'));
		$delay[] = JHtml::_('select.option', 10, JText::_('QTC_TEN'));
		$delay[] = JHtml::_('select.option', 25, JText::_('QTC_TWENTYFIVE'));

		// $delay[]=JHtml::_('select.option',50, JText::_('QTC_FIFTY'));
		$this->assignRef('delay', $delay);

		if ($layout == 'default')
		{
			// SEARCH TEXT BOX VALUE
			$search = $mainframe->getUserStateFromRequest($option . 'search', 'search', '', 'string');
			$search = JString::strtolower($search);

			if ($search == null)
			{
				$search = '';
			}

			$filter_order_Dir = $mainframe->getUserStateFromRequest('com_quick2cart.filter_order_Dir', 'filter_order_Dir', '', 'word');
			$filter_type      = $mainframe->getUserStateFromRequest('com_quick2cart.filter_order', 'filter_order', '', 'string');
			$search_store     = $mainframe->getUserStateFromRequest($option . 'search_store', 'search_store', 0, 'INTEGER');
			$status           = $mainframe->getUserStateFromRequest($option . 'search_select', 'search_select', '', 'string');
			$delayday         = $mainframe->getUserStateFromRequest($option . 'search_select_delay', 'search_select_delay', '', 'INTEGER');

			$model                 = $this->getModel('delaysreport');
			$this->getDelaysReport = $getDelaysReport = $model->getDelaysReport();
			$this->getDelaysReport = $getDelaysReport;

			$total       = $this->get('Total');
			$this->total = $total;

			$pagination       = $this->get('Pagination');
			$this->pagination = $pagination;

			// From date FILTER
			$fromDate = $mainframe->getUserStateFromRequest($option . 'salesfromDate', 'salesfromDate', '', 'RAW');

			// To date FILTER
			$toDate   = $mainframe->getUserStateFromRequest($option . 'salestoDate', 'salestoDate', '', 'RAW');

			$lists['salesfromDate']       = $fromDate;
			$lists['salestoDate']         = $toDate;
			$lists['order_Dir']           = $filter_order_Dir;
			$lists['order']               = $filter_type;

			// $lists['search']      = $search;
			$lists['search_select']       = $status;
			$lists['search_select_delay'] = $delayday;
			$lists['search_list']         = $search;
			$this->lists                  = $lists;
		}

		$payee_name = $mainframe->getUserStateFromRequest('com_quick2cart', 'payee_name', '', 'string');

		//  $lists['payee_name']=$payee_name;

		$this->_setToolBar();

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
	 * @return  null
	 */
	public function _setToolBar()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'components/com_quick2cart/css/quick2cart.css');
		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::title(JText::_('QTC_DELAY_ORDERS_REPORT'), 'icon-48-quick2cart.png');

		$layout = JRequest::getVar('layout', 'default');

		if ($layout == "default")
		{
			// JToolBarHelper::cancel( 'cancel', 'Close' );
			// FILTER FOR J3.0
			if (JVERSION >= 3.0)
			{
				// JHtmlSidebar class to render a list view sidebar //setAction::Set value for the action attribute of the filter form
				JHtmlSidebar::setAction('index.php?option=com_quick2cart');
				$serSel = JHtml::_('select.options', $this->sstatus, "value", "text", $this->lists['search_select'], true);
				JHtmlSidebar::addFilter(JText::_('QTC_SELONE'), 'search_select', $serSel);

				$days = JHtml::_('select.options', $this->delay, "value", "text", $this->lists['search_select_delay'], true);
				JHtmlSidebar::addFilter(JText::_('QTC_DAYS'), 'search_select_delay', $days);
			}

			// CSV EXPORT
			if (JVERSION >= 3.0)
			{
				JToolBarHelper::custom('csvexport', 'icon-32-save.png', 'icon-32-save.png', JText::_("COM_QUICK2CART_SALES_CSV_EXPORT"), false);
			}
			else
			{
				$button = "<a href='#' onclick=\"javascript:document.getElementById('task').value =" .
				" 'csvexport';document.getElementById('controller').value =" .
				" 'salesreport';document.adminForm.submit();\" ><span class='icon-32-save' title='Export'></span>" .
				JText::_('COM_QUICK2CART_SALES_CSV_EXPORT') . "</a>";
				$bar->appendButton('Custom', $button);
			}
		}
	}
}
