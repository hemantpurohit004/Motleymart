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

jimport('joomla.application.component.model');

// @TODO rewrite entire model after v2.3
/**
 * ORder Model class.
 *
 * @since  1.6
 */
class Quick2cartModelOrders extends JModelLegacy
{
	public $data;

	public $total = null;

	public $pagination = null;

	public $store_id = null;

	public $customer_count = null;

	private $params;

	private $comquick2cartHelper = null;

	public $productHelper = null;

	/**
	 * Constructor
	 *
	 * @since    1.6
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$this->siteMainHelper = new comquick2cartHelper;

		// Get the pagination request variables
		$limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option . 'limitstart', 'limitstart', 0, 'int');

		// Set the limit variable for query later on
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get component Parameters
		$this->params = JComponentHelper::getParams('com_quick2cart');

		// Declared component helper object
		$this->comquick2cartHelper = new comquick2cartHelper;
		$this->productHelper       = new productHelper;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @param   integer  $store_id     store id.
	 * @param   integer  $customer_id  customer id.
	 *
	 * @return  query
	 *
	 * @since   1.6
	 */
	public function _buildQuery($store_id = 0, $customer_id = 0)
	{
		$db = JFactory::getDBO();

		// Get the WHERE and ORDER BY clauses for the query
		$where = '';
		$where = $this->_buildContentWhere($store_id, $customer_id);
		$query = "SELECT i.processor, i.amount, i.cdate, i.payee_id, i.status, i.id, i.prefix, i.email, i.currency, u.name, u.username
		 FROM #__kart_orders AS i
		 LEFT JOIN #__users AS u ON u.id = i.payee_id" . $where;

		global $mainframe, $option;
		$mainframe        = JFactory::getApplication();
		$jinput           = $mainframe->input;
		$option           = $jinput->get('option');
		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'cdate', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		if ($filter_order)
		{
			$qry1 = "SHOW COLUMNS FROM #__kart_orders";
			$db->setQuery($qry1);
			$exists1 = $db->loadobjectlist();

			foreach ($exists1 as $key1 => $value1)
			{
				$allowed_fields[] = $value1->Field;
			}

			if (in_array($filter_order, $allowed_fields))
			{
				$query .= " ORDER BY $filter_order $filter_order_Dir";
			}
		}

		return $query;
	}

	/**
	 * Prepare for where conditions
	 *
	 * @param   integer  $store_id     store id.
	 * @param   integer  $customer_id  customer id.
	 *
	 * @since   2.2
	 * @return  void
	 */
	public function _buildContentWhere($store_id = 0, $customer_id = 0)
	{
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$jinput    = $mainframe->input;
		$option    = $jinput->get('option');
		$db        = JFactory::getDBO();

		$filter_search = $mainframe->getUserStateFromRequest($option . 'filter.search', 'filter_search', '', 'string');

		$filter_state = $mainframe->getUserStateFromRequest($option . 'search_list', 'search_list', '', 'string');
		$search       = $mainframe->getUserStateFromRequest($option . 'search_select', 'search_select', '', 'string');

		// For Filter Based on Gateway
		$search_gateway = '';
		$search_gateway = $mainframe->getUserStateFromRequest($option . 'search_gateway', 'search_gateway', '', 'string');
		$search_gateway = JString::strtolower($search_gateway);

		// For Filter Based on Gateway
		$where = array();

		if ($mainframe->getName() == 'site')
		{
			$user = JFactory::getuser();

			if (!empty($store_id))
			{
				$order_ids = $this->getOrderIds($store_id);
				$order_ids = (!empty($order_ids) ? $order_ids : 0);
				$where[]   = "i.id IN ( " . $order_ids . ")";
			}
			elseif (!empty($customer_id))
			{
				if (is_numeric($customer_id))
				{
					$where[] = "i.payee_id = " . $customer_id;
				}
				else
				{
					$where[] = "i.email LIKE '" . $customer_id . "'";
				}
			}
			else
			{
				$where[] = "i.payee_id = " . $user->id;
			}
		}

		if ($search_gateway)
		{
			$where[] = " (i.processor LIKE '" . $search_gateway . "')";
		}

		// Hack By Nitesh -Added D status
		if ($search == 'P' || $search == 'C' || $search == 'RF' || $search == 'S' || $search == 'E' || $search == 'D')
		{
			$where[] = 'i.status = ' . $this->_db->Quote($search);
		}

		if ($filter_state)
		{
			$where[] = " UPPER( CONCAT( i.prefix, i.id )) LIKE UPPER('%" . trim($filter_state) . "%')";
		}

		if ($filter_search)
		{
			$where[] = "i.email LIKE '%" . $filter_search . "%'" . "
			OR CONCAT (i.prefix,i.id) LIKE '%" . $filter_search . "%'" . "
			OR i.prefix LIKE '%" . $filter_search . "%'" . "
			OR u.name LIKE '%" . $filter_search . "%'" . "
			OR u.username LIKE '%" . $filter_search . "%'";
		}

		return $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
	}

	/**
	 * Give you order list accordig to pagination
	 *
	 * @param   integer  $store_id     store id.
	 * @param   integer  $customer_id  customer id.
	 *
	 * @since   2.2
	 * @return  void
	 */
	public function getOrders($store_id = 0, $customer_id = 0)
	{
		if (empty($this->data))
		{
			$query       = $this->_buildQuery($store_id, $customer_id);
			$this->data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->data;
	}

	/**
	 * Give you getTotal
	 *
	 * @param   integer  $store_id  store id.
	 *
	 * @since   2.2
	 * @return  void
	 */
	public function getTotal($store_id = 0)
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->total))
		{
			$query        = $this->_buildQuery($store_id);
			$this->total = $this->_getListCount($query);
		}

		return $this->total;
	}

	/**
	 * Give you pagination object
	 *
	 * @param   integer  $count     Pagination count
	 * @param   integer  $store_id  store id.
	 *
	 * @since   2.2
	 * @return  void
	 */
	public function getPagination($count = 0, $store_id = 0)
	{
		// Lets load the content if it doesn’t already exist // NOTE :: COUNT PRESENT MEAN->CALLING FROM MYCUSTOMER VIEW
		if (empty($this->pagination) || $count)
		{
			if (empty($count))
			{
				$count = $this->getTotal($store_id);
			}

			jimport('joomla.html.pagination');
			$this->pagination = new JPagination($count, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}

	/**
	 * This function update the order status with note.
	 *
	 * If escaping mechanism is either htmlspecialchars or htmlentities, uses
	 * {@link $_encoding} setting.
	 *
	 * @param   integer  $store_id  if we are updating store product status
	 *
	 * @return  integer  if 1 = success 	2= error 	3 = refund order
	 *
	 * @since   2.2
	 */
	public function store($store_id = 0)
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$lang                = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
		$returnvaule = 1;
		$jinput      = JFactory::getApplication()->input;
		$layout = $jinput->get("layout");
		$status = $jinput->get('status');

		$data       = $jinput->post;
		$orderId         = $data->get('id');
		$notify_chk = $data->get('notify_chk' . '|' . $orderId, '');

		if (isset($notify_chk) && $notify_chk != null)
		{
			$notify_chk = 1;
		}
		else
		{
			$notify_chk = 0;
		}

		// $comment = $data->get('comment', '', 'STRING');
		// Save order history

		$add_note_chk = $data->get('add_note_chk' . '|' . $orderId, '');
		$note         = $data->get('order_note' . '|' . $orderId, '', "STRING");
		$comquick2cartHelper->updatestatus($orderId, $status, $note, $notify_chk, $store_id);

		if ($status == 'RF')
		{
			$returnvaule = 3;
		}

		// Get order item
		if ($layout == "order")
		{
			$orderItemsStr = $data->get("orderItemsStr", '', "STRING");
			$orderItems = explode("||", $orderItemsStr);
		}
		else
		{
			$orderItems = $this->getOrderItems($orderId);
		}

		// Save order history
		foreach ($orderItems as $oitemId)
		{
			// Save order item status history
			$this->comquick2cartHelper->saveOrderStatusHistory($orderId, $oitemId, $jinput->get('status'), $note, $notify_chk);
		}

		return $returnvaule;
	}

	/**
	 * Return order item ids list
	 *
	 * @param   string  $orderid  order_id.
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function getOrderItems($orderid)
	{
		if ($orderid)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('order_item_id');
			$query->from('#__kart_order_item AS oi');
			$query->where("oi.order_id= " . $orderid);
			$db->setQuery($query);

			return $orderList = $db->loadColumn();
		}
	}

	/**
	 * This function gives you gatewaylist.
	 *
	 * @since   2.0
	 *
	 * @return   list.
	 */
	public function gatewaylist()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT DISTINCT(`processor`) FROM #__kart_orders";
		$db->setQuery($query);
		$gatewaylist = $db->loadObjectList();

		if (!$gatewaylist)
		{
			return 0;
		}
		else
		{
			return $gatewaylist;
		}
	}

	/**
	 * This function delete the Order.
	 *
	 * @param   integer  $odid  order id.
	 *
	 * @since   2.0
	 *
	 * @return   store id.
	 */
	public function delete($odid)
	{
		$odid_str = implode(',', $odid);

		// Get Order item ids
		$db    = JFactory::getDBO();
		$query = "SELECT order_item_id FROM #__kart_order_item WHERE order_id IN (" . $odid_str . ")";
		$db->setQuery($query);
		$order_itemList = $db->loadColumn();

		if (!empty($order_itemList))
		{
			$order_itemStr = implode(',', $order_itemList);

			// Del order item files.
			$query = "DELETE FROM #__kart_orderItemFiles where order_item_id IN (" . $order_itemStr . ")";
			$this->_db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}

			$query = "DELETE FROM #__kart_order_itemattributes where order_item_id IN (" . $order_itemStr . ")";
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		$query = "DELETE FROM #__kart_orders where id IN (" . $odid_str . ")";
		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		$query = "DELETE FROM #__kart_order_item where order_id IN (" . $odid_str . ")";
		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		$query = "DELETE FROM #__kart_users where order_id IN (" . $odid_str . ")";
		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// START Q2C Sample development
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result = $dispatcher->trigger('onQuick2cartAfterOrderDelete', array($odid));

		// Depricated
		$result = $dispatcher->trigger('Onq2cOrderDelete', array($odid));

		return true;
	}

	/**
	 * This function gives OrderIds.
	 *
	 * @param   integer  $store_id  storeid.
	 *
	 * @since   2.0
	 *
	 * @return   store id.
	 */
	public function getOrderIds($store_id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT DISTINCT `order_id` FROM `#__kart_order_item` where `store_id`=" . $store_id;
		$db->setQuery($query);
		$ids = $db->loadColumn();

		return implode(',', $ids);
	}

	/**
	 * This function gives getCustomers.
	 *
	 * @param   integer  $store_id  storeid.
	 *
	 * @since   2.0
	 *
	 * @return   store id.
	 */
	public function getCustomers($store_id)
	{
		$db    = JFactory::getDBO();
		$query = $this->buildCustomer($store_id);

		if (!empty($query))
		{
			$this->data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

			return $this->data;
		}
		else
		{
			return;
		}
	}

	/**
	 * This function build buildCustomer
	 *
	 * @param   integer  $store_id  storeid.
	 *
	 * @since   2.0
	 *
	 * @return   store id.
	 */
	public function buildCustomer($store_id)
	{
		$db        = JFactory::getDBO();
		$order_ids = $this->getOrderIds($store_id);
		$query     = "";

		if (!empty($order_ids))
		{
			$query = "select * from (SELECT  u.* FROM  `#__kart_orders` AS o
				LEFT JOIN  `#__kart_users` AS u ON o.`email` = u.`user_email`
				WHERE u.`address_type` =  'BT' AND o.id=u.order_id AND u.`order_id` IN (" . $order_ids . " ) order by u.id  DESC
				) AS newtb ";

			global $mainframe, $option;
			$mainframe        = JFactory::getApplication();
			$jinput           = $mainframe->input;
			$option           = $jinput->get('option');
			$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'firstname', 'cmd');
			$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
			$filter_state     = $mainframe->getUserStateFromRequest($option . 'search_list', 'search_list', '', 'string');

			// NOTE:: 1. FIND ALL WHERE AND APPEND TO QUERY
			if (!empty($filter_state))
			{
				$where = " WHERE `firstname` LIKE \"%" . $filter_state . "%\" ";
				$query .= $where;
			}

			// NOTE:: 2. USE GROUP BY IF ANY
			$groupby = " group by newtb.user_email ";
			$query .= $groupby;

			// NOTE:: 3. USE FILTER
			if ($filter_order)
			{
				$comquick2cartHelper = new comquick2cartHelper;
				$allowed_fields      = $comquick2cartHelper->getColumns('#__kart_users');

				if (in_array($filter_order, $allowed_fields))
				{
					$query .= " ORDER BY $filter_order $filter_order_Dir";
				}
			}
		}

		return $query;
	}

	/**
	 * This function get getCustomerTotal
	 *
	 * @param   integer  $store_id  storeid.
	 *
	 * @since   2.0
	 *
	 * @return   store id.
	 */
	public function getCustomerTotal($store_id)
	{
		$query = $this->buildCustomer($store_id);

		if (!empty($query))
		{
			return $this->customer_count = $this->_getListCount($query);
		}
		else
		{
			return;
		}
	}

	/**
	 * This function get all product list and and its final price against store_id,order_id
	 *
	 * @param   integer  $storeids  storeid.
	 * @param   integer  $orderid   order id.
	 *
	 * @since   2.2.7
	 *
	 * @return   store id.
	 */
	public function getStore_items($storeids, $orderid)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT `order_item_name`,`product_final_price` from `#__kart_order_item` where `store_id`=" . $storeids . " AND order_id=" . $orderid;
		$db->setQuery($query);

		return $result = $db->loadObjectList();
	}

	/**
	 * Get getStore_product_price
	 *
	 * @param   integer  $storeids  storeid.
	 * @param   integer  $orderid   order id.
	 *
	 * @since   2.2.7
	 *
	 * @return   store id.
	 */
	public function getStore_product_price($storeids, $orderid)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT SUM(`product_final_price`) AS store_items_price from `#__kart_order_item` where `store_id`=" .
		$storeids . " AND order_id=" . $orderid;
		$db->setQuery($query);

		return $result = $db->loadResult();
	}

	/**
	 * Get owner's store id
	 *
	 * @since   2.2.7
	 *
	 * @return   store id.
	 */
	public function getstoreId()
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDBO();
		$query = "select id from `#__kart_store` where `owner`=" . $user->id;
		$db    = $db->setQuery($query);

		return $storeid = $db->loadResult($query);
	}

	/**
	 * Amol change : Function to resend invoice : For now called it from backend order details view
	 *
	 * @since   2.2.7
	 *
	 * @return   status.
	 */
	public function resendInvoice()
	{
		$app    = JFactory::getApplication();
		$jinput = $app->input;
		$post   = $jinput->post;

		$orderid    = $jinput->get('orderid', '', 'INT');
		$notify_chk = 1;
		$store_id = $jinput->get('store_id', '', 'INT');

		$db    = JFactory::getDBO();
		$query = "SELECT o.status FROM #__kart_orders as o WHERE o.id =" . $orderid;
		$db->setQuery($query);
		$order_oldstatus = $db->loadResult();

		$useinvoice = $this->params->get('useinvoice', '1');
		$comment    = $post->get('comment', '', 'STRING');

		if ($useinvoice == '1' && ($order_oldstatus == 'C' || $order_oldstatus == 'S'))
		{
			$this->comquick2cartHelper->sendInvoice($orderid, $order_oldstatus, $comment, $notify_chk, $store_id);
		}
		else
		{
			echo JText::_("COM_QUICK2CART_INVOICE_SENDING_FAILED_REASON");

			// $app->enqueueMessage(JText::_("COM_QUICK2CART_INVOICE_SENDING_FAILED_REASON", true), "error");

			return false;
		}

		return true;
	}

	/**
	 * Amol change : Save order history
	 *
	 * @since   2.2.7
	 *
	 * @return   status.
	 */
	public function saveOrderData()
	{
		$jinput = JFactory::getApplication()->input;
		$data   = $jinput->post;
		$orderid = $jinput->get('orderid', '', 'INT');

		// Trigger after edit campaign
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$dispatcher->trigger('onAfterQ2COrderSave', array($orderid, $data));

		return true;
	}

	/**
	 * Amol change : Get order xref data: This allow you to save extra information which should not go to order's extra column. Such as mso no. etc
	 * order's extra column holds only payment releated data (currenlty).
	 *
	 * @param   integer  $orderid  order id.
	 *
	 * @since   2.2.7
	 *
	 * @return   object.
	 */
	public function getOrderXrefData($orderid)
	{
		if (empty($orderid))
		{
			return;
		}

		$query = $this->_db->getQuery(true);
		$query->select("`extra`");
		$query->from('#__kart_orders_xref');
		$query->where(" order_id = " . $orderid);
		$this->_db->setQuery($query);

		if (!empty($this->_db->loadObject()->extra))
		{
			return $result = json_decode($this->_db->loadObject()->extra);
		}

		return false;
	}

	/**
	 * Amol change : Get order items info. This is used for order edit from backend
	 *
	 * @param   integer  $orderid  order id.
	 *
	 * @since   2.2.7
	 *
	 * @return   object.
	 */
	public function getorderinfo($orderid)
	{
		// Get Order Info
		$order = $this->comquick2cartHelper->getorderinfo($orderid);

		// Get item attribute details
		foreach ($order['items'] as $key => $item)
		{
			$productHelper              = new productHelper;

			// Get cart items attribute details
			$item->prodAttributeDetails = $productHelper->getItemCompleteAttrDetail($item->item_id);
			$product_attributes = explode(',', $item->product_attributes);

			/* E.g data
			 *
			 *  [prodAttributeDetails] => Array
			 (
			 [0] => stdClass Object
			 (
			 [itemattribute_id] => 93
			 [itemattribute_name] => Colors3
			 [attribute_compulsary] => 1
			 [attributeFieldType] => Select
			 [optionDetails] => Array
			 (
			 [0] => stdClass Object
			 (
			 [itemattributeoption_id] => 231
			 [itemattributeoption_name] => Red
			 [itemattributeoption_price] => 0.00
			 [itemattributeoption_prefix] => +
			 [ordering] => 1
			 [itemattribute_id] => 93
			 [USD] => 0.00
			 )
			 */

			foreach ($item->prodAttributeDetails as $optionDetails)
			{
				foreach ($optionDetails->optionDetails as $option)
				{
					if (in_array($option->itemattributeoption_id, $product_attributes))
					{
						$selected_value = $option->itemattributeoption_id;

						if (!empty($selected_value))
						{
							$query = $this->_db->getQuery(true);
							$query->select("`orderitemattribute_id`, `orderitemattribute_name`");
							$query->from('#__kart_order_itemattributes');
							$query->where("itemattributeoption_id =" . $selected_value . "");
							$query->where("order_item_id = " . $item->order_item_id);
							$this->_db->setQuery($query);

							$itemattributes                       = $this->_db->LoadObject();
							$optionDetails->orderitemattribute_id = $itemattributes->orderitemattribute_id;
							$optionDetails->selected              = $selected_value;

							if (!empty($optionDetails->attributeFieldType) && ($optionDetails->attributeFieldType == 'Textbox'))
							{
								$optionDetails->orderitemattribute_name = $itemattributes->orderitemattribute_name;
							}

							break;
						}
					}
				}
			}
		}

		return $order;
	}

	/**
	 * Updatecart updates the cart, Attributes, shipping charges.
	 *
	 * @since   2.2.2
	 *
	 * @return   status.
	 */
	public function updateOrderCartItemAttributes()
	{
		$jinput = JFactory::getApplication()->input;
		$post   = $jinput->post;

		// Get parsed form data
		parse_str($post->get('formData', '', 'STRING'), $formData);
		$comquick2cartHelper         = new Comquick2cartHelper;
		$path                        = JPATH_SITE . '/components/com_quick2cart/models/cartcheckout.php';
		$checkoutModel = $comquick2cartHelper->loadqtcClass($path, "Quick2cartModelcartcheckout");

		$order_id   = $jinput->get('order_id', '', 'INT');
		$orderInfo  = $this->getorderinfo($order_id);
		$userId     = $orderInfo['order_info'][0]->user_id;
		$orderItems = $orderInfo['items'];

		// $cart_item_id = $post->get('order_item_id', '', 'INT');
		$item_id   = $post->get('item_id', '', 'INT');
		$cartItems = $formData['cartDetail'];

		// Required while order update
		$modifiedOrder                  = new stdClass;
		$modifiedOrder->id              = $order_id;
		$modifiedOrder->coupon_code     = $orderInfo['order_info'][0]->coupon_code;
		$totalItemTax                   = 0;
		$totalItemShip                  = 0;
		$modifiedOrder->original_amount = 0;

		// Update items first
		foreach ($cartItems as $cartIndex => $item)
		{
			$dbOrderItemDetail = array();

			// Get order item details
			foreach ($orderItems as $oitem)
			{
				if ($cartIndex == $oitem->order_item_id)
				{
					$dbOrderItemDetail = $oitem;
					break;
				}
			}

			$orderItem                           = new stdClass;
			$orderItem->order_item_id            = $item['order_item_id'];
			$orderItem->product_quantity         = $item['cart_count'];
			$orderItem->product_item_price       = $dbOrderItemDetail->product_item_price;
			$orderItem->product_attributes_price = 0;
			$OrderitemAttrTbIds                  = array();
			$product_attribute_names             = array();

			// Check for count
			// 1. If item has attribute
			if (!empty($item['attrDetail']))
			{
				// Get attribute list for order items
				$DBoptionList = $this->getOrderItemOptionList($item['order_item_id']);

				foreach ($item['attrDetail'] as $key => $attr)
				{
					$optionDetails = array();

					/* Control come if user has changed option value to not compulsory. (none)
					Just removing the empty option field
					*/
					if ($attr['type'] == 'Select' && empty($attr['value']))
					{
						continue;
					}

					// If type  = select and option present in Db attribute array then nothing have to update
					// For text type, we hv to update
					if ($attr['type'] == "Textbox")
					{
						$itemattributeoption_id = $attr['itemattributeoption_id'];

						// Update option - text field value and update option entry
						$optionDetails['orderitemattribute_name'] = $attr['value'];
					}
					elseif ($attr['type'] == 'Select')
					{
						$itemattributeoption_id = $attr['value'];
					}

					// Get attribute option details
					$optDetail = $this->productHelper->getOrderAttributeOptionDetails($item['order_item_id'], $itemattributeoption_id);

					// Update text attribute value
					if (!empty($optionDetails['orderitemattribute_name']))
					{
						$optDetail['orderitemattribute_name'] = $optionDetails['orderitemattribute_name'];
					}

					// For new option, it will be empty
					if (empty($optDetail))
					{
						// Get option details
						$result = $this->productHelper->getAttributeOptionDetails($itemattributeoption_id);

						$optionDetails['order_item_id']           = $item['order_item_id'];
						$optionDetails['itemattributeoption_id']  = $result['itemattributeoption_id'];

						if ($attr['type'] == 'Select')
						{
							$optionDetails['orderitemattribute_name'] = $result['itemattributeoption_name'];
						}

						// It is recalculated in next function - insertOptionToOrderItems
						$optionDetails['orderitemattribute_price']  = 0;
						$optionDetails['orderitemattribute_prefix'] = $result['itemattributeoption_prefix'];
					}
					else
					{
						$optionDetails = $optDetail;
					}

					// Insert/update option and get latest option detail
					$newOptionDetails = $this->productHelper->insertOptionToOrderItems($optionDetails);

					if ($newOptionDetails['orderitemattribute_prefix'] == '+')
					{
						$orderItem->product_attributes_price += $newOptionDetails['orderitemattribute_price'];
					}
					elseif ($newOptionDetails['orderitemattribute_prefix'] == '-')
					{
						$orderItem->product_attributes_price -= $newOptionDetails['orderitemattribute_price'];
					}

					$OrderitemAttrTbIds[]      = $newOptionDetails['itemattributeoption_id'];
					$product_attribute_names[] = $newOptionDetails['orderitemattribute_name'];

					// Remove from DBoptionList
					if ($itemattributeoption_id && isset($DBoptionList[$itemattributeoption_id]))
					{
						unset($DBoptionList[$itemattributeoption_id]);
					}
				}

				// Delete extra options
				$this->deleteExtraOrderoptions($DBoptionList);

				// Get attribute ids and names
				$orderItem->product_attributes      = !empty($OrderitemAttrTbIds) ? implode(',', $OrderitemAttrTbIds) : '';
				$orderItem->product_attribute_names = !empty($product_attribute_names) ? implode(',', $product_attribute_names) : '';
			}

			// ProductPrice = Base price + attribute price) * qty
			$productPrice = ((float) $orderItem->product_item_price + (float) $orderItem->product_attributes_price) * $orderItem->product_quantity;
			$orderItem->original_price = $productPrice;

			// Check for item discount and update product price
			if (isset($dbOrderItemDetail->params))
			{
				$param    = json_decode($dbOrderItemDetail->params, true);
				$cop_code = isset($param['coupon_code']) ? $param['coupon_code'] : '';

				if ($cop_code)
				{
					$path                        = JPATH_SITE . "/components/com_quick2cart/models/cartcheckout.php";
					$checkoutModel = $this->comquick2cartHelper->loadqtcClass($path, 'Quick2cartModelcartcheckout');
					$valid                       = $checkoutModel->getcoupon($cop_code, $userId, "order", $order_id);

					if (!empty($valid))
					{
						if ($valid[0]->val_type == 1)
						{
							$cval = ($valid[0]->value / 100) * $productPrice;
						}
						else
						{
							$cval = $valid[0]->value;
						}

						$afterDiscount = $productPrice - $cval;

						if ($afterDiscount <= 0)
						{
							$afterDiscount = 0;
						}

						$productPrice = ($afterDiscount >= 0) ? $afterDiscount : $totalamt;
					}
				}
			}

			// Leval of taxation order leval or item leval
			$taxShipLeval = isset($formData['editLeval_taxship']) ? $formData['editLeval_taxship'] : '';

			$itemTaxShipDetail = isset($formData['itemTaxShipDetail']) ? $formData['itemTaxShipDetail'] : '';

			if (isset($itemTaxShipDetail[$orderItem->order_item_id]['tax']))
			{
				// Update the item tax detail
				$DBitem_tax_detail = array();

				if (!empty($dbOrderItemDetail->item_tax_detail))
				{
					$DBitem_tax_detail = json_decode($dbOrderItemDetail->item_tax_detail, true);
				}

				$DBitem_tax_detail['adminUpdate'] = "Admin updated the tax. Old tax = " .
				$dbOrderItemDetail->item_tax . " && new = " . $itemTaxShipDetail[$orderItem->order_item_id]['tax'];
				$orderItem->item_tax_detail       = json_encode($DBitem_tax_detail);

				// Update the item tax
				$orderItem->item_tax = $dbOrderItemDetail->item_tax = $itemTaxShipDetail[$orderItem->order_item_id]['tax'];
				$totalItemTax += $orderItem->item_tax;
			}

			if (isset($itemTaxShipDetail[$orderItem->order_item_id]['ship']))
			{
				// Update the item ship detail
				$DBitem_ship_detail = array();

				if (!empty($dbOrderItemDetail->item_shipDetail))
				{
					$DBitem_ship_detail = json_decode($dbOrderItemDetail->item_shipDetail, true);
				}

				$DBitem_ship_detail['adminUpdate'] = "Admin updated the ship. Old tax = " .
				$dbOrderItemDetail->item_shipcharges . " && new = " . $itemTaxShipDetail[$orderItem->order_item_id]['ship'];
				$orderItem->item_tax_detail        = json_encode($DBitem_tax_detail);

				$orderItem->item_shipcharges = $dbOrderItemDetail->item_shipcharges = $itemTaxShipDetail[$orderItem->order_item_id]['ship'];
				$totalItemShip += $orderItem->item_shipcharges;
			}

			$orderItem->product_final_price = $productPrice + $dbOrderItemDetail->item_tax + $dbOrderItemDetail->item_shipcharges;
			$modifiedOrder->original_amount += $orderItem->product_final_price;

			try
			{
				$db = JFactory::getDbo();

				if (!$db->updateObject('#__kart_order_item', $orderItem, 'order_item_id'))
				{
					echo $db->stderr();
				}
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
			}
		}

		// NOW: Update the order table
		$copDiscount = 0;

		if ($modifiedOrder->coupon_code)
		{
			$copDiscount = $checkoutModel->afterDiscountPrice($modifiedOrder->original_amount, $modifiedOrder->coupon_code, "", "order", $modifiedOrder->id);

			$copDiscount = ($copDiscount >= 0) ? $copDiscount : 0;
		}

		// Get order detail
		$dbOrderDetail                    = $this->getOrderEntry($modifiedOrder->id);
		$modifiedOrder->order_tax_details = $dbOrderDetail->order_tax_details;
		$modifiedOrder->order_tax         = $dbOrderDetail->order_tax;

		$modifiedOrder->order_shipping_details = $dbOrderDetail->order_shipping_details;
		$modifiedOrder->order_shipping         = $dbOrderDetail->order_shipping;

		// 4. Update amount column according to discount,tax,shipping details
		if (isset($formData['OrderTaxShipDetail']))
		{
			$newOrderTax = isset($formData['OrderTaxShipDetail']['tax']) ? $formData['OrderTaxShipDetail']['tax'] : 0;

			// If tax is updated then only update field
			if ($newOrderTax != $dbOrderDetail->order_tax)
			{
				// Update the item tax detail
				$DBTax_detail = array();

				if (!empty($dbOrderDetail->order_tax_details))
				{
					$DBTax_detail = json_decode($dbOrderDetail->order_tax_details, true);
				}

				$DBTax_detail['adminUpdate'][]['old'] = $dbOrderDetail->order_tax;
				$DBTax_detail['adminUpdate'][]['new'] = $newOrderTax;

				$modifiedOrder->order_tax_details = json_encode($DBTax_detail);
				$modifiedOrder->order_tax         = $newOrderTax;
			}

			$newOrderShip = isset($formData['OrderTaxShipDetail']['ship']) ? $formData['OrderTaxShipDetail']['ship'] : 0;

			// If shipping is updated then only update field
			if ($newOrderShip != $dbOrderDetail->order_shipping)
			{
				// Update the item tax detail
				$DBTax_detail = array();

				if (!empty($dbOrderDetail->order_shipping_details))
				{
					$DBTax_detail = json_decode($dbOrderDetail->order_shipping_details, true);
				}

				$DBTax_detail['adminUpdate'][]['old'] = $dbOrderDetail->order_shipping;
				$DBTax_detail['adminUpdate'][]['new'] = $newOrderShip;

				$modifiedOrder->order_shipping_details = json_encode($DBTax_detail);
				$modifiedOrder->order_shipping         = $newOrderShip;
			}

			$modifiedOrder->amount = $modifiedOrder->original_amount + $modifiedOrder->order_tax + $modifiedOrder->order_shipping - $copDiscount;
		}
		else
		{
			// + $totalItemTax + $totalItemShip - $copDiscount;
			$modifiedOrder->amount = $modifiedOrder->original_amount;
		}

		if (!$db->updateObject('#__kart_orders', $modifiedOrder, 'id'))
		{
			$this->setError($db->getErrorMsg());

			return 0;
		}

		return 1;
	}

	/**
	 * This function return order table entry
	 *
	 * @param   integer  $order_id  order id.
	 *
	 * @since   2.2.7
	 * @return  array.
	 */
	public function getOrderEntry($order_id)
	{
		if ($order_id)
		{
			try
			{
				$db    = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select(' o.* ');
				$query->from('#__kart_orders as o');
				$query->where("id =" . $order_id);

				$db->setQuery($query);

				return $orderDetail = $db->loadObject();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				throw new Exception($this->_db->getErrorMsg());

				return;
			}
		}
	}

	/**
	 * This function return Order items's OptionList
	 *
	 * @param   integer  $orderItemId  Order Item Id.
	 *
	 * @since   2.2
	 * @return   list
	 */
	public function getOrderItemOptionList($orderItemId)
	{
		if ($orderItemId)
		{
			// Get orderitemattribute_ids to update
			try
			{
				$query = $this->_db->getQuery(true);
				$query->select("`orderitemattribute_id`,itemattributeoption_id	");
				$query->from('#__kart_order_itemattributes ');
				$query->where(" order_item_id = " . $orderItemId);
				$this->_db->setQuery($query);

				return $this->_db->loadAssocList('itemattributeoption_id');
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return array();
			}
		}
	}

	/**
	 * This function return Order items's OptionList
	 *
	 * @param   array  $DBoptionList  Order Item Id.
	 *
	 * @since   2.2
	 * @return   list
	 */
	private function deleteExtraOrderoptions($DBoptionList)
	{
		$db = JFactory::getDBO();

		try
		{
			foreach ($DBoptionList as $option)
			{
				$query = $db->getQuery(true)->delete('#__kart_order_itemattributes')->where('orderitemattribute_id =' . $option['orderitemattribute_id']);
				$db->setQuery($query);
				$db->execute();
			}
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			throw new Exception($this->_db->getErrorMsg());

			return -1;
		}
	}

	/**
	 * Amol changes Get order history
	 *
	 * @param   integer  $order_id  order_id.
	 *
	 * @return  result.
	 *
	 * @since   1.6
	 */
	public function getOrderHistory($order_id)
	{
		if (!empty($order_id))
		{
			$query = $this->_db->getQuery(true);
			$query->select("oh.*,i.name");
			$query->from($this->_db->quoteName('#__kart_orders_history') . ' AS oh');
			$query->join('INNER', "#__kart_order_item AS oi ON oh.order_item_id = oi.order_item_id");
			$query->join('INNER', "#__kart_items AS i ON i.item_id = oi.item_id");

			$query->where("oh.order_id = " . $order_id);
			$query->order("oh.order_item_id ASC");
			$query->order("oh.mdate  ASC");

			$this->_db->setQuery($query);

			return $result = $this->_db->loadObjectList();
		}

		return false;
	}
}
