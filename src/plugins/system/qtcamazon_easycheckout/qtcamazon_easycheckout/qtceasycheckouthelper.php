<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

jimport('joomla.html.html');
jimport('joomla.plugin.helper');

/**
 * Quick2cart main helper
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class Qtceasycheckouthelper
{
	public static $order_id = 0;

	public $plgConfig = array();

	// For now, its for Indian curr
	public $currentCurr = "INR";

	/**
	 * Constructor
	 *
	 * @since   2.2
	 */
	public function __construct()
	{
		$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

		if (!class_exists('comquick2cartHelper'))
		{
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		$this->qtcmainHelper = new comquick2cartHelper;

		$plugin = JPluginHelper::getPlugin('system', 'qtcamazon_easycheckout');
		$params = json_decode($plugin->params);

		$this->plgConfig['merchantID'] = $params->merchantID;
		$this->plgConfig['accessKeyID'] = $params->accessKeyID;
		$this->plgConfig['secretKeyID'] = $params->secretKeyID;
	}

	/**
	 * This function amazon checkout buttom's html
	 *
	 * @param   string  $order_id  order id
	 *
	 * @since   2.2
	 *
	 * @return   null
	 */
	public function getAmazonCheckoutButton($order_id)
	{
		if (empty($order_id))
		{
			$retMsg = "<div class='alert alert-danger'>" .
			JText::_('PLUG_AMAZON_SOMTHING_WRONG_WHILE_PLACING_THE_ORDER') .
			JText::_('PLUG_AMAZON_ERROR_INVALID_ORDER_ID') .
			"</div>";

			return $retMsg;
		}

		// Define constant which you have to use in get cart xml function
		define('QTC_AMAZON_INT_ORDER_ID', $order_id);

		// Plug path var/www/html/qtchtml/bookshop2/plugins/system/qtcamazon_easycheckout
		$plugPath = dirname(dirname(__FILE__));

		// Require in library
		ini_set('include_path', $plugPath . "/lib/cart_processing");
		$plPath = JPATH_SITE . '/plugins/system/qtcamazon_easycheckout/';
		require_once $plPath . 'lib/cart_processing/signature/merchant/cart/html/MerchantHTMLCartFactory.php';
		require_once $plPath . 'lib/cart_processing/signature/common/cart/xml/XMLCartFactory.php';
		require_once $plPath . 'lib/cart_processing/signature/common/signature/SignatureCalculator.php';

		$plugin = JPluginHelper::getPlugin('system', 'qtcamazon_easycheckout');
		$params = json_decode($plugin->params);

		$merchantID = $params->merchantID;
		$accessKeyID = $params->accessKeyID;
		$secretKeyID = $params->secretKeyID;

		// XML cart demo: Create the cart and the signature
		$cartFactory = XMLCartFactory::getInstanceForCurrentMode(Q2C_AMAZOON_CKOUT_PLUG_WORKING_MODE);
		$data = $cartFactory->getCART_JAVASCRIPT_START();

		// XML cart demo: Create the cart and the signature
		// $cartFactory = new XMLCartFactory;
		$calculator = new SignatureCalculator;

		$cart = $cartFactory->getSignatureInput($merchantID, $accessKeyID);
		$signature = $calculator->calculateRFC2104HMAC($cart, $secretKeyID);

		$cartHtml = $cartFactory->getCartHTML($merchantID, $accessKeyID, $signature);

		return $cartHtml;
	}

	/**
	 * This function returns the order item xml string. Called by XMLCartFactory->getCartXML() function
	 *
	 * @param   object  $merchantID      orderItem details
	 * @param   object  $awsAccessKeyID  $merchantID
	 *
	 * @return  OBJECT.
	 *
	 * @since   1.6
	 */
	public function getCartXML($merchantID, $awsAccessKeyID)
	{
		if (!defined('QTC_AMAZON_INT_ORDER_ID'))
		{
			$retMsg = "<div class='alert alert-danger'>" .
			JText::_('PLUG_AMAZON_SOMTHING_WRONG_WHILE_PLACING_THE_ORDER') .
			JText::_('PLUG_AMAZON_ERROR_INVALID_ORDER_ID') .
			"</div>";

			return $retMsg;
		}

		// @TODO GET ORDER ID WITH PREFIX
		$order_id = QTC_AMAZON_INT_ORDER_ID;

		$path = JPATH_SITE . '/components/com_quick2cart/models/payment.php';

		if (!class_exists('Quick2cartModelpayment'))
		{
			JLoader::register('Quick2cartModelpayment', $path);
			JLoader::load('Quick2cartModelpayment');
		}

		$Quick2cartModelpayment = new Quick2cartModelpayment;
		$prefix = $Quick2cartModelpayment->generate_prefix($order_id);

		// Primary category configured at amazon seller center. This is for internal review perpose. (Will not display while checkout )
		$merchandPrimaryCat = "Books";
		$orderInfo = $this->qtcmainHelper->getorderinfo($order_id);

		if (empty($orderInfo))
		{
			return "COM_QUICK2CART_ERROR_ORDER_INFO_NOT_LOADED";
		}

		$orderCurrency = $orderInfo['order_info'][0]->currency;
		$orderItems = $orderInfo['items'];

		require_once JPATH_SITE . '/plugins/system/qtcamazon_easycheckout/lib/qtcxml_processing/Array2XML.php';
		$orderArray = array();
		$orderArray['@attributes'] = array(
			/*'xmlns' => 'http://payments.amazon.com/checkout/2008-06-15/' */
			'xmlns' => 'http://payments.amazon.com/checkout/2009-05-15/'
		);

		$orderArray['ClientRequestId'] = $order_id;
		$orderArray['Cart'] = array();
		$orderArray['Cart']['Items']['Item'] = array();

		JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
		$cartmodel = new Quick2cartModelcart;

		foreach ($orderItems  as $orderItem)
		{
			$itemDetail = $cartmodel->getItemRec($orderItem->item_id);
			$itemPrice = $cartmodel->getPrice($orderItem->item_id, 1);
			$modifiedSku = $orderItem->item_id . "-" . $itemDetail->sku;

			$item = array();
			$item['SKU'] = substr($modifiedSku, 0, 39);
			$item['MerchantId'] = $merchantID;
			$item['Title'] = $itemDetail->name;
			$item['Description'] = substr($itemDetail->description, 0, 50);

			// Create price // @make generalised Right now this is only for indian currency INR
			$item['Price']['Amount'] = $orderItem->product_item_price + $orderItem->product_attributes_price;
			$item['Price']['CurrencyCode'] = $this->currentCurr;

			$item['Quantity'] = $orderItem->product_quantity;

			// Create weight field. // Get item wight in KG (for amazon IND)
			// @TODO need to take according to weight class
			$Weight = array();
			$Weight['Amount'] = $itemDetail->item_weight;
			$Weight['Unit'] = "KG";
			$item['Weight'] = $Weight;

			$item['Category'] = $merchandPrimaryCat;

			// Hazmat is required for easyship
			$item['Hazmat'] = false;
			$item['HandlingTime']['MinDays'] = 3;
			$item['HandlingTime']['MaxDays'] = 5;

			/* ORder ITem custom data*/
			$item['ItemCustomData'] = array();
			$item['ItemCustomData']["OrderItemId"] = $orderItem->order_item_id;
			$orderArray['Cart']['Items']['Item'][] = $item;
		}

		$this->addTax_coupon($orderInfo, $orderArray);

		/* $Tax['CartTaxAmounts']['CartTaxAmount']['TaxAmount']['Price']['Amount'] = 10;
		$Tax['CartTaxAmounts']['CartTaxAmount']['TaxAmount']['Price']['CurrencyCode'] = $this->currentCurr
		*/
		/* Order level custom data*/
		$users = JFactory::getUser();
		$orderArray['Cart']['CartCustomData']['userId'] = !empty($users->id) ? $users->id : 0;
		$xml = Array2XML::createXML('Order', $orderArray);
		$xmlStr = $xml->saveXML();

		return $xmlStr;
	}

	/**
	 * Method to get allow rating to bought the product user
	 *
	 * @param   object  $orderInfo    Order details
	 *
	 * @param   array   &$orderArray  Order deail in array (Which will convert to xml)
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function addTax_coupon($orderInfo, &$orderArray)
	{
		$coupon_discount = 0;

		if (!empty($orderInfo['order_info'][0]->coupon_discount))
		{
			$coupon_discount = (float) $orderInfo['order_info'][0]->coupon_discount;
		}

		if (!empty($coupon_discount))
		{
			// For cart level Promotion
			$orderArray['Cart']['CartPromotionId'] = "CART_PROMO-1";

			$promotion1['PromotionId'] = "CART_PROMO-1";
			$promotion1['Description'] = "coupon_code-" . $orderInfo['order_info'][0]->coupon_code;
			$promotion1['Benefit']['FixedAmountDiscount']['Amount'] = $orderInfo['order_info'][0]->coupon_discount;
			$promotion1['Benefit']['FixedAmountDiscount']['CurrencyCode'] = $this->currentCurr;
			$orderArray["Promotions"]['Promotion'] = $promotion1;
		}

		$order_tax = 0;

		if (!empty($orderInfo['order_info'][0]->order_tax))
		{
			$order_tax = (float) $orderInfo['order_info'][0]->order_tax;
		}

		if (!empty($order_tax))
		{
			// For Cart level tax
			$item = array();
			$item['SKU'] = "qtc_cart_level_tax";
			$item['MerchantId'] = $this->plgConfig['merchantID'];
			$item['Title'] = "Tax";
			$item['Description'] = "Tax";

			// Create price // @make generalised Right now this is only for $this->currentCurr
			$item['Price']['Amount'] = $orderInfo['order_info'][0]->order_tax;
			$item['Price']['CurrencyCode'] = $this->currentCurr;

			$item['Quantity'] = 1;

			// Create weight field. // Get item wight in KG (for amazon IND)
			// @TODO need to take according to weight class
			$Weight = array();
			$Weight['Amount'] = "0";
			$Weight['Unit'] = "KG";
			$item['Weight'] = $Weight;

			$merchandPrimaryCat = "Books";
			$item['Category'] = $merchandPrimaryCat;
			$orderArray['Cart']['Items']['Item'][] = $item;
		}
	}

	/**
	 * Method to get allow rating to bought the product user
	 *
	 * @param   string  $option  component name. eg quick2cart for component com_quick2cart etc.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function isComponentEnabled($option)
	{
		$status = 0;

		if ($option)
		{
			// Load lib
			jimport('joomla.filesystem.file');

			if (JFile::exists(JPATH_ROOT . '/components/com_' . $option . '/' . $option . '.php'))
			{
				if (JComponentHelper::isEnabled('com_' . $option, true))
				{
					$status = 1;
				}
			}
		}

		return $status;
	}

	/**
	 * This method process the IOPN notifications
	 *
	 * @param   striing  $NotificationData  component name. eg quick2cart for component com_quick2cart etc.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function processIOPN($NotificationData)
	{
		$NotificationType = '';
		$orderStatus = "";

		if (strpos($NotificationData, 'NewOrderNotification') !== false)
		{
			$NotificationType = 'NewOrderNotification';
			$orderStatus = "";
		}

		if (strpos($NotificationData, 'OrderCancelledNotification') !== false)
		{
			$NotificationType = 'OrderCancelledNotification';
			$orderStatus = "E";
		}

		if (strpos($NotificationData, 'OrderReadyToShipNotification') !== false)
		{
			$NotificationType = 'OrderReadyToShipNotification';
			$orderStatus = "C";
		}

		require_once JPATH_SITE . '/plugins/system/qtcamazon_easycheckout/lib/qtcxml_processing/XML2Array.php';
		$notificationDataInArray = XML2Array::createArray($NotificationData);

		if ($NotificationType)
		{
			$this->_formatIOPNData($notificationDataInArray, $NotificationType);
		}
	}

	/**
	 * This method convert Instance order Processing notification in internal Format.
	 *  (For now, we are updating the shipping only)
	 *
	 * @param   Object  $iopnXML           notification xml data in array format
	 * @param   Object  $NotificationType  notification type
	 *
	 * @return  boolean
	 *
	 * @since   2.6
	 */
	private function _formatIOPNData($iopnXML, $NotificationType)
	{
		// Convrt xml array to object
		$iopnXML = json_encode($iopnXML);
		$notification = json_decode($iopnXML, false);
		$NotificationDetail = $notification->$NotificationType;

		$iopnData = array();
		$NotificationReferenceId = (string) $NotificationDetail->NotificationReferenceId;
		$OrderChannel = (string) $NotificationDetail->ProcessedOrder->OrderChannel;
		$AmazonOrderID = (string) $NotificationDetail->ProcessedOrder->AmazonOrderID;
		$OrderDate = (string) $NotificationDetail->ProcessedOrder->OrderDate;
		$BuyerName = (string) $NotificationDetail->ProcessedOrder->BuyerInfo->BuyerName;
		$OrderItems = $NotificationDetail->ProcessedOrder->ProcessedOrderItems->ProcessedOrderItem;

		// In case or one item .. it gives only one std class object
		if (!is_array($OrderItems))
		{
			$tempOrerItems[] = $OrderItems;
			$OrderItems = $tempOrerItems;
		}

		// Get shipping label
		$DisplayableShippingLabel = $NotificationDetail->ProcessedOrder->DisplayableShippingLabel;

		// For tax, ship, coupon on order item level
		$itemLevelCharges = array();

		// For now we are considering for MV=OFF
		$orderLevelShip = 0;
		$order_id = 0;
		$user_id = 0;

		foreach ($OrderItems as $oItem)
		{
			$itemDetail = new stdClass;
			$order_id = $itemDetail->order_id = $oItem->ClientRequestId;

			// Get custom data - In case of tax (as a product) entry - you will not get item custom data
			$itemDetail->OrderItemId = !empty($oItem->ItemCustomData->OrderItemId) ? $oItem->ItemCustomData->OrderItemId: '';
			$user_id = $oItem->CartCustomData->userId;

			$itemTax = 0;
			$itemShip = 0;
			$itemCop = 0;
			$diffItemCharges = $oItem->ItemCharges->Component;

			/* @TODO useful whiile doing changes for item level (multivendor = ON )
			 * $itemLevelCharges[$itemDetail->OrderItemId]["item_tax"] = 0;
			$itemLevelCharges[$itemDetail->OrderItemId]["item_tax_detail"] = "";
			$itemLevelCharges[$itemDetail->OrderItemId]["item_shipcharges"] = 0;
			$itemLevelCharges[$itemDetail->OrderItemId]["item_shipDetail"] = '';*/

			foreach ($diffItemCharges as $itemCharges)
			{
				$chargestype = $itemCharges->Type;

				// Amount for current charge type eg shippin, tax  etc
				$chargestypeAmount = $itemCharges->Charge->Amount;
				$chargestypeAmountCurr = $itemCharges->Charge->CurrencyCode;

				switch ($chargestype)
				{
					case "PrincipalPromo":
					break;

					case "Shipping":

							$orderLevelShip += $chargestypeAmount;

							/* Display name to buyer  // May be used further
							 * $itemLevelCharges[$itemDetail->OrderItemId]["item_shipcharges"] += $chargestypeAmount;
							 * */

							/*$item_shipDetail = array();
							$item_shipDetail['name'] = $DisplayableShippingLabel;
							$item_shipDetail['client'] = "qtcamazon_easycheckout";
							$item_shipDetail['totalShipCost'] = $chargestypeAmount;
							$itemLevelCharges[$itemDetail->OrderItemId]["item_shipDetail"] .= json_encode($item_shipDetail);
							*/

					break;

					case "ShippingPromo":
					break;
					/* NOTE: following chunk of code is used to add STORE AMAZON shipping charges on item level in array */

					/*
					case "Tax":
					case "ShippingTax":
					case"GiftWrapTax":
					case "OtherTax":

							$item_tax_detail = array();
							$item_tax_detail['name'] = $chargestype;
							$item_tax_detail['amount'] = $chargestypeAmount;

							$item_tax_detail['rate'] = '';

							$itemTax += $chargestypeAmount;
							$itemLevelCharges[$itemDetail->OrderItemId]["item_tax"] += $chargestypeAmount;
							$itemLevelCharges[$itemDetail->OrderItemId]["item_tax_detail"] .= json_encode($item_tax_detail);
					break;
					*/
				}
			}
			// @TODO store tax,ship, cop according to item based
		}

		// GETTING CART ITEMS
		JLoader::import('createorder', JPATH_SITE . '/components/com_quick2cart/helpers');
		$CreateOrderHelper = new CreateOrderHelper;

		// Add total shipping at order level
		$detailMsg['charges'] = $orderLevelShip;
		$detailMsg['client'] = "qtcamazon_easycheckout";
		$detailMsg['detailMsg'] = '';

		$shippingDetails['charges'] = $orderLevelShip;
		$shippingDetails['order_shipping_details'] = json_encode($detailMsg);
		$CreateOrderHelper->qtc_apply_shipping($shippingDetails, $order_id);

		/* NOTE: following chunk of code is used to add AMAZON shipping charges on item level */

		/*
		$itemShipMethDetail = array();
		$totalShipCost = 0;

		foreach ($itemLevelCharges as $orderItemId => $charges)
		{
			if ($charges['item_shipcharges'] && $charges['item_shipcharges'])
			{
				$oiShip = array();

				$oiShip['name'] = $DisplayableShippingLabel;
				$oiShip['totalShipCost'] = $charges['item_shipcharges'];
				$oiShip['client'] = "qtcamazon_easycheckout";
				$itemShipMethDetail[$orderItemId] = $oiShip;
				$totalShipCost += $oiShip['totalShipCost'];

				$ShippingAPIData = array();
				$ShippingAPIData['itemShipMethDetail'] = $itemShipMethDetail;

				$CreateOrderHelper->qtc_apply_shipping($ShippingAPIData, $order_id, $orderItemId);
			}
		}
		*/

		// Save Address
		$this->_saveAddress($NotificationDetail, $order_id);

		$orderStatus = "";

		if ($NotificationType == "NewOrderNotification")
		{
			$orderStatus = "P";
		}
		elseif ($NotificationType == "OrderCancelledNotification")
		{
			$orderStatus = "E";
		}
		elseif ($NotificationType == "OrderReadyToShipNotification")
		{
			$orderStatus = "C";
		}

		$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

		if (!class_exists('comquick2cartHelper'))
		{
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		require_once JPATH_SITE . '/components/com_quick2cart/helper.php';
		$qtcmainHelper = new comquick2cartHelper;

		// Update the status
		if (!empty($orderStatus))
		{
			$extraData = array();
			$extraData['prder']['transaction_id'] = $AmazonOrderID;

			$notificationTypeRawData = array();
			$notificationTypeRawData[$NotificationType] = $NotificationDetail;
			$extraData['prder']['extra'] = json_encode($notificationTypeRawData);
			$qtcmainHelper->updatestatus($order_id, $orderStatus, '', 1);
		}
	}

	/**
	 * This method convert Instance order Processing notification in internal Format.
	 *
	 * @param   Object  $NotificationDetail  notification detail
	 * @param   Object  $order_id            Order Id
	 *
	 * @return  boolean
	 *
	 * @since   2.6
	 */
	private function _saveAddress($NotificationDetail, $order_id)
	{
		// ShippingAddress
		$shipping = new stdclass;
		$shipping->firstname = (string) $NotificationDetail->ProcessedOrder->ShippingAddress->Name;
		$shipping->middlename = '';

		// As amazon has only one field for name;
		$shipping->lastname = " ";
		$shipping->vat_number = '';

		if (!empty($NotificationDetail->ProcessedOrder->ShippingAddress->PhoneNumber))
		{
			$shipping->phone = $NotificationDetail->ProcessedOrder->ShippingAddress->PhoneNumber;
		}

		$shipping->address_title = $shipping->firstname;
		$shipping->user_email = (string) $NotificationDetail->ProcessedOrder->BuyerInfo->BuyerEmailAddress;

		$AddressFieldOne = (string) $NotificationDetail->ProcessedOrder->ShippingAddress->AddressFieldOne;
		$AddressFieldTwo = (string) $NotificationDetail->ProcessedOrder->ShippingAddress->AddressFieldTwo;
		$shipping->address = $AddressFieldOne . " " . $AddressFieldTwo;
		$shipping->land_mark = '';
		$shipping->zipcode = (string) $NotificationDetail->ProcessedOrder->ShippingAddress->PostalCode;
		$shipping->city = (string) $NotificationDetail->ProcessedOrder->ShippingAddress->City;

		$country_code = (string) $NotificationDetail->ProcessedOrder->ShippingAddress->CountryCode;

		// Getting DB country id from country code
		require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php';
		$tjGeoHelper = TjGeoHelper::getInstance('TjGeoHelper');
		$country = $tjGeoHelper->getCountryFromTwoDigitCountryCode($country_code);

		if ($country !== false)
		{
			$shipping->country_code = $country->id;
		}
		else
		{
			$shipping->country_code = (string) $NotificationDetail->ProcessedOrder->ShippingAddress->CountryCode;
		}

		// Gettomg state id from country name
		$region = (string) $NotificationDetail->ProcessedOrder->ShippingAddress->State;
		$region = $tjGeoHelper->getRegionFromRegionName($shipping->country_code, $region);

		if ($region !== false)
		{
			$shipping->state_code = $region->id;
		}
		else
		{
			$shipping->state_code = (string) $NotificationDetail->ProcessedOrder->ShippingAddress->State;
		}

		if ($shipping->user_email)
		{
			// Load create order helper and save billing and shipping details
			JLoader::import('createorder', JPATH_SITE . '/components/com_quick2cart/helpers');
			$CreateOrderHelper = new CreateOrderHelper;
			$CreateOrderHelper->qtc_add_billing_address($shipping, $order_id);
			$CreateOrderHelper->qtc_add_shipping_address($shipping, $order_id);
		}
	}
}
