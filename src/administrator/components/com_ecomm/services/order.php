<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Ecomm
 * @author     Shivneri <shivnerisystems.com>
 * @copyright  Copyright (c) 2017-2020 shivnerisystems
 * @license    GNU General Public License version 2, or later
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Ecomm Order service class.
 *
 * @since  1.0
 */

class EcommOrderService
{
    public function __construct()
    {
        $this->db                    = JFactory::getDbo();
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
    }

    /* - ORDER
     * Function to send sms and email
     */
    public function ecommSendOrderNotification($sendEmail, $sendSms, $orderId)
    {
        $order_obj = array();

        if (!empty($orderId)) {
            $orderData        = $this->ecommGetSingleOrderDetails(0, $orderId);
            $this->returnData = array();

            if ($orderData['success'] == 'true') {
                $orderDetails = $orderData['orderDetails'];
                $helperPath   = JPATH_SITE . '/components/com_quick2cart/helpers/createorder.php';
                JLoader::register('comquick2cartHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
                $comquick2cartHelper = new comquick2cartHelper;
                $createOrderHelper   = $comquick2cartHelper->loadqtcClass($helperPath, "CreateOrderHelper");

                $dispatcher = JDispatcher::getInstance();
                JPluginHelper::importPlugin("system");
                $result = $dispatcher->trigger("ecommOnQuick2cartAfterOrderPlace", array($orderDetails));

                $params                 = JComponentHelper::getParams('com_quick2cart');
                $send_email_to_customer = $params->get('send_email_to_customer', 0);
                $after_order_placed     = $params->get('send_email_to_customer_after_order_placed', 0);

                if ($send_email_to_customer == 1) {
                    if ($after_order_placed == 1) {
                        // We are assuming that empty status as pending
                        if (empty($orderDetails->status) || $orderDetails->status == 'P') {
                            @$data                       = $comquick2cartHelper->sendordermail($orderDetails->orderId);
                            $this->returnData['success'] = 'true';
                        }
                    }
                } else {
                    $this->returnData['success'] = 'false';
                    $this->returnData['message'] = JText::_('Sending email is disabled.');
                }
            } else {
                $this->returnData['success'] = 'false';
                $this->returnData['message'] = JText::_('Order details not found.');
            }
        }

        return $this->returnData;
    }

    /* User - ORDER
     * Function to get single order details for given orderId and shopId
     * return array containig status as true and the order details
     */
    public function ecommGetReorderDetails($orderId)
    {
        $productList = array();
        $grandTotal  = 0;
        $price       = 0;
        $totalPrice  = 0;
        $order       = $this->ecommGetSingleOrderDetails($shopId = '', $orderId);

        if ($order['success'] == 'true' && !empty($order['orderDetails'])) {
            foreach ($order['orderDetails']->productDetails as $product) {
                // Require helper file
                JLoader::register('EcommProductService', JPATH_ADMINISTRATOR . '/components/com_ecomm/services/product.php');
                $productService = new EcommProductService;

                $productDetails = $productService->ecommGetCategoryIdAndStoreId($product->productId);

                $productData = $productService->ecommGetSingleProductDetails($productDetails['product_id'], $productDetails['category'], $productDetails['store_id']);

                if ($productData['success'] == 'true' && !empty($productData['productDetails'])) {
                    $productData['productDetails']->order_count = $product->quantity;
                }

                $ifOptionsPresent = false;

                // Get available in options
                if ($productData['productDetails']->availableIn['isAvailable']) {
                    foreach ($productData['productDetails']->availableIn['options'] as $options) {
                        if ($options['optionId'] == $product->optionDetails->optionId) {
                            $ifOptionsPresent                                 = true;
                            $productData['productDetails']->optionId          = $options['optionId'];
                            $productData['productDetails']->availableInOption = $options['optionName'];
                            $price                                            = $options['optionPrice'];
                        }
                    }
                }

                if (!$ifOptionsPresent) {
                    $productData['productDetails']->optionId          = "";
                    $productData['productDetails']->availableInOption = "";
                    $price                                            = $productData['productDetails']->price;
                }

                $productData['productDetails']->productAmount      = $price;
                $productData['productDetails']->productTotalAmount = $price * $productData['productDetails']->order_count;
                $grandTotal += $productData['productDetails']->productTotalAmount;

                // Return data
                $resultData                       = array();
                $resultData['productId']          = $productData['productDetails']->product_id;
                $resultData['productTitle']       = $productData['productDetails']->name;
                $resultData['shopId']             = $productData['productDetails']->store_id;
                $resultData['quantity']           = $productData['productDetails']->order_count;
                $resultData['categoryId']         = $productData['productDetails']->category;
                $resultData['productAmount']      = (string) round($productData['productDetails']->productAmount, 2);
                $resultData['productTotalAmount'] = (string) round($productData['productDetails']->productTotalAmount, 2);
                $resultData['productImages']      = $productService->ecommGetProductImages($product->productId);

                $resultData['price']        = $productData['productDetails']->price;
                $resultData['sellingPrice'] = $productData['productDetails']->sellingPrice;
                $optionData                 = $productData['productDetails']->availableIn;

                foreach ($optionData['options'] as $option) {
                    if ($productData['productDetails']->optionId == $option['optionId']) {
                        $resultData['optionId']    = $option['optionId'];
                        $resultData['optionName']  = $option['optionName'];
                        $resultData['optionMRP']   = $option['optionMRP'];
                        $resultData['optionPrice'] = $option['optionPrice'];
                    }
                }

                $productList[] = $resultData;
            }

            unset($this->returnData['orderDetails']);

            if (!empty($productList)) {
                $this->returnData['success']         = "true";
                $this->returnData['productDetails']  = $productList;
                $this->returnData['totalBillAmount'] = (string) $grandTotal;
            } else {
                $this->returnData['success'] = "Products not found from the order.";
            }
        } else {
            $this->returnData['success'] = "false";
            $this->returnData['message'] = "Order details not found.";
        }

        return $this->returnData;
    }

    /** - ORDER
     * Function ecommCancelOrder.
     */
    public function ecommCancelOrder($orderId)
    {
        $orderDetails = $this->ecommGetSingleOrderDetails('', $orderId);

        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        if ($orderDetails['success'] == 'true') {
            // Remove hardcoded store_id afterwards
            $store_id   = 0;
            $note       = '';
            $notify_chk = 1;
            $status     = 'E';

            JLoader::register('comquick2cartHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
            $comquick2cartHelper = new comquick2cartHelper;
            $comquick2cartHelper->updatestatus($orderId, $status, $note, $notify_chk, $store_id);

            $orderDetails = $orderData['orderDetails'];
            $dispatcher   = JDispatcher::getInstance();
            JPluginHelper::importPlugin("system");
            $result = $dispatcher->trigger("ecommOnQuick2cartAfterOrderCancel", array($orderDetails));

            $this->returnData['success'] = "true";

        }

        return $this->returnData;
    }

    /* VENDOR - ORDER
     * Function to get single order ddetails for given orderId and shopId
     * return array containig status as true and the order details
     */
    public function ecommGetSingleOrderDetails($shopId, $orderId)
    {
        $orders = array();

        JLoader::register('comquick2cartHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
        $comquick2cartHelper = new comquick2cartHelper;
        $order               = $comquick2cartHelper->getorderinfo($orderId, $shopId);

        if (!empty($order) && isset($order['order_info'][0]->id)) {
            $orderData = $order['order_info'][0];

            $orderDetails                  = new stdClass;
            $orderDetails->orderId         = $orderData->order_id;
            $orderDetails->prefix          = $orderData->prefix;
            $orderDetails->status          = $orderData->status;
            $orderDetails->createdOn       = $orderData->cdate;
            $orderDetails->createdBy       = $orderData->user_id;
            $orderDetails->tax             = $orderData->order_tax;
            $orderDetails->shippingCharges = $orderData->order_shipping;

            $totalItemShipCharges = 0;
            $totalItemTaxCharges  = 0;
            $totalItemDiscount    = 0;
            $totalItemPrice       = 0;
            $productsDetails      = array();
            $totalTax             = 0;
            $totalShippingCharges = 0;
            $couponCode           = '';

            foreach ($order['items'] as $item) {
                $product = new stdClass;

                $product->productId     = $item->item_id;
                $product->storeId       = $item->store_id;
                $product->productName   = $item->order_item_name;
                $product->quantity      = $item->product_quantity;
                $product->productAmount = $item->product_item_price;
                $product->totalAmount   = $item->product_attributes_price * $item->product_quantity;

                $product->optionDetails               = new stdClass;
                $product->optionDetails->optionId     = $item->product_attributes;
                $product->optionDetails->optionName   = $item->product_attribute_names;
                $product->optionDetails->optionAmount = (string) $item->product_attributes_price;

                $productsDetails[] = $product;

                $totalItemDiscount += !empty($item->discount) ? $item->discount : 0.00;
                $totalItemPrice += !empty($product->totalAmount) ? $product->totalAmount : 0.00;

                if (!empty($item->coupon_code) && empty($couponCode)) {
                    $couponCode = $item->coupon_code;
                }
            }

            $orderDetails->amount   = (string) round($orderData->amount, 2);
            $orderDetails->subTotal = (string) round($totalItemPrice, 2);

            $orderDetails->discount   = (string) round($totalItemDiscount, 2);
            $orderDetails->couponCode = $couponCode;

            $orderDetails->productDetails = $productsDetails;

            // Address Details
            $orderDetails->userAddressDetails              = new stdClass;
            $orderDetails->userAddressDetails->firstName   = $orderData->firstname;
            $orderDetails->userAddressDetails->middleName  = $orderData->middlename;
            $orderDetails->userAddressDetails->lastName    = $orderData->lastname;
            $orderDetails->userAddressDetails->email       = $orderData->user_email;
            $orderDetails->userAddressDetails->mobileNo    = $orderData->phone;
            $orderDetails->userAddressDetails->address     = $orderData->address;
            $orderDetails->userAddressDetails->landMark    = $orderData->land_mark;
            $orderDetails->userAddressDetails->city        = $orderData->city;
            $orderDetails->userAddressDetails->zipCode     = $orderData->zipcode;
            $orderDetails->userAddressDetails->stateName   = $orderData->state_name;
            $orderDetails->userAddressDetails->countryName = $orderData->country_name;
            $orderDetails->userAddressDetails->latitude    = $orderData->latitude;
            $orderDetails->userAddressDetails->longitude   = $orderData->longitude;

            $this->returnData['success']      = 'true';
            $this->returnData['orderDetails'] = $orderDetails;
        } else {
            $this->returnData['message'] = 'Failed to get the order details';
        }

        return $this->returnData;
    }

    /* VENDOR - ORDER
     * Function to get all the pending orders for given store
     * return array containig status as true and the order details
     */
    public function ecommGetAllOrdersForShop($shopId, $startDate, $endDate, $status)
    {
        // Initialise the variables
        $orders = array();

        try {
            // Get the query instance
            $innerQuery = $this->db->getQuery(true);
            $query      = $this->db->getQuery(true);

            // Get the orderIds of orders for current store and status
            $innerQuery->select('DISTINCT' . $this->db->quoteName('order_id'));
            $innerQuery->from($this->db->quoteName('#__kart_order_item'));
            $innerQuery->where($this->db->quoteName('store_id') . " = " . $shopId);

            // Get the sum of amount of given order ids
            $query->select('id');
            $query->from($this->db->quoteName('#__kart_orders'));
            $query->where($this->db->quoteName('id') . ' IN  (' . $innerQuery . ')');

            // If status is provided
            if ($status) {
                $query->where($this->db->quoteName('status') . ' =  "' . $status . '"');
            }

            // If start date is provided
            if ($startDate) {
                $query->where($this->db->quoteName('cdate') . ' >=  "' . $startDate . '"');
            }

            // If end date is provided
            if ($endDate) {
                $query->where($this->db->quoteName('cdate') . ' <=  "' . $endDate . '"');
            }

            // Set the query and get the result
            $this->db->setQuery($query);
            $orderIds = $this->db->loadAssocList();

            // If order id exists
            if (!empty($orderIds)) {
                JLoader::register('comquick2cartHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
                $comquick2cartHelper = new comquick2cartHelper;

                // Get the details of each order id
                foreach ($orderIds as $ordersData) {
                    $order = $this->ecommGetSingleOrderDetails($shopId, $ordersData['id']);

                    if ($order['success'] == 'true') {
                        $orders[] = $order['orderDetails'];

                        unset($this->returnData['success']);
                        unset($this->returnData['orderDetails']);
                    }
                }

                $this->returnData['success'] = 'true';
                $this->returnData['orders']  = $orders;
            } else {
                $this->returnData['message'] = 'No orders found.';
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = 'Failed to get the orders.';
            return $this->returnData;
        }
    }

    /* User - ORDER
     * Function to get all the pending orders for given user
     * return array containig status as true and the order details
     */
    public function eccommGetOrdersForUser($userId, $statuses)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Initialise the variables
        $orders      = array();
        $strStatuses = '';

        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('id')
                ->from($this->db->quoteName('#__kart_orders'))
                ->where($this->db->quoteName('payee_id') . " = " . (int) $userId);

            if (is_array($statuses)) {
                if ($statuses[0] != '*') {
                    $strStatuses = implode("','", $statuses);
                    $query->where($this->db->quoteName('status') . " IN ('" . $strStatuses . "')");
                }
            }

            $query->order('id DESC');

            $this->db->setQuery($query);

            // Load the list of users found
            $orderIds = $this->db->loadAssocList();

            // If order id exists
            if (!empty($orderIds)) {
                JLoader::register('comquick2cartHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
                $comquick2cartHelper = new comquick2cartHelper;

                // Get the details of each order id
                foreach ($orderIds as $orderId) {
                    $order    = $comquick2cartHelper->getorderinfo($orderId['id'], $shopId = 0);
                    $order    = $this->getFormattedSingleOrderDetails($order['order_info'][0]);
                    $orders[] = $order;
                }

                $this->returnData['success'] = 'true';
                $this->returnData['orders']  = $orders;
            } else {
                $this->returnData['message'] = 'No orders found.';
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = 'Failed to get the orders.';
            return $this->returnData;
        }

    }

    /* ORDER
     */
    public function getFormattedSingleOrderDetails($orderData)
    {
        $singleOrderDetails = array();

        $singleOrderDetails['id']         = $orderData->id;
        $singleOrderDetails['order_id']   = $orderData->order_id;
        $singleOrderDetails['status']     = $orderData->status;
        $singleOrderDetails['prefix']     = $orderData->prefix;
        $singleOrderDetails['amount']     = $orderData->amount;
        $singleOrderDetails['user_email'] = $orderData->user_email;
        $singleOrderDetails['firstname']  = $orderData->firstname;
        $singleOrderDetails['middlename'] = $orderData->middlename;
        $singleOrderDetails['lastname']   = $orderData->lastname;
        $singleOrderDetails['address']    = $orderData->address;
        $singleOrderDetails['city']       = $orderData->city;
        $singleOrderDetails['land_mark']  = $orderData->land_mark;
        $singleOrderDetails['zipcode']    = $orderData->zipcode;
        $singleOrderDetails['phone']      = $orderData->phone;
        $singleOrderDetails['cdate']      = $orderData->cdate;
        $singleOrderDetails['latitude']   = $orderData->latitude;
        $singleOrderDetails['longitude']  = $orderData->latitude;

        return $singleOrderDetails;

    }

    /* - ORDER
     * Function to create new order
     * return array containig status as true
     */
    public function ecommCreateOrder($productsDetails, $shippingAddressId, $billingAddressId, $paymentDetails, $couponCode)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
        $shippingAddress             = new stdClass;
        $billingAddress              = new stdClass;
        $products                    = array();

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin("system");
        $result = $dispatcher->trigger("ecommApplyCouponCode", array($couponCode));

        JLoader::import('promotion', JPATH_SITE . '/components/com_quick2cart/helpers');
        $promotionHelper = new PromotionHelper;
        $couponDetails   = $promotionHelper->getSessionCoupon();

        $user = JFactory::getUser();

        // Require helper file
        JLoader::register('EcommAddressService', JPATH_ADMINISTRATOR . '/components/com_ecomm/services/address.php');
        $addressService = new EcommAddressService;

        // Get the billing address details
        $address = $addressService->ecommGetSingleCustomerAddressDetails($billingAddressId);

        if ($address['success'] == 'true') {
            $billingAddress = $address['address'];
        } else {
            $this->returnData['message'] = 'Billing address not found';
            return $this->returnData;
        }

        unset($this->returnData['address']);

        // If shipping address is enabled
        if ($shippingAddressId) {
            // Get the shipping address details
            $address = $addressService->ecommGetSingleCustomerAddressDetails($shippingAddressId);

            if ($address['success'] == 'true') {
                $shippingAddress = $address['address'];
            } else {
                $this->returnData['message'] = 'Shipping address not found';
                return $this->returnData;
            }
        }

        unset($this->returnData['address']);

        // Iterate over the product details
        foreach ($productsDetails as $product) {
            $productData = array();

            // Prepare the product data
            $productData['store_id']         = $product['shopId'];
            $productData['product_id']       = $product['productId'];
            $productData['product_quantity'] = $product['quantity'];

            // Check if optionId is set
            if (!empty($product['optionId'])) {
                $productData['att_option'][$product['shopId']] = $product['optionId'];
            }

            $products[] = $productData;
        }

        // Build the data
        $orderData                    = new stdClass;
        $orderData->address           = new stdClass;
        $orderData->userId            = $user->id;
        $orderData->products_data     = $products;
        $orderData->address->billing  = $billingAddress;
        $orderData->address->shipping = $shippingAddress;
        $orderData->coupon_code       = $couponDetails;

        JLoader::import('createorder', JPATH_SITE . '/components/com_quick2cart/helpers');
        $createOrderHelper = new CreateOrderHelper;

        //print_r($orderData);die;

        // Place the order
        $result = $createOrderHelper->qtc_place_order($orderData);

        // If order is successful
        if ($result->status == 'success' && isset($result->order_id) && !empty($result->order_id)) {
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_quick2cart/models');
            $cartModel = JModelLegacy::getInstance('cart', 'Quick2cartModel');
            $cartModel->empty_cart();

            // Update payment details start
            $data      = $this->ecommGetSingleOrderDetails(0, $result->order_id);
            $orderData = $data['orderDetails'];

            $orderDetails                   = array();
            $orderDetails['total']          = $orderData->amount;
            $orderDetails['mail_addr']      = $orderData->userAddressDetails->email;
            $orderDetails['order_id']       = $orderData->prefix . $orderData->orderId;
            $orderDetails['user_id']        = $orderData->createdBy;
            $orderDetails['comment']        = '';
            $paymentDetails['orderDetails'] = $orderDetails;

            if (isset($paymentDetails) && isset($paymentDetails['response']) && !empty($paymentDetails['response'])) {
                $paymentDetails['response']['txnid'] = $orderData->prefix . $orderData->orderId;
            }

            // Require helper file
            JLoader::register('EcommPaymentService', JPATH_ADMINISTRATOR . '/components/com_ecomm/services/payment.php');
            $paymentService = new EcommPaymentService;
            $paymentService->ecommUpdatePaymentDetailsForOrder($paymentDetails);
            // update payment details end

            $this->returnData = $orderData;
        }

        return $this->returnData;
    }
}
