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
 * View class for a list of orders.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewOrders extends JViewLegacy
{
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
		$mainframe                 = JFactory::getApplication();
		$input                     = $mainframe->input;
		$option                    = $input->get('option');

		// Load language for frontend
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);

		$model = $this->getModel('Orders');

		$pstatus       = array();
		$pstatus[]     = JHtml::_('select.option', 'P', JText::_('QTC_PENDIN'));
		$pstatus[]     = JHtml::_('select.option', 'C', JText::_('QTC_CONFR'));
		$pstatus[]     = JHtml::_('select.option', 'RF', JText::_('QTC_REFUN'));
		$pstatus[]     = JHtml::_('select.option', 'S', JText::_('QTC_SHIP'));
		$pstatus[]     = JHtml::_('select.option', 'E', JText::_('QTC_ERR'));
		$pstatus[]     = JHtml::_('select.option', 'D', JText::_('Delivered'));
		$this->pstatus = $pstatus;

		$sstatus       = array();
		$sstatus[]     = JHtml::_('select.option', '-1', JText::_('COM_QUICK2CART_SELECT_APPROVAL_STATUS'));
		$sstatus[]     = JHtml::_('select.option', 'P', JText::_('QTC_PENDIN'));
		$sstatus[]     = JHtml::_('select.option', 'C', JText::_('QTC_CONFR'));
		$sstatus[]     = JHtml::_('select.option', 'RF', JText::_('QTC_REFUN'));
		$sstatus[]     = JHtml::_('select.option', 'S', JText::_('QTC_SHIP'));
		$sstatus[]     = JHtml::_('select.option', 'E', JText::_('QTC_ERR'));
		$sstatus[]     = JHtml::_('select.option', 'D', JText::_('Delivered'));
		$this->sstatus = $sstatus;

		$layout = $input->get('layout', '');

		// To change invoice design
		if ($layout == 'order' || $layout == 'invoice')
		{
			$orderid = $input->get('orderid', '');
			$store_id = $input->get('store_id', '');

			$this->orderid = $orderid;

			// Changed j Requests
			$input->set('orderid', $orderid);

			if ($layout == 'invoice')
			{
				$this->store_id          = $store_id;
				$this->storeReleatedView = 1;
				$this->orders = $orderInfo          = $this->comquick2cartHelper->getorderinfo($orderid, $store_id);
			}
			else
			{
				$this->orders = $orderInfo = $model->getorderinfo($orderid);
			}

			$this->orderinfo    = $orderInfo['order_info'];
			$this->orderitems   = $orderInfo['items'];
			$this->order_xref   = $model->getOrderXrefData($orderid);
			$this->orderHistory = $model->getOrderHistory($orderid);
		}
		else
		{
			// Added by Sagar For Gateway Filter
			$search_gateway   = $mainframe->getUserStateFromRequest($option . 'search_gateway', 'search_gateway', '', 'string');
			$search_gateway   = JString::strtolower($search_gateway);
			$filter_order_Dir = $mainframe->getUserStateFromRequest($option . "filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
			$filter_type      = $mainframe->getUserStateFromRequest($option . "filter_order", 'filter_order', 'id', 'string');

			$sstatus_gateway   = array();
			$sstatus_gateway[] = JHtml::_('select.option', '0', JText::_('QTC_FILTER_GATEWAY'));
			$gatewaylist       = $model->gatewaylist();

			if ($gatewaylist)
			{
				foreach ($gatewaylist as $key => $gateway)
				{
					$gateway_nm        = $gateway->processor;
					$this->paidPlgName = $this->comquick2cartHelper->getPluginName($gateway_nm);
					$sstatus_gateway[] = JHtml::_('select.option', $gateway_nm, $this->paidPlgName);
				}
			}

			$this->sstatus_gateway = $sstatus_gateway;

			// End Added by Sagar For Gateway Filter
			$filter_search = $mainframe->getUserStateFromRequest($option . 'filter.search', 'filter_search', '', 'string');

			$filter_state = $mainframe->getUserStateFromRequest($option . 'search_list', 'search_list', '', 'string');
			$search       = $mainframe->getUserStateFromRequest($option . 'search_select', 'search_select', '', 'string');
			$filter_state = JString::strtolower($filter_state);

			if ($filter_state == null)
			{
				$filter_state = '';
			}

			// Get data from the model
			$total      = $this->get('Total');
			$pagination = $this->get('Pagination');
			$orders     = $this->get('Orders');

			// Search filter
			$lists['filter_search']  = $filter_search;
			$lists['search_select']  = $search;
			$lists['search_list']    = $filter_state;
			$lists['search_gateway'] = $search_gateway;
			$lists['order_Dir']      = $filter_order_Dir;
			$lists['order']          = $filter_type;

			// Get data from the model
			$this->lists      = $lists;
			$this->pagination = $pagination;
			$this->orders     = $orders;
		}

		// FOR J3.0
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
	public function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/quick2cart.php';
		$canDo = Quick2cartHelper::getActions();

		$input  = JFactory::getApplication()->input;
		$layout = $input->get('layout', '');

		// If default layout
		if ($layout != 'order' && $layout != 'invoice')
		{
			if (isset($this->orders[0]))
			{
				if ($canDo->get('core.delete'))
				{
					JToolBarHelper::deleteList('', 'orders.deleteorders');
				}

				// CSV EXPORT
				if (JVERSION >= '3.0')
				{
					JToolBarHelper::custom('orders.payment_csvexport', 'download', 'download', 'COM_QUICK2CART_SALES_CSV_EXPORT', false);
				}
				else
				{
					// Get the toolbar object instance
					$bar = JToolBar::getInstance('toolbar');

					$button = "<a href='#' onclick=\"javascript:document.getElementById('task').value = 'orders.payment_csvexport';";
					$button .= " document.adminForm.submit();\" > <span class='icon-32-save' title='" . JText::_('COM_QUICK2CART_SALES_CSV_EXPORT');
					$button .= "'></span>" . JText::_('COM_QUICK2CART_SALES_CSV_EXPORT') . "</a>";

					$bar->appendButton('Custom', $button);
				}
			}

			JToolBarHelper::back('QTC_HOME', 'index.php?option=com_quick2cart');

			if (JVERSION >= '3.0')
			{
				JToolBarHelper::title(JText::_('QTC_ORDERS'), 'list');
			}
			else
			{
				JToolBarHelper::title(JText::_('QTC_ORDERS'), 'orders.png');
			}
		}
		elseif ($layout != 'invoice')
		{
			$params             = JComponentHelper::getParams('com_quick2cart');
			$multivendor_enable = $params->get('multivendor');
			JToolBarHelper::back('COM_QUICK2CART_BACK', 'index.php?option=com_quick2cart&view=orders');

			if (JVERSION >= '3.0')
			{
				JToolBarHelper::title(JText::_('COM_QUICK2CART_ORDER_TITLE'), 'list');
			}
			else
			{
				JToolBarHelper::title(JText::_('COM_QUICK2CART_ORDER_TITLE'), 'order.png');
			}
		}

		if (JVERSION >= '3.0')
		{
			// JHtmlSidebar class to render a list view sidebar
			// setAction::Set value for the action attribute of the filter form
			JHtmlSidebar::setAction('index.php?option=com_quick2cart');
		}
	}
}
