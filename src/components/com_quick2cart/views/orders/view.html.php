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
 * View class for list view of products.
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
		global $mainframe, $option;
		$mainframe    = JFactory::getApplication();
		$jinput        = $mainframe->input;
		$option       = $jinput->get('option');
		$this->layout = $layout = $jinput->get('layout', 'default');
		$this->comquick2cartHelper = new comquick2cartHelper;
		$this->storeHelper         = new storeHelper;
		$this->params              = JComponentHelper::getParams('com_quick2cart');

		$orders_site       = '1';
		$this->orders_site = $orders_site;

		$model         = $this->getModel('Orders');
		$pstatus       = array();
		$pstatus[]     = JHtml::_('select.option', 'P', JText::_('QTC_PENDIN'));
		$pstatus[]     = JHtml::_('select.option', 'C', JText::_('QTC_CONFR'));
		$pstatus[]     = JHtml::_('select.option', 'RF', JText::_('QTC_REFUN'));
		$pstatus[]     = JHtml::_('select.option', 'E', JText::_('QTC_ERR'));

		/*$pstatus[]=JHtml::_('select.option','3','Cancelled');*/
		$this->pstatus = $pstatus;

		// For filter
		$sstatus   = array();
		$sstatus[] = JHtml::_('select.option', '-1', JText::_('QTC_SELONE'));

		// For store order view, there should not be pending status filter
		if ($layout != "storeorder")
		{
			// #24969
			$sstatus[] = JHtml::_('select.option', 'P', JText::_('QTC_PENDIN'));
		}

		$sstatus[]     = JHtml::_('select.option', 'C', JText::_('QTC_CONFR'));
		$sstatus[]     = JHtml::_('select.option', 'S', JText::_('QTC_SHIP'));
		$sstatus[]     = JHtml::_('select.option', 'RF', JText::_('QTC_REFUN'));
		$sstatus[]     = JHtml::_('select.option', 'E', JText::_('QTC_ERR'));
		$this->sstatus = $sstatus;

		$vendorstatus   = array();

		// Commented after discussing with DJ
		$vendorstatus[] = JHtml::_('select.option', '-1', JText::_('QTC_SEL_STATUS'));
		$vendorstatus[] = JHtml::_('select.option', 'P',  JText::_('QTC_PENDIN'));
		$vendorstatus[]     = JHtml::_('select.option', 'C', JText::_('QTC_CONFR'));
		$vendorstatus[] = JHtml::_('select.option', 'S', JText::_('QTC_SHIP'));
		$vendorstatus[] = JHtml::_('select.option', 'E', JText::_('QTC_ERR'));
		$vendorstatus[] = JHtml::_('select.option', 'RF', JText::_('QTC_REFUN'));
		$store_id       = $jinput->get('store_id');

		// NOTE :: (this is used to view order detail in vendor store product detail )
		if (!empty($store_id))
		{
			$this->store_id          = $store_id;
			$this->storeReleatedView = 1;
		}

		// Check for multivender COMPONENT PARAM
		if ($layout == "mycustomer" || $layout == "storeorder")
		{
			$isMultivenderOFFmsg = $this->comquick2cartHelper->isMultivenderOFF();

			if (!empty($isMultivenderOFFmsg))
			{
				print $isMultivenderOFFmsg;

				return false;
			}
		}

		$Itemid       = $this->comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=orders');
		$this->Itemid = $Itemid;

		if ($layout == "order" || $layout == "customerdetails")
		{
			$orderid       = $jinput->get('orderid', 0, 'integer');
			$this->orderid = $orderid;
			$jinput->set('orderid', $orderid);

			if ($layout == 'customerdetails')
			{
				$orderid               = $jinput->get('orderid', '', 'GET');
				$this->store_authorize = $store_authorize = $this->comquick2cartHelper->store_authorize("orders_customerdetails", $store_id);

				// Authorization may be depends on roll of user eg, store owner, manager,admin,front desk employee
				$order                 = $this->comquick2cartHelper->getorderinfo($orderid, $store_id);

				if (!empty($order["order_info"][0]->user_id))
				{
					$customer_id = $order["order_info"][0]->user_id;
				}
				else
				{
					if ($order["order_info"][0]->address_type == 'BT')
					{
						$customer_id = $order["order_info"][0]->user_email;
					}
					elseif ($order["order_info"][1]->address_type == 'BT')
					{
						$customer_id = $order["order_info"][1]->user_email;
					}
				}

				$this->vendorstatus = $vendorstatus;
				$pagination         = $this->get('Pagination');
				$this->pagination   = $pagination;
				$orders             = $model->getOrders(0, $customer_id);
				$this->orders       = $orders;
			}
			else
			{
				$order = $this->comquick2cartHelper->getorderinfo($orderid, $store_id);
				$guest_email   = $jinput->get('email', '', "RAW");
				$authDetail = new stdClass;
				$authDetail->store_id = $store_id;
				$authDetail->order_user_id = $this->storeHelper->getOrderUser($orderid);
				$authDetail->guest_email = $guest_email;
				$authDetail->order_id = $orderid;

				@$allowToViewOrderDetailView = $this->storeHelper->allowToViewStoreOrderDetailView($authDetail);

				if (empty($store_id))
				{
					// My order detail view mean not realeated to store view
					$this->storeReleatedView = 0;
				}
				else
				{
					$this->storeReleatedView    = 1;
				}

				if ($allowToViewOrderDetailView != 1)
				{
					echo $allowToViewOrderDetailView;

					return false;
				}
				else
				{
					$this->order_authorized = 1;
				}

				// Get order history
				$this->vendorstatus = $vendorstatus;
				$this->orderHistory = $model->getOrderHistory($orderid, $store_id);
			}

			$this->orderinfo  = $order["order_info"];
			$this->orderitems = $order["items"];

			// Get plugin name.
			if (!empty($this->orderinfo[0]->processor))
			{
				$this->paidPlgName = $this->comquick2cartHelper->getPluginName($this->orderinfo[0]->processor);
			}

			// PAYMENT
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('payment');

			// $params->get( 'gateways' ) = array('0' => 'paypal','1'=>'Payu');
			$params = JComponentHelper::getParams('com_quick2cart');

			if (!is_array($params->get('gateways')))
			{
				$gateway_param[] = $params->get('gateways');
			}
			else
			{
				$gateway_param = $params->get('gateways');
			}

			if (!empty($gateway_param))
			{
				$gateways = $dispatcher->trigger('onTP_GetInfo', array($gateway_param));
			}

			$this->gateways = $gateways;
		}
		elseif ($layout == "mycustomer")
		{
			$this->store_authorize   = $store_authorize = $this->comquick2cartHelper->store_authorize("orders_mycustomer");

			// Retrun store_id,role etc with order by role,store_id
			$this->store_role_list = $store_role_list = $this->comquick2cartHelper->getStoreIds();
			$change_storeto        = $jinput->get('change_store', '');
			$store_id              = (!empty($change_storeto)) ? $change_storeto : $store_role_list[0]['store_id'];
			$this->store_id        = $this->selected_store = $store_id;

			if (!empty($store_authorize))
			{
				$user_info       = $model->getCustomers($store_id);
				$this->user_info = $user_info;
			}

			$pagination       = $model->getPagination($model->getCustomerTotal($store_id));
			$this->pagination = $pagination;
		}
		elseif ($layout == "invoice")
		{
			// Store_releated view
			$this->storeReleatedView = 1;
			$this->store_id          = $store_id;
			$orderid               = $jinput->get('orderid', '', 'GET');
			$this->orders          = $this->comquick2cartHelper->getorderinfo($orderid, $this->store_id);
		}
		else
		{
			if ($layout == "storeorder")
			{
				// Store_releated view
				$this->vendorstatus      = $vendorstatus;
				$this->storeReleatedView = 1;
				$this->store_authorize   = $store_authorize = $this->comquick2cartHelper->store_authorize("orders_storeorder");

				// $store_id=$model->getstoreId();  // find out current user's store_id
				$this->store_role_list   = $store_role_list = $this->comquick2cartHelper->getStoreIds(); // retrun store_id,role etc with order by role,store_id

				// Store_id is changed from manage storeorder view
				$change_storeto          = $jinput->get('change_store', '');
				$store_id                = (!empty($change_storeto)) ? $change_storeto : $store_role_list[0]['store_id'];
				$this->store_id          = $this->selected_store = $store_id;
				$orders                  = array();

				if (!empty($store_id))
				{
					$orders = $model->getOrders($store_id);
				}

				$pagination       = $model->getPagination(0, $store_id);
				$this->pagination = $pagination;
			}
			else
			{
				// My orders view is not releated to store (any user can access it)
				$this->storeReleatedView = 0;
				$orders                  = $model->getOrders();

				$pagination       = $this->get('Pagination');
				$this->pagination = $pagination;
			}

			$this->orders = $orders;
		}

		$filter_order_Dir = $mainframe->getUserStateFromRequest("$option.filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$filter_type      = $mainframe->getUserStateFromRequest("$option.filter_order", 'filter_order', 'id', 'string');

		$filter_search = $mainframe->getUserStateFromRequest($option . 'filter.search', 'filter_search', '', 'string');
		$filter_state  = $mainframe->getUserStateFromRequest($option . 'search_list', 'search_list', '', 'string');
		$search        = $mainframe->getUserStateFromRequest($option . 'search_select', 'search_select', '', 'string');

		/*	$search = JString::strtolower( $search );*/

		if ($search == null)
		{
			$search = '-1';
		}

		// Search filter
		$lists['filter_search'] = $filter_search;
		$lists['search_select'] = $search;
		$lists['search_list']   = $filter_state;

		// $lists['search_gateway']		= $search_gateway;
		$lists['order_Dir']     = $filter_order_Dir;
		$lists['order']         = $filter_type;

		// Get data from the model
		$this->lists            = $lists;
		$this->addToolbar();
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
		// Added by aniket for task #25690
		$document = JFactory::getDocument();
		$jinput    = JFactory::getApplication()->input;
		$layout   = $jinput->get('layout', '');

		switch ($layout)
		{
			case 'mycustomer':
				$document->setTitle(JText::_('QTC_MYCUSTOMER_PAGE'));
				break;
			case 'storeorder':
				$document->setTitle(JText::_('QTC_STOREORDERS_PAGE'));
				break;
			case 'customerdetails':
				$document->setTitle(JText::_('QTC_CUS_DETAILS_PAGE'));
				break;
			case 'order':
				$document->setTitle(JText::_('QTC_ORDERS_PAGE'));
				break;
			case 'default':
				$document->setTitle(JText::_('QTC_ORDERS_PAGE'));
				break;
		}
	}
}
