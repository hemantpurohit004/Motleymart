<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

/**
 * Reports Helper
 *
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 * @since       1.5
 */
class CreateOrderHelper
{
	/**
	 * Construct
	 *
	 * @since   2.5.5
	 */
	public function __construct()
	{
		require_once JPATH_SITE . "/components/com_quick2cart/models/cart.php";
		require_once JPATH_SITE . "/components/com_quick2cart/models/attributes.php";
		require_once JPATH_SITE . "/components/com_quick2cart/models/payment.php";
		require_once JPATH_SITE . "/components/com_quick2cart/models/cartcheckout.php";
		require_once JPATH_SITE . "/components/com_quick2cart/helpers/product.php";
		require_once JPATH_SITE . "/administrator/components/com_quick2cart/models/globalattribute.php";
		require_once JPATH_SITE . "/components/com_quick2cart/helper.php";
	}

	/**
	 * Function to place order
	 *
	 * @param   ARRAY  $orderData  order data
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function qtc_place_order($orderData)
	{
		// Function to club common products
		$comquick2cartHelper = new comquick2cartHelper;
		$orderData->products_data = $this->formatProductsData($orderData->products_data);

		$user = JFactory::getUser();

		$orderDetails = new stdclass;
		$orderStatus = new stdclass;
		$orderStatus->status = "error";
		$orderStatus->message = "";
		$orderStatus->order_id = 0;

		if (empty($orderData))
		{
			$orderStatus->message = JText::_('COM_QUICK2CART_INVALID_DATA_FOR_PLACE_ORDER_API');

			return $orderStatus;
		}

		$orderDetails->user_info_id = $orderData->userId;

		if ($orderDetails->user_info_id != $user->id)
		{
			$orderDetails->created_by = $user->id;
		}

		$orderDetails->payee_id = $orderData->userId;

		// Get client details
		if (!empty($orderData->address->billing))
		{
			// Assigning userdata to order details
			$orderDetails->email = $orderData->address->billing->user_email;
			$orderDetails->name = $orderData->address->billing->firstname;
		}

		/* Commented By Nitesh
		// Before payment order status will be pending
		$orderDetails->status = "P";
		$orderDetails->cdate = date("Y-m-d H:i:s");
		$orderDetails->mdate = date("Y-m-d H:i:s");
		*/

		// Hack By Nitesh
		$timeZone = JFactory::getConfig()->get('offset');
		date_default_timezone_set($timeZone);
		$date = date("Y-m-d H:i:s");

		// Before payment order status will be pending
		$orderDetails->status = "P";
		$orderDetails->cdate = $date;
		$orderDetails->mdate = $date;

		// Price without coupon
		$order_original_amount = $this->calculateOrderPrice($orderData->products_data);

		$orderDetails->amount = $this->calculateOrderPrice($orderData->products_data);

		$params = JComponentHelper::getParams('com_quick2cart');
		$isShippingEnabled = $params->get('shipping', 0);
		$isTaxationEnabled = $params->get('enableTaxtion', 0);

		// Get ip address of user placing the order (consider from input PENDING)
		$orderDetails->ip_address = !empty($_SERVER ['REMOTE_ADDR'])?$_SERVER ['REMOTE_ADDR']:'unknown';

		$db = JFactory::getDbo();

		$cartCheckoutModel = new Quick2cartModelcartcheckout;

		if (!empty($orderId))
		{
			// EDIT ORDER
			$orderDetails->id = $orderId;

			if (!$db->updateObject('#__kart_orders', $orderDetails, 'id'))
			{
				$orderStatus->status = "error";
				$orderStatus->message = JText::_('ERR_CONFIG_SAV_LOGIN');
				$orderStatus->dberror = $db->stderr();
			}
			else
			{
				$orderStatus->status = "success";
				$orderStatus->order_id = $orderId;
			}
		}
		else
		{
			$currency = $comquick2cartHelper->getCurrencySession();
			$orderDetails->currency = !empty($orderData->currency_code) ? $orderData->currency_code : $currency;

			if (!$db->insertObject('#__kart_orders', $orderDetails, 'id'))
			{
				$orderStatus->status = "error";
				$orderStatus->message = JText::_('ERR_CONFIG_SAV_LOGIN');
				$orderStatus->dberror = $db->stderr();
			}
			else
			{
				$orderStatus->status = "success";
				$orderStatus->order_id = $orderId = $db->insertid();
			}

			// Code to pad zero's to order_id and append to prefix and update - start
			$Quick2cartModelpayment = new Quick2cartModelpayment;

			$prefix = $Quick2cartModelpayment->generate_prefix($orderId);

			// Code to pad zero's to order_id and append to prefix and update - end

			// Update Order prefix for the order
			$orderDetails = new stdClass;
			$orderDetails->prefix 		= $prefix;
			$orderDetails->id 			= $orderId;

			if (!$db->updateObject('#__kart_orders', $orderDetails, 'id'))
			{
				$orderStatus->status = "error";
				$orderStatus->message = JText::_('ERR_CONFIG_SAV_LOGIN');
				$orderStatus->dberror = $db->stderr();
			}
			else
			{
				$orderStatus->status = "success";
				$orderStatus->order_id = $orderId;
			}

			if (!empty($orderStatus->order_id))
			{
				// Code to add order items to order items table - start
				$order_item_status = $this->qtc_add_order_items($orderData, $orderStatus->order_id);
			}

			// Code to calculate tax - starts
			$vars = new stdclass;

			foreach ($orderData->products_data as $product_data)
			{
				$product = array();
				$product['item_id'] = $product_data['product_id'];

				$product['product_final_price'] = $product['tamt'] = $this->calculateProductPrice($product_data);
				$product['product_attributes'] = "";

				if (!empty($product_data['att_option']))
				{
					$product['product_attributes'] = $this->getSelectedAttributesList($product_data['att_option']);
					$attri_items_info = $this->getItemAttributeDetails($product['product_attributes']);

					if (!empty($attri_items_info))
					{
						$product['product_attribute_names'] = $this->getItemsSelectedAttributeName($product_data['att_option'], $attri_items_info);
					}
				}

				$product['qty'] = $product_data["product_quantity"];

				$curr = $comquick2cartHelper->getCurrencySession();
				$product['currency'] = $curr;

				$cartItemDetail[] = $product;
			}

			$vars->cartItemDetail = $cartItemDetail;

			if (!empty($orderData->address->billing))
			{
				$vars->billing_address = $this->mapUserAddress($orderData->address->billing);
			}

			if (!empty($orderData->address->shipping))
			{
				$vars->shipping_address = $this->mapUserAddress($orderData->address->shipping);
			}

			if ($isTaxationEnabled && $orderStatus->status == 'success')
			{
				// TODO: Remove reference of ship_chk
				$vars->ship_chk = 1;

				$taxDetails = $cartCheckoutModel->afterTaxPrice($order_original_amount, $vars);

				if (!empty($taxDetails->order_tax_details) && !empty($taxDetails->charges))
				{
					$taxDetails->order_tax_details = $taxDetails->order_tax_details;
				}

				$this->qtc_apply_taxes($taxDetails, $orderId);

				// Code to calculate tax - ends
			}

			// Code for shipping - start
			if ($isShippingEnabled)
			{
				$vars->ship_chk = 1;

				$jinput = JFactory::getApplication();
				$itemsShipMethRateDetail = $jinput->input->get('itemshipMethDetails', '', '');

				if (!empty($itemsShipMethRateDetail))
				{
					$vars->itemsShipMethRateDetail = $jinput->input->get('itemshipMethDetails', '', '');
				}

				$selectedItemshipMeth = $jinput->input->get('itemshipMeth', '', '');

				if (!empty($selectedItemshipMeth))
				{
					$vars->selectedItemshipMeth = $jinput->input->get('itemshipMeth', '', '');
				}

				$shippingDetails = $cartCheckoutModel->afterShipPrice($order_original_amount, $vars);

				// If allowed to place order
				if (!empty($shippingDetails['allowToPlaceOrder']) && $shippingDetails['allowToPlaceOrder'] == 1 && !empty($shippingDetails['charges']))
				{
					$this->qtc_apply_shipping($shippingDetails, $orderId);
				}
			}
			// Code for shipping - end

			// Code to update order original price
			$this->updateOrderOriginalPrice($orderId);

			// Add address info to created order
			if (!empty($orderStatus->order_id))
			{
				if (!empty($orderData->address->billing))
				{
					$this->qtc_add_billing_address($orderData->address->billing, $orderStatus->order_id);
				}

				if (!empty($orderData->address->shipping))
				{
					$this->qtc_add_shipping_address($orderData->address->shipping, $orderStatus->order_id);
				}

				$jinput              = JFactory::getApplication()->input;
				$jinput->set('orderid', $orderId);
			}

			// Clear the seesion coupon
			$session = JFactory::getSession();
			$session->clear("coupon");
		}

		return $orderStatus;
	}

	/**
	 * Function to add billing address for order
	 *
	 * @param   ARRAY  $address  array of address info
	 *
	 * @param   INT    $orderId  order id
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function qtc_add_billing_address($address, $orderId)
	{
		$status = new stdclass;
		$db = JFactory::getDbo();

		if (!empty($orderId))
		{
			$orderInfo = $this->getOrderDetails($orderId);
		}

		if (empty($orderInfo) || empty($address->user_email))
		{
			$status->status = "error";
			$status->message = JText::_('COM_QUICK2CART_INVALID_ADDRESS_OR_ORDER');

			return $status;
		}

		if (!empty($address->user_email))
		{
			$obj = new stdClass;

			// Update email id in orders table
			$obj->email = $address->user_email;
			$obj->id = $orderId;
			$db->updateObject('#__kart_orders', $obj, 'id');
		}

		$addressDetails = new stdclass;

		$addressDetails->address_type = "BT";
		$addressDetails->tax_exempt = "0";
		$addressDetails->approved = "1";
		$addressDetails->user_id = (!empty($orderInfo->user_info_id))?$orderInfo->user_info_id:'';
		$addressDetails->order_id = (!empty($orderId))?$orderId:'';
		$addressDetails->user_email = (!empty($address->user_email))?$address->user_email:'';
		$addressDetails->firstname = (!empty($address->firstname))?$address->firstname:'';
		$addressDetails->middlename = (!empty($address->middlename))?$address->middlename:'';
		$addressDetails->lastname = (!empty($address->lastname))?$address->lastname:'';
		$addressDetails->vat_number = (!empty($address->vat_number))?$address->vat_number:'';
		$addressDetails->country_code = (!empty($address->country_code))?$address->country_code:'';
		$addressDetails->address = (!empty($address->address))?$address->address:'';
		$addressDetails->city = (!empty($address->city))?$address->city:'';
		$addressDetails->state_code = (!empty($address->state_code))?$address->state_code:'';
		$addressDetails->zipcode = (!empty($address->zipcode))?$address->zipcode:'';
		$addressDetails->land_mark = (!empty($address->land_mark))?$address->land_mark:'';
		$addressDetails->phone = isset($address->phone) ? $address->phone : '';
		$addressDetails->latitude = isset($address->latitude) ? $address->latitude : '';
		$addressDetails->longitude = isset($address->longitude) ? $address->longitude : '';

		// To check if billing address already present for order
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__kart_users'));
		$query->where($db->quoteName('address_type') . ' = ' . $db->quote('BT'));
		$query->where($db->quoteName('order_id') . ' = ' . $addressDetails->order_id);
		$query->order('id DESC');

		$db->setQuery($query);

		$bill_id = $db->loadResult();

		if ($bill_id)
		{
			$addressDetails->id = $bill_id;

			if (!$db->updateObject('#__kart_users', $addressDetails, 'id'))
			{
				$status->status = "error";
				$status->message = JText::_('COM_QUICK2CART_INVALID_ADDRESS');
				$status->dberror = $db->stderr();
			}
			else
			{
				$status->status = "success";
			}
		}
		else
		{
			if (!$db->insertObject('#__kart_users', $addressDetails, 'id'))
			{
				$status->status = "error";
				$status->message = JText::_('COM_QUICK2CART_INVALID_ADDRESS');
				$status->dberror = $db->stderr();
			}
			else
			{
				if (!empty($address->id) && !empty($orderInfo))
				{
					// Function to update flag for last used address as billing address
					$this->updateAsLastUsedBillingAddress($address->id, $orderInfo->user_info_id);
				}

				$status->status = "success";
			}
		}
	}

	/**
	 * Function to update shipping info to kart_users table
	 *
	 * @param   ARRAY  $address  array of address info
	 *
	 * @param   INT    $orderId  order id
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function qtc_add_shipping_address($address, $orderId)
	{
		$status = new stdclass;
		$db = JFactory::getDbo();

		if (!empty($orderId))
		{
			$orderInfo = $this->getOrderDetails($orderId);
		}

		if (empty($orderInfo) || empty($address->user_email))
		{
			$status->status = "error";
			$status->message = JText::_('COM_QUICK2CART_INVALID_ADDRESS_OR_ORDER');

			return $status;
		}

		$addressDetails = new stdclass;

		$addressDetails->address_type = "ST";
		$addressDetails->tax_exempt = "0";
		$addressDetails->approved = "1";
		$addressDetails->user_id = (!empty($orderInfo->user_info_id))?$orderInfo->user_info_id:'';
		$addressDetails->order_id = (!empty($orderId))?$orderId:'';
		$addressDetails->user_email = (!empty($address->user_email))?$address->user_email:'';
		$addressDetails->firstname = (!empty($address->firstname))?$address->firstname:'';
		$addressDetails->middlename = (!empty($address->middlename))?$address->middlename:'';
		$addressDetails->lastname = (!empty($address->lastname))?$address->lastname:'';
		$addressDetails->vat_number = (!empty($address->vat_number))?$address->vat_number:'';
		$addressDetails->country_code = (!empty($address->country_code))?$address->country_code:'';
		$addressDetails->address = (!empty($address->address))?$address->address:'';
		$addressDetails->city = (!empty($address->city))?$address->city:'';
		$addressDetails->state_code = (!empty($address->state_code))?$address->state_code:'';
		$addressDetails->zipcode = (!empty($address->zipcode))?$address->zipcode:'';
		$addressDetails->land_mark = (!empty($address->land_mark))?$address->land_mark:'';
		$addressDetails->phone = isset($address->phone) ? $address->phone : '';

		// To check if shipping address already present for order
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__kart_users'));
		$query->where($db->quoteName('address_type') . ' = ' . $db->quote('ST'));
		$query->order('id DESC');

		$db->setQuery($query);

		$ship_id = $db->loadResult();

		if (!empty($ship_id))
		{
			$addressDetails->id = $ship_id;

			if (!$db->updateObject('#__kart_users', $addressDetails, 'id'))
			{
				$status->status = "error";
				$status->message = JText::_('COM_QUICK2CART_INVALID_ADDRESS');
				$status->dberror = $db->stderr();
			}
			else
			{
				$status->status = "success";
			}
		}
		else
		{
			if (!$db->insertObject('#__kart_users', $addressDetails, 'id'))
			{
				$status->status = "error";
				$status->message = JText::_('COM_QUICK2CART_INVALID_ADDRESS');
				$status->dberror = $db->stderr();
			}
			else
			{
				if (!empty($address->id) && !empty($orderInfo))
				{
					// Function to update flag for last used address as shipping address
					$this->updateAsLastUsedShippingAddress($address->id, $orderInfo->user_info_id);
				}

				$status->status = "success";
			}
		}
	}

	/**
	 * Function to add entry of order items into order_items table
	 *
	 * @param   ARRAY  $orderData    array of orders data
	 *
	 * @param   INT    $orderId      order id
	 *
	 * @param   INT    $orderItemId  order_item_id
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function qtc_add_order_items($orderData, $orderId, $orderItemId = 0)
	{
		$products_data = $orderData->products_data;
		$productHelper = new productHelper;
		$Quick2cartModelcart = new Quick2cartModelcart;
		$AttributesModel = new quick2cartModelAttributes;

		// Array to store data in cart format - used to get promotion details
		$cartFormat = array();

		// Array to store Order Item records
		$ordeItemList = array();

		foreach ($products_data as $key => $product_data)
		{
			$orderItemData = new stdclass;
			$cartFormatData = array();

			// Cart item unique id - used for getting promotion data
			$cartFormatData['id'] = $key;

			if (!empty($orderItemId))
			{
				$orderItemData->id = $orderItemId;
			}

			$orderItemData->store_id = $cartFormatData['store_id'] = $product_data['store_id'];
			$orderItemData->order_id = $orderId;
			$orderItemData->item_id = $cartFormatData['item_id'] = $product_data['product_id'];

			if (!empty($product_data['att_option']))
			{
				$orderItemData->product_attributes = $this->getSelectedAttributesList($product_data['att_option']);
			}

			if (!empty($orderItemData->product_attributes))
			{
				$product_attribute_names = array();

				$attribute_items_info = $this->getItemAttributeDetails($orderItemData->product_attributes);

				if (!empty($attribute_items_info))
				{
					$orderItemData->product_attribute_names = $this->getItemsSelectedAttributeName($product_data['att_option'], $attribute_items_info);
				}

				foreach ($attribute_items_info as $attribute_item_info)
				{
					if (!empty($attribute_item_info['child_product_item_id']))
					{
						$orderItemData->variant_item_id = $attribute_item_info['child_product_item_id'];
					}
				}
			}

			// GET ITEM DETAIL
			if (!empty($product_data['product_id']))
			{
				$itemDetail = $AttributesModel->getItemDetail(0, '', $product_data['product_id']);

				$cartFormatData['category'] = $itemDetail['category'];
				$orderItemData->order_item_name = $itemDetail['name'];
			}

			$orderItemData->product_quantity = $cartFormatData['qty'] = $product_data['product_quantity'];

			$product_obj = array();
			$product_obj['item_id'] = $product_data['product_id'];
			$product_obj['count'] = $product_data['product_quantity'];

			$product_obj['options'] = !empty($orderItemData->product_attributes)?$orderItemData->product_attributes:'';

			$prod_info = $Quick2cartModelcart->getProd($product_obj);
			$orderItemData->product_item_price = !empty($prod_info[0]['price'])?$prod_info[0]['price']:'0';

			$orderItemData->product_attributes_price = 0;

			if (!empty($prod_info[1]))
			{
				foreach ($prod_info[1] as $attri_info)
				{
					// Hack By Nitesh
					if ($attri_info['itemattributeoption_prefix'] == '+')
					{
						// $orderItemData->product_attributes_price += $attri_info['itemattributeoption_price']; Commented By Nitesh
						$orderItemData->product_attributes_price = $attri_info['itemattributeoption_price'];
					}
					else
					{
						// $orderItemData->product_attributes_price -= $attri_info['itemattributeoption_price']; Commented By Nitesh
						$orderItemData->product_attributes_price -= $attri_info['itemattributeoption_price'] * -1;
					}
				}
			}

			// $product_price = $orderItemData->product_attributes_price + $orderItemData->product_item_price; Commented By Nitesh

			// Hack By Nitesh
			$product_price = $orderItemData->product_attributes_price;

			$orderItemData->product_final_price = $cartFormatData['tamt'] = $product_price * $orderItemData->product_quantity;

			$orderItemData->original_price = $orderItemData->product_final_price;

			$orderItemData->cdate = date("Y-m-d H:i:s");
			$orderItemData->mdate = date("Y-m-d H:i:s");
			$orderItemData->status = 'P';

			// Store order Item details in array
			$ordeItemList[] = $orderItemData;

			// Store data in cart format - used to get promotions - unique product should be entered in unique index of array
			$cartFormat[$cartFormatData['id']] = $cartFormatData;
		}

		$helperPath = JPATH_SITE . '/components/com_quick2cart/models/cartcheckout.php';

		$comquick2cartHelper = new comquick2cartHelper;
		$cartcheckoutModel = $comquick2cartHelper->loadqtcClass($helperPath, "Quick2cartModelcartcheckout");

		// Get Coupon Code from order data
		$coupon_code = (!empty($orderData->coupon_code))?$orderData->coupon_code:'';

		// Check for Promotion. (Update in cart items(pass by reference). Substract the applicable discount from item's total amount.)
		$cartcheckoutModel->afterPromotionDiscount($cartFormat, $coupon_code);

		foreach ($ordeItemList as $key => $orderItem)
		{
			$db = JFactory::getDbo();

			// Discount detail
			$orderItem->discount = 0;

			if (!empty($cartFormat[$key]['discount']))
			{
				$orderItem->product_final_price -= $cartFormat[$key]['discount'];

				$orderItem->discount = $cartFormat[$key]['discount'];

				if (!empty($cartFormat[$key]['discount_detail']))
				{
					$promoDetail = $cartFormat[$key]['discount_detail'];
					$orderItem->coupon_code = !empty($promoDetail['coupon_code']) ? $promoDetail['coupon_code'] : '';

					if (!empty($promoDetail))
					{
						$orderItem->discount_detail = json_encode($promoDetail);
					}
				}
			}

			$db->insertObject('#__kart_order_item', $orderItem, 'order_item_id');
			$order_item_id = $db->insertid();

			$product_obj = array();
			$product_obj['item_id'] = $orderItem->item_id;
			$product_obj['count'] = $orderItem->product_quantity;

			$product_obj['options'] = !empty($orderItem->product_attributes)?$orderItem->product_attributes:'';

			$prod_info = $Quick2cartModelcart->getProd($product_obj);

			if (!empty($prod_info[1]))
			{
				if (!empty($product_data['att_option']))
				{
					$this->qtc_add_order_items_attributeoptions($order_item_id, $prod_info[1], '0', $product_data['att_option']);
				}
				else
				{
					$this->qtc_add_order_items_attributeoptions($order_item_id, $prod_info[1]);
				}
			}
		}
	}

	/**
	 * Function to remove duplicate products data
	 *
	 * @param   ARRAY  $products_data  array of products data
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function formatProductsData($products_data)
	{
		$formated_product_data = array();

		for ($i = 0; $i < count($products_data); $i++)
		{
			if (empty($products_data[$i]['product_id']))
			{
				continue;
			}

			$product_id = $products_data[$i]['product_id'];
			$product_attribute_details = '';

			if (!empty($products_data[$i]['att_option']))
			{
				$product_attributes = $this->getSelectedAttributesList($products_data[$i]['att_option']);

				$product_attribute_info = $this->getItemAttributeDetails($product_attributes);

				if (!empty($product_attribute_info))
				{
					$product_attribute_details = $this->getItemsSelectedAttributeName($products_data[$i]['att_option'], $product_attribute_info);
				}
			}

			for ($k = $i + 1; $k < count($products_data); $k++)
			{
				if (empty($products_data[$k]['product_id']))
				{
					continue;
				}

				$p_id = $products_data[$k]['product_id'];
				$p_attribute_details = '';

				if (!empty($products_data[$k]['att_option']))
				{
					$p_attributes = $this->getSelectedAttributesList($products_data[$k]['att_option']);

					$p_attribute_info = $this->getItemAttributeDetails($p_attributes);

					if (!empty($p_attribute_info))
					{
						$p_attribute_details = $this->getItemsSelectedAttributeName($products_data[$k]['att_option'], $p_attribute_info);
					}
				}

				if (($p_id == $product_id) && ($p_attribute_details == $product_attribute_details))
				{
					$products_data[$i]['product_quantity'] += $products_data[$k]['product_quantity'];
					$products_data[$k] = '';
				}
			}
		}

		return array_filter($products_data);
	}

	/**
	 * Function to add entry of order item attibute options into items_attributeoptions table
	 *
	 * @param   INT    $order_item_id          order item id
	 *
	 * @param   ARRAY  $attributes_info        array of attributes info
	 *
	 * @param   Int    $orderitemattribute_id  attribute id
	 *
	 * @param   ARRAY  $attributeData          attribute data
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function qtc_add_order_items_attributeoptions($order_item_id, $attributes_info, $orderitemattribute_id = 0, $attributeData = '')
	{
		if (empty($attributes_info) || empty($order_item_id))
		{
			return false;
		}

		foreach ($attributes_info as $key => $attribute_info)
		{
			$itemAttributeOptionDetails = new stdclass;

			if (!empty($orderitemattribute_id))
			{
				$itemAttributeOptionDetails->orderitemattribute_id = $orderitemattribute_id;
			}

			$db = JFactory::getDbo();

			$itemAttributeOptionDetails->order_item_id = $order_item_id;
			$itemAttributeOptionDetails->itemattributeoption_id = $attribute_info['itemattributeoption_id'];

			$itemAttributeOptionDetails->orderitemattribute_name = $attribute_info['itemattributeoption_name'];

			if (!empty($attributeData))
			{
				if (is_array($attributeData[$key]))
				{
					if (!empty($attributeData[$key]['value']))
					{
						$itemAttributeOptionDetails->orderitemattribute_name = $attributeData[$key]['value'];
					}
				}
			}

			$itemAttributeOptionDetails->orderitemattribute_price = $attribute_info['itemattributeoption_price'];
			$itemAttributeOptionDetails->orderitemattribute_prefix = $attribute_info['itemattributeoption_prefix'];

			if (empty($orderitemattribute_id))
			{
				$db->insertObject('#__kart_order_itemattributes', $itemAttributeOptionDetails, 'orderitemattribute_id');
			}
			else
			{
				$db->updateObject('#__kart_order_itemattributes', $itemAttributeOptionDetails, 'orderitemattribute_id');
			}
		}

		$order_item_id = $db->insertid();
	}

	/**
	 * Function to apply taxes on order and order items
	 *
	 * @param   ARRAY  $taxDetails   array of tax details
	 *
	 * @param   INT    $orderId      order id
	 *
	 * @param   INT    $orderItemId  order item id
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function qtc_apply_taxes($taxDetails, $orderId, $orderItemId = '0')
	{
		/* order level tax
		stdClass Object
		(
			[charges] => 37.5
			[order_tax_details] => {"DetailMsg":"12.5%","charges":37.5}
		)

		stdClass Object
		(
			[charges] => 33.84
			[itemsTaxDetail] => Array
				(
					[42] => Array
						(
							[0] => Array
								(
									[taxdetails] => Array
										(
											[1] => Array
												(
													[taxrate_id] => 1
													[name] => vat
													[rate] => 12.000
													[amount] => 3.84
												)

										)

									[taxAmount] => 3.84
								)

						)

				)

		)
		* */

		// If order level tax is configured in quick2cart then send data in "order_tax_details" and update tax against order in kart_orders table
		$orderStatus = new stdclass;

		if (!empty($taxDetails->order_tax_details))
		{
			// Get order details to update order tax amount
			$orderDetails = $this->getOrderDetails($orderId);

			$orderDetails->id = $orderId;

			// Add tax amount to order amount
			$orderDetails->order_tax = $taxDetails->charges;

			$orderDetails->coupon_discount = empty($orderDetails->coupon_discount)?'0':$orderDetails->coupon_discount;
			$orderDetails->order_shipping = empty($orderDetails->order_shipping)?'0':$orderDetails->order_shipping;
			$total = $orderDetails->order_tax + $orderDetails->order_shipping + $orderDetails->original_amount;
			$orderDetails->amount = ($total) - $orderDetails->coupon_discount;

			// If tax details are provided then save the details in json format
			if (!empty($taxDetails->order_tax_details))
			{
				$orderDetails->order_tax_details = $taxDetails->order_tax_details;
			}

			$db = JFactory::getDbo();

			// Update order amount by adding item tax to order amount
			if (!$db->updateObject('#__kart_orders', $orderDetails, 'id'))
			{
				$orderStatus->status = "error";
				$orderStatus->message = JText::_('ERR_CONFIG_SAV_LOGIN');
				$orderStatus->dberror = $db->stderr();
			}
			else
			{
				$orderStatus->status = "success";
				$orderStatus->charges = $taxDetails->charges;
			}
		}

		// If item level tax is configured in quick2cart then send tax data in "itemsTaxDetail" and update tax against order items in order_items table
		if (!empty($taxDetails->itemsTaxDetail))
		{
			$itemTaxDetails = new stdclass;

			// Add tax against each item in order
			foreach ($taxDetails->itemsTaxDetail as $item_tax_detail)
			{
				if (!empty($item_tax_detail))
				{
					// Calculate total tax for item
					foreach ($item_tax_detail['taxdetails'] as $item_variant_tax_detail)
					{
						$item_tax_amount = 0;

						if (!empty($item_variant_tax_detail))
						{
							if (is_array($item_variant_tax_detail))
							{
								$item_tax_amount += !empty($item_variant_tax_detail['amount'])?$item_variant_tax_detail['amount']:'0';
							}
						}
					}

					if (!empty($item_tax_amount))
					{
						// Add tax details for items
						$item_tax_details = '';

						if (!empty($item_tax_detail['taxdetails']))
						{
							// Tax details
							$item_tax_details = json_encode($item_tax_detail['taxdetails']);
						}

						if (!empty($item_tax_detail['product_attribute_names']))
						{
							$p_att_name = $item_tax_detail['product_attribute_names'];
						}
						else
						{
							$p_att_name = '';
						}

						if (!empty($item_tax_detail['product_attributes']))
						{
							$p_att = $item_tax_detail['product_attributes'];
						}
						else
						{
							$p_att = '';
						}

						$db = JFactory::getDbo();

						// Fields to update.
						$fields = array(
							$db->quoteName('item_tax') . ' = ' . $db->quote($item_tax_amount),
							$db->quoteName('item_tax_detail') . " = " . $db->quote($item_tax_details)
						);

						// Conditions for which records should be updated.

						if (!empty($orderItemId))
						{
							$conditions = array(
								$db->quoteName('order_item_id') . ' = ' . $db->quote($orderItemId)
							);
						}
						else
						{
							$item_id = $item_tax_detail['item_id'];

							$conditions = array(
								$db->quoteName('order_id') . ' = ' . $db->quote($orderId),
								$db->quoteName('item_id') . ' = ' . $db->quote($item_id)
							);

							if (!empty($p_att))
							{
								$attribute_condition = array($db->quoteName('product_attributes') . ' = ' . $db->quote($p_att));
								$conditions = array_merge($conditions, $attribute_condition);
							}

							if (!empty($p_att_name))
							{
								$attribute_name_condition = array($db->quoteName('product_attribute_names') . ' = ' . $db->quote($p_att_name));
								$conditions = array_merge($conditions, $attribute_name_condition);
							}
						}

						$query = $db->getQuery(true);

						// Update tax against items in order_items table
						$query->update($db->quoteName('#__kart_order_item'))->set($fields)->where($conditions);

						$db->setQuery($query);

						$result = $db->execute();

						// Update product_final_price in kart_order_item
						$query = $db->getQuery(true);
						$query->select('*');
						$query->from($db->quoteName('#__kart_order_item'));
						$query->where($conditions);
						$db->setQuery($query);
						$itemResult = $db->loadObject();

						if (!empty($itemResult))
						{
							$shippingCharges = empty($itemResult->item_shipcharges)?'0':$itemResult->item_shipcharges;
							$itemDiscount = empty($itemResult->discount)?'0':$itemResult->discount;
							$itemResult->product_final_price = ($itemResult->original_price + $itemResult->item_tax + $shippingCharges) - $itemDiscount;

							$db = JFactory::getDbo();
							$db->updateObject('#__kart_order_item', $itemResult, 'order_item_id');
						}
					}
				}
			}
		}

		return $orderStatus;
	}

	/**
	 * Function to map address fields according to database structure
	 *
	 * @param   ARRAY  $address  array of address details
	 *
	 * @return  ARRAY  mapped array
	 *
	 * @since	2.5.5
	 */
	public function mapUserAddress($address)
	{
		$address = (array) $address;
		$mappedAddress = array();

		if (!empty($address['firstname']))
		{
			$mappedAddress['fnam'] = $address['firstname'];
		}

		if (!empty($address['lastname']))
		{
			$mappedAddress['lnam'] = $address['lastname'];
		}

		if (!empty($address['user_email']))
		{
			$mappedAddress['email1'] = $address['user_email'];
		}

		if (!empty($address['vat_number']))
		{
			$mappedAddress['vat_num'] = $address['vat_number'];
		}

		if (!empty($address['phone']))
		{
			$mappedAddress['phon'] = $address['phone'];
		}

		if (!empty($address['address']))
		{
			$mappedAddress['addr'] = $address['address'];
		}

		if (!empty($address['land_mark']))
		{
			$mappedAddress['land_mark'] = $address['land_mark'];
		}

		if (!empty($address['zipcode']))
		{
			$mappedAddress['zip'] = $address['zipcode'];
		}

		if (!empty($address['country_code']))
		{
			$mappedAddress['country'] = $address['country_code'];
		}

		if (!empty($address['state_code']))
		{
			$mappedAddress['state'] = $address['state_code'];
		}

		if (!empty($address['city']))
		{
			$mappedAddress['city'] = $address['city'];
		}

		return $mappedAddress;
	}

	/**
	 * Function to re-map address fields according to database structure
	 *
	 * @param   ARRAY  $address  array of address details
	 *
	 * @return  ARRAY  re-mapped array
	 *
	 * @since	2.5.5
	 */
	public function reMapUserAddress($address)
	{
		$address = (array) $address;
		$mappedAddress = array();

		if (!empty($address['fnam']))
		{
			$mappedAddress['firstname'] = $address['fnam'];
		}

		if (!empty($address['lnam']))
		{
			$mappedAddress['lastname'] = $address['lnam'];
		}

		if (!empty($address['email1']))
		{
			$mappedAddress['user_email'] = $address['email1'];
		}

		if (!empty($address['vat_num']))
		{
			$mappedAddress['vat_number'] = $address['vat_num'];
		}

		if (!empty($address['phon']))
		{
			$mappedAddress['phone'] = $address['phon'];
		}

		if (!empty($address['addr']))
		{
			$mappedAddress['address'] = $address['addr'];
		}

		if (!empty($address['land_mark']))
		{
			$mappedAddress['land_mark'] = $address['land_mark'];
		}

		if (!empty($address['zip']))
		{
			$mappedAddress['zipcode'] = $address['zip'];
		}

		if (!empty($address['country']))
		{
			$mappedAddress['country_code'] = $address['country'];
		}

		if (!empty($address['state']))
		{
			$mappedAddress['state_code'] = $address['state'];
		}

		if (!empty($address['city']))
		{
			$mappedAddress['city'] = $address['city'];
		}

		return $mappedAddress;
	}

	/**
	 * Function to return order details
	 *
	 * @param   INT  $orderId  order id
	 *
	 * @return  ARRAY  array of order info
	 *
	 * @since	2.5.5
	 */
	public function getOrderDetails($orderId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__kart_orders'));
		$query->where($db->quoteName('id') . ' = ' . $orderId);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Function to return order price
	 *
	 * @param   ARRAY  $products_data  array of product details
	 *
	 * @return  INT  total order price
	 *
	 * @since	2.5.5
	 */
	public function calculateOrderPrice($products_data)
	{
		$orderPrice = 0;

		foreach ($products_data as $product_data)
		{
			$orderPrice += $this->calculateProductPrice($product_data);
		}

		return $orderPrice;
	}

	/**
	 * Function to return product price depending upon the product attributes
	 *
	 * @param   ARRAY  $product_data  array of product details
	 *
	 * @return  INT  total product price
	 *
	 * @since	2.5.5
	 */
	public function calculateProductPrice($product_data)
	{
		$Quick2cartModelcart = new Quick2cartModelcart;
		$product_obj = array();
		$product_obj['item_id'] = $product_data['product_id'];
		$product_obj['count'] = $product_data['product_quantity'];
		$product_obj['options'] = '';

		if (!empty($product_data['att_option']))
		{
			$product_obj['options'] = $this->getSelectedAttributesList($product_data['att_option']);
		}

		$prod_info = $Quick2cartModelcart->getProd($product_obj);

		$prodPrice = $Quick2cartModelcart->getPrice($product_obj['item_id'], 1);

		$basePrice = 0;

		if (isset($prodPrice['discount_price']) && !is_null($prodPrice['discount_price']))
		{
			$basePrice = $prodPrice['discount_price'];
		}
		else
		{
			$basePrice = $prodPrice['price'];
		}

		if (!empty($prod_info[1]))
		{
			foreach ($prod_info[1] as $attri_info)
			{
				if ($attri_info['itemattributeoption_prefix'] == '+')
				{
					$basePrice = $attri_info['itemattributeoption_price'];
				}
				else
				{
					$basePrice = $attri_info['itemattributeoption_price'];
				}
			}
		}

		return $basePrice * $product_obj['count'];
	}

	/**
	 * Function to get list of selectede attributes form products
	 *
	 * @param   ARRAY  $att_option  array of option details
	 *
	 * @return  String comma seperated ids of selected attributes
	 *
	 * @since	2.5.5
	 */
	public function getSelectedAttributesList($att_option)
	{
		$optionsList = '';

		if (!empty($att_option))
		{
			$product_attributes_ids = $att_option;
			$product_attributes_ids = array_filter($product_attributes_ids);

			foreach ($product_attributes_ids as $product_attributes_id)
			{
				if (is_array($product_attributes_id))
				{
					if (!empty($product_attributes_id['value']))
					{
						$attr_opttionIds[] = $product_attributes_id['option_id'];
					}
				}
				else
				{
					$attr_opttionIds[] = $product_attributes_id;
				}
			}

			if (!empty($attr_opttionIds))
			{
				$optionsList = implode(",", $attr_opttionIds);
			}
		}

		// List of selected attributes for product
		return $optionsList;
	}

	/**
	 * Function to get product attributes details
	 *
	 * @param   INT  $itemattributeoption_id  attribute option id
	 *
	 * @return  ARRAY  array of attributes details
	 *
	 * @since	2.5.5
	 */
	public function getItemAttributeDetails($itemattributeoption_id)
	{
		if (!empty($itemattributeoption_id))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__kart_itemattributeoptions'));
			$query->where($db->quoteName('itemattributeoption_id') . ' In (' . $itemattributeoption_id . ')');
			$db->setQuery($query);

			return $db->loadAssocList();
		}
	}

	/**
	 * Function to get name and value of selected attributes for the product
	 *
	 * @param   ARRAY  $att_option_data           array of attributes info
	 *
	 * @param   ARRAY  $selected_attributes_info  array of selected attributes
	 *
	 * @return  String name and value in form name:value
	 *
	 * @since	2.5.5
	 */
	public function getItemsSelectedAttributeName($att_option_data, $selected_attributes_info)
	{
		$productHelper = new productHelper;
		$attr_opttionNames = array();

		foreach ($att_option_data as $attribute_id => $att_option)
		{
			foreach ($selected_attributes_info as $selected_attribute_info)
			{
				if (is_array($att_option))
				{
					if (!empty($att_option['value']))
					{
						if ($att_option['option_id'] == $selected_attribute_info['itemattributeoption_id'])
						{
							$attr_opttionNames[] = $selected_attribute_info['itemattributeoption_name'] . ":" . $att_option['value'];
						}
					}
				}
				else
				{
					if ($att_option == $selected_attribute_info['itemattributeoption_id'])
					{
						if (!empty($attribute_id))
						{
							$attrbuteName = $productHelper->getAttributeName($attribute_id);
							$attr_opttionNames[] = $attrbuteName . ":" . $selected_attribute_info['itemattributeoption_name'];
						}
					}
				}
			}
		}

		return implode(',', $attr_opttionNames);
	}

	/**
	 * Function to apply shipping on order and order items depending upon
	 *
	 * @param   ARRAY  $shippingDetails  array of shipping details
	 *
	 * @param   INT    $orderId          order id
	 *
	 * @param   INT    $orderItemId      order item id
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function qtc_apply_shipping($shippingDetails, $orderId, $orderItemId = '0')
	{
		$orderStatus = new stdclass;

		if (!empty($shippingDetails['itemShipMethDetail']))
		{
			foreach ($shippingDetails['itemShipMethDetail'] as $shippingDetail)
			{
				$orderDetails = new stdclass;
				$orderDetails->id = $orderId;

				if (!empty($shippingDetail['product_attribute_names']))
				{
					$p_att_name = $shippingDetail['product_attribute_names'];
				}
				else
				{
					$p_att_name = '';
				}

				if (!empty($shippingDetail['product_attributes']))
				{
					$p_att = $shippingDetail['product_attributes'];
				}
				else
				{
					$p_att = '';
				}

				$db = JFactory::getDbo();

				// Fields to update.
				$fields = array(
					$db->quoteName('item_shipcharges') . ' = ' . $db->quote($shippingDetail['totalShipCost']),
					$db->quoteName('item_shipDetail') . " = " . $db->quote(json_encode($shippingDetail))
				);

				// Conditions for which records should be updated.
				if (!empty($orderItemId))
				{
					$conditions = array(
						$db->quoteName('order_item_id') . ' = ' . $db->quote($orderItemId)
					);
				}
				else
				{
					$item_id = $shippingDetail['item_id'];

					$conditions = array(
						$db->quoteName('order_id') . ' = ' . $db->quote($orderId),
						$db->quoteName('item_id') . ' = ' . $db->quote($item_id)
					);

					if (!empty($p_att))
					{
						$attribute_condition = array($db->quoteName('product_attributes') . ' = ' . $db->quote($p_att));
						$conditions = array_merge($conditions, $attribute_condition);
					}

					if (!empty($p_att_name))
					{
						$attribute_name_condition = array($db->quoteName('product_attribute_names') . ' = ' . $db->quote($p_att_name));
						$conditions = array_merge($conditions, $attribute_name_condition);
					}
				}

				$query = $db->getQuery(true);

				// Update tax against items in order_items table
				$query->update($db->quoteName('#__kart_order_item'))->set($fields)->where($conditions);

				$db->setQuery($query);

				$result = $db->execute();

				// Update product_final_price in kart_order_item
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__kart_order_item'));
				$query->where($conditions);
				$db->setQuery($query);
				$itemResult = $db->loadObject();

				if (!empty($itemResult))
				{
					$taxCharges = empty($itemResult->item_tax)?'0':$itemResult->item_tax;
					$itemDiscount = empty($itemResult->discount)?'0':$itemResult->discount;
					$itemResult->product_final_price = ($itemResult->original_price + $itemResult->item_shipcharges + $taxCharges) - $itemDiscount;
					$db = JFactory::getDbo();
					$db->updateObject('#__kart_order_item', $itemResult, 'order_item_id');
				}
			}
		}

		if (!empty($shippingDetails['order_shipping_details']))
		{
			$orderDetails = $this->getOrderDetails($orderId);
			$shippingStatus = new stdclass;
			$orderDetails->id = $orderId;

			if (!empty($shippingDetails['charges']))
			{
				$orderDetails->order_shipping = $shippingDetails['charges'];
				$orderDetails->order_shipping_details = $shippingDetails['order_shipping_details'];

				$orderDetails->order_tax = empty($orderDetails->order_tax)?'0':$orderDetails->order_tax;
				$orderDetails->coupon_discount = empty($orderDetails->coupon_discount)?'0':$orderDetails->coupon_discount;
				$orderDetails->order_shipping = empty($orderDetails->order_shipping)?'0':$orderDetails->order_shipping;
				$total = $orderDetails->order_tax + $orderDetails->order_shipping + $orderDetails->original_amount;
				$orderDetails->amount = ($total) - $orderDetails->coupon_discount;
			}

			$db = JFactory::getDbo();

			// Update order amount by adding item tax to order amount
			if (!$db->updateObject('#__kart_orders', $orderDetails, 'id'))
			{
				$orderStatus->status = "error";
				$orderStatus->message = JText::_('ERR_CONFIG_SAV_LOGIN');
				$orderStatus->dberror = $db->stderr();
			}
			else
			{
				$orderStatus->status = "success";
				$orderStatus->charges = $shippingDetails['charges'];
			}
		}

		return $orderStatus;
	}

	/**
	 * Function to get order items details
	 *
	 * @param   INT  $orderId  order id
	 *
	 * @return  ARRAY  array of orders items details
	 *
	 * @since	2.5.5
	 */
	public function getOrderItemDetails($orderId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__kart_order_item'));
		$query->where("order_id = " . $orderId);
		$db->setQuery($query);
		$itemResult = $db->loadObjectList();

		return $itemResult;
	}

	/**
	 * Function to update last billing address for user
	 *
	 * @param   INT  $addressId  address id
	 * @param   INT  $userId     user id
	 *
	 * @return  boolean
	 *
	 * @since	2.5.5
	 */
	protected function updateAsLastUsedBillingAddress($addressId, $userId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__kart_customer_address');
		$query->where('user_id = ' . $userId);

		$db->setQuery($query);
		$userAddresses = $db->loadObjectList();

		foreach ($userAddresses as $address)
		{
			if ($addressId == $address->id)
			{
				$address->last_used_for_billing = 1;
			}
			else
			{
				$address->last_used_for_billing = 0;
			}

			$result = JFactory::getDbo()->updateObject('#__kart_customer_address', $address, 'id');
		}
	}

	/**
	 * Function to update last billing address for user
	 *
	 * @param   INT  $addressId  address id
	 * @param   INT  $userId     user id
	 *
	 * @return  boolean
	 *
	 * @since	2.5.5
	 */
	protected function updateAsLastUsedShippingAddress($addressId, $userId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__kart_customer_address');
		$query->where('user_id = ' . $userId);

		$db->setQuery($query);
		$userAddresses = $db->loadObjectList();

		foreach ($userAddresses as $address)
		{
			if ($addressId == $address->id)
			{
				$address->last_used_for_shipping = 1;
			}
			else
			{
				$address->last_used_for_shipping = 0;
			}

			$result = JFactory::getDbo()->updateObject('#__kart_customer_address', $address, 'id');
		}
	}

	/**
	 * Function to update order original price
	 *
	 * @param   INT  $orderId  order id
	 *
	 * @return  boolean
	 *
	 * @since	2.5.5
	 */
	protected function updateOrderOriginalPrice($orderId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__kart_order_item');
		$query->where('order_id = ' . $orderId);

		$db->setQuery($query);
		$orderItems = $db->loadObjectList();

		$orderOriginalPrice = 0;

		foreach ($orderItems as $items)
		{
			// Order original_price = sum of product_final_price
			$orderOriginalPrice += $items->product_final_price;
		}

		// Update order original price and order amount
		$orderDetails = $this->getOrderDetails($orderId);
		$orderDetails->original_amount = $orderOriginalPrice;

		$orderDetails->order_tax = empty($orderDetails->order_tax)?'0':$orderDetails->order_tax;
		$orderDetails->coupon_discount = empty($orderDetails->coupon_discount)?'0':$orderDetails->coupon_discount;
		$total = $orderDetails->order_tax + $orderDetails->order_shipping + $orderDetails->original_amount;
		$orderDetails->amount = ($total) - $orderDetails->coupon_discount;

		$db = JFactory::getDbo();

		return $db->updateObject('#__kart_orders', $orderDetails, 'id');
	}
}
