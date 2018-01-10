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

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Customer_address controller class.
 *
 * @since  1.6
 */
class Quick2cartControllerCreateOrder extends JControllerForm
{
	/**
	 * Function to place order
	 *
	 * @return  order status
	 *
	 * @since	2.5.5
	 */
	public function qtc_place_order()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$params = JComponentHelper::getParams('com_quick2cart');
		$isShippingEnabled = $params->get('shipping', 0);
		$orderData = new stdclass;
		$path = JPATH_SITE . "/components/com_quick2cart/helpers/createorder.php";
		require_once $path;
		$CreateOrderHelper = new CreateOrderHelper;

		$orderData->products_data = $jinput->get('qtcorder_productdetails', '', 'array');

		// Id of user against whome order is being created
		$orderData->userId = $jinput->get('qtcuser', '0', 'int');
		$orderStatus = new stdclass;

		if (empty($orderData->userId))
		{
			$orderStatus->status = "error";
			$orderStatus->message = JText::_('COM_QUICK2CART_SELECT_CLIENT_ERROR_MSG');
			echo json_encode($orderStatus);

			jexit();
		}

		// Array to store id of shipping and billing address ids
		$address = new stdclass;
		$shippingAddressId = $jinput->get('shipping_address', '0', 'int');

		if ($isShippingEnabled)
		{
			if (empty($shippingAddressId))
			{
				$orderStatus->status = "error";
				$orderStatus->message = JText::_('COM_QUICK2CART_SELECT_SHIPPING_ADDRESS_ERROR_MSG');
				echo json_encode($orderStatus);

				jexit();
			}
		}

		$billingAddressId = $jinput->get('billing_address', '0', 'int');

		if (empty($billingAddressId))
		{
			$orderStatus->status = "error";
			$orderStatus->message = JText::_('COM_QUICK2CART_SELECT_BILLING_ADDRESS_ERROR_MSG');
			echo json_encode($orderStatus);

			jexit();
		}

		// Load customer_addressform Model
		if (!class_exists("Quick2cartModelcustomer_addressform"))
		{
			JLoader::register("Quick2cartModelcustomer_addressform", JPATH_SITE . "/components/com_quick2cart/models/customer_addressform.php");
			JLoader::load("Quick2cartModelcustomer_addressform");
		}

		$customer_addressFormModel = new Quick2cartModelcustomer_addressform;
		$address->shipping = $customer_addressFormModel->getAddress($shippingAddressId);
		$address->billing = $customer_addressFormModel->getAddress($billingAddressId);
		$orderData->address = $address;

		// Function to add entry to orders table
		$orderStatus = $CreateOrderHelper->qtc_place_order($orderData);

		echo json_encode($orderStatus);

		jexit();
	}

	/**
	 * Function to return product total price depending upon product attributes and product quantity
	 *
	 * @return  total product price
	 *
	 * @since	2.5.5
	 */
	public function qtc_update_product_price()
	{
		$path = JPATH_SITE . "/components/com_quick2cart/models/cart.php";
		require_once $path;
		$Quick2cartModelcart = new Quick2cartModelcart;
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$product_data = $jinput->get('qtcorder_productdetails', '', '');
		$product_id = $jinput->get('prod_id', '0', 'int');
		$pc_number = $jinput->get('container_number', '0', 'int');

		$product_obj['item_id'] = $product_data[$pc_number]['product_id'];
		$product_obj['count'] = $product_data[$pc_number]['product_quantity']?$product_data[$pc_number]['product_quantity']:1;
		$product_obj['options'] = '';

		if (!empty($product_data[$pc_number]['att_option']))
		{
			$product_attributes_ids = $product_data[$pc_number]['att_option'];
			$product_obj['options'] = '';
			$product_attributes_ids = array_filter($product_attributes_ids);

			foreach ($product_attributes_ids as $product_attributes_id)
			{
				if (is_array($product_attributes_id))
				{
					$product_obj['options'] .= $product_attributes_id['option_id'] . ",";
					$attr_opttionIds[] = $product_attributes_id['option_id'];
				}
				else
				{
					$product_obj['options'] .= $product_attributes_id . ",";
					$attr_opttionIds[] = $product_attributes_id;
				}
			}

			if (!empty($attr_opttionIds))
			{
				$product_obj['options'] = implode(",", $attr_opttionIds);
			}
		}

		$attri_info = $Quick2cartModelcart->getProd($product_obj);

		echo json_encode($attri_info);

		jexit();
	}

	/**
	 * Function to return order total price depending upon product and product quantity
	 *
	 * @return  total order price
	 *
	 * @since	2.5.5
	 */
	public function qtc_update_order_price()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$product_prices = $jinput->get('qtc_product_total', '', 'array');
		$order_price = 0;

		foreach ($product_prices as $product_price)
		{
			$order_price += $product_price['ptotal'];
		}

		echo $order_price;

		jexit();
	}
}
