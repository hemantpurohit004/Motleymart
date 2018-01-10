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

jimport('joomla.application.component.controlleradmin');

/**
 * Orders list controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerOrders extends JControllerAdmin
{
	/**
	 * Save order data.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function save()
	{
		$model  = $this->getModel('orders');
		$jinput = JFactory::getApplication()->input;

		$post = $jinput->post;
		$model->setState('request', $post);
		$result = $model->store();

		if ($result == 1)
		{
			$msg = JText::_('QTC_FIELD_SAVING_MSG');
		}
		elseif ($result == 3)
		{
			$msg = JText::_('QTC_REFUND_SAVING_MSG');
		}
		else
		{
			$msg = JText::_('FIELD_ERROR_SAVING_MSG');
		}

		$layout = $jinput->get("layout");

		if ($layout == "order")
		{
			$orderid = $jinput->get("orderid");
			$link = 'index.php?option=com_quick2cart&view=orders&layout=order&orderid=' . $orderid;
		}
		else
		{
			$link = 'index.php?option=com_quick2cart&view=orders';
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * SaveOrder data on save button. (Amol)
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function saveOrderData()
	{
		$input   = JFactory::getApplication()->input;
		$orderid = $input->get('orderid', '', 'INT');
		$model   = $this->getModel('orders');

		$result = $model->saveOrderData();

		if ($result)
		{
			$msg = JText::_('C_SAVE_M_S');
		}
		else
		{
			$msg = JText::_('C_SAVE_M_NS');
		}

		$link = 'index.php?option=com_quick2cart&view=orders&layout=order&orderid=' . $orderid;
		$this->setRedirect($link, $msg);
	}

	/**
	 * Export order payment stats into a csv file.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function payment_csvexport()
	{
		$db    = JFactory::getDBO();
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);

		$query = $db->getQuery(true);
		$query->select("i.id, i.name, i.email, i.user_info_id,i.cdate, i.transaction_id, i.processor, i.order_tax, i.order_tax_details");
		$query->select("i.order_shipping, i.order_shipping_details,  i.amount, i.status, i.ip_address");
		$query->from('#__kart_orders AS i');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		$csvDataString .= implode(";", $headColumn);
		$csvDataString .= "\n";
		$csvData = null;
		$csvData .= " Order_Id, Order_Date, User_Name, User_IP, Order_Tax, Order_Tax_details, Order_Shipping, Order_Shipping_details";
		$csvData .= ", Order_Amount, Order_Status, Payment_Gateway, Cart_Items, billing_email, billing_first_name, billing_last_name, billing_phone";
		$csvData .= ", billing_address, billing_city, billing_state, billing_country_name, billing_postal_code, shipping_email";
		$csvData .= ", shipping_first_name, shipping_last_name, shipping_phone";
		$csvData .= ", shipping_address, shipping_city, shipping_state, shipping_country_name, shipping_postal_code";

		$csvData .= "\n";
		$filename = "Orders_" . date("Y-m-d_H-i", time());
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m") . ".csv");
		header("Content-disposition: filename=" . $filename . ".csv");

		// Get geo manager obj
		require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php';
		$tjGeoHelper = TjGeoHelper::getInstance('TjGeoHelper');

		foreach ($results as $result)
		{
			if (($result->id))
			{
				$order_tax_details = (str_replace(",", ";", $result->order_tax_details));
				$order_shipping_details = addslashes(str_replace(",", ";", $result->order_shipping_details));

				$csvData .= '"' . $result->id . '"' . ',' . '"' . $result->cdate . '"';
				$csvData .= ',' . '"' . JFactory::getUser($result->user_info_id)->username . '"';
				$csvData .= ',' . '"' . $result->ip_address . '"' . ',' . '"' . $result->order_tax . '"';
				$csvData .= ',' . $order_tax_details . ',' . '"' . $result->order_shipping . '"';
				$csvData .= ',' . $order_shipping_details . ',' . '"' . $result->amount . '"' . ',';

				switch ($result->status)
				{
					case 'C':
						$orderstatus = JText::_('QTC_CONFR');
						break;
					case 'RF':
						$orderstatus = JText::_('QTC_REFUN');
						break;
					case 'S':
						$orderstatus = JText::_('QTC_SHIP');
						break;
					case 'P':
						$orderstatus = JText::_('QTC_PENDIN');
						break;
				}

				$query = "SELECT count(order_item_id) FROM #__kart_order_item WHERE order_id =" . $result->id;
				$db->setQuery($query);
				$cart_items = $db->loadResult();
				$csvData .= '"' . $orderstatus . '"' . ',' . '"' . $result->processor . '"' . ',' . '"' . $cart_items . '"' . ',';

				// 	Getting billind detail
				$query = $db->getQuery(true);
				$query->select("ou.*");
				$query->from('#__kart_users as ou');
				$query->where("ou.address_type='BT' AND ou.user_id =" . $result->user_info_id);
				$query->where("ou.order_id=" . $result->id);
				$query->order("id DESC");
				$db->setQuery($query);
				$billin = $db->loadObject();

				if ($billin)
				{
					// Getting country and region names from their codes.
					if (is_numeric($billin->country_code))
					{
						$billin->country_code = $tjGeoHelper->getCountryNameFromId($billin->country_code);
					}

					if (is_numeric($billin->state_code))
					{
						$billin->state_code = $tjGeoHelper->getRegionNameFromId($billin->state_code);
					}

					$csvData .= '"' . $billin->user_email . '"';
					$csvData .= ',' . '"' . $billin->firstname . '"';
					$csvData .= ',' . '"' . $billin->lastname . '"';
					$csvData .= ',' . '"' . $billin->phone . '"';
					$csvData .= ',' . '"' . $billin->address . '"';
					$csvData .= ',' . '"' . $billin->city . '"';
					$csvData .= ',' . '"' . $billin->state_code . '"';
					$csvData .= ',' . '"' . $billin->country_code . '"';
					$csvData .= ',' . '"' . $billin->zipcode . '"' . ',';
				}

				$query = $db->getQuery(true);
				$query->select("ou.*");
				$query->from('#__kart_users as ou');
				$query->where("ou.address_type='ST' AND ou.user_id =" . $result->user_info_id);
				$query->where("ou.order_id=" . $result->id);
				$query->order("id DESC");
				$db->setQuery($query);
				$shipping = $db->loadObject();

				if ($shipping)
				{
					// Getting country and region names from their codes.
					if (is_numeric($shipping->country_code))
					{
						$shipping->country_code = $tjGeoHelper->getCountryNameFromId($shipping->country_code);
					}

					if (is_numeric($shipping->state_code))
					{
						$shipping->state_code = $tjGeoHelper->getRegionNameFromId($shipping->state_code);
					}

					$csvData .= '"' . $shipping->user_email . '"';
					$csvData .= ',' . '"' . $shipping->firstname . '"';
					$csvData .= ',' . '"' . $shipping->lastname . '"';
					$csvData .= ',' . '"' . $shipping->phone . '"' . ',';
					$csvData .= '"' . $shipping->address . '"';
					$csvData .= ',' . '"' . $shipping->city . '"';
					$csvData .= ',' . '"' . $shipping->state_code . '"';
					$csvData .= ',' . '"' . $shipping->country_code . '"';
					$csvData .= ',' . '"' . $shipping->zipcode . '"' . ',';
				}

				print $csvData .= "\n";
			}
		}

		ob_clean();
		print $csvData;
		exit();
	}

	/*function cancel()
	{
	$msg = JText::_('QTC_FIELD_CANCEL_MSG');
	$this->setRedirect('index.php?option=com_quick2cart', $msg);
	}*/

	/**
	 * Method to delete records.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function deleteorders()
	{
		$model  = $this->getModel('Orders');
		$jinput = JFactory::getApplication()->input;
		$post   = $jinput->post;
		$cid    = $post->get('cid', array(), 'ARRAY');

		if ($model->delete($cid))
		{
			$msg = JText::_('ORDER_DELETED');
		}
		else
		{
			$msg = JText::_('ERR_ORDER_DELETED');
		}

		$this->setRedirect(JUri::base() . "index.php?option=com_quick2cart&view=orders", $msg);
	}

	/**
	 * Function for Resending invoice to buyer
	 *
	 * @since   2.2.2
	 * @return   null.
	 */
	public function resendInvoice()
	{
		$params             = JComponentHelper::getParams('com_quick2cart');
		$multivendor_enable = $params->get('multivendor');
		$app     = JFactory::getApplication();
		$jinput  = $app->input;
		$orderid = $jinput->get('orderid', '', 'INT');
		$store_id = $jinput->get('store_id', '', 'INT');
		$comquick2cartHelper = new comquick2cartHelper;

		if (empty($multivendor_enable))
		{
			$order = $comquick2cartHelper->getorderinfo($orderid);
			$store_id = $order['items'][0]->store_id;
			$jinput->set('store_id', $store_id);
		}

		$model  = $this->getModel('Orders');
		$result = $model->resendInvoice();
		$msg = '';

		if ($result)
		{
			$msg = JText::_("COM_QUICK2CART_INVOICE_SEND");
		}

		echo $msg;

		jexit();

		// IF not multi-vendor then redirect to my order list layout

		/*if (empty($multivendor_enable))
		{
			$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=orders&layout=defaul', false);
		}
		else
		{
			$redirectUrl = JUri::base() . "index.php?option=com_quick2cart&view=orders&layout=order&orderid=" . $orderid . "&store_id=" . $store_id;
		}

		$this->setRedirect($redirectUrl, $msg);*/
	}

	/**
	 * Updatecart updates the cart, Attributes.
	 * shipping charges
	 *
	 * @since   2.2.2
	 * @return   json.
	 */
	public function updateOrderItemAttribute()
	{
		$input = JFactory::getApplication()->input;
		$post = $input->post;
		$data          = $post->get('formData', '', 'STRING');

		if (!empty($data)) // && !empty($order_item_id))
		{
			$model = $this->getModel('Orders');
			echo $result = $model->updateOrderCartItemAttributes();
		}
		else
		{
			return false;
		}

		jexit();
	}

	/**
	 * This function is to generate, store wise invoice PDF
	 *
	 * @since   2.5
	 * @return   json.
	 */
	public function generateInvoicePDF()
	{
		$params             = JComponentHelper::getParams('com_quick2cart');
		$multivendor_enable = $params->get('multivendor');
		$app     = JFactory::getApplication();
		$jinput  = $app->input;

		// Get order id and store id
		$orderid = $jinput->get('orderid', '', 'INT');
		$store_id = $jinput->get('store_id', '', 'INT');

		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
		$storeHelper = new storeHelper;
		$storeHelper->generateInvoicePDF($orderid, $store_id);

		// IF not multi-vendor then redirect to my order list layout
		if (empty($multivendor_enable))
		{
			$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=orders&layout=defaul', false);
		}
		else
		{
			$redirectUrl = JUri::base() . "index.php?option=com_quick2cart&view=orders&layout=order&orderid=" . $orderid;
		}

		$this->setRedirect($redirectUrl);
	}
}
