<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Ecomm
 * @author     Shivneri <shivnerisystems.com>
 * @copyright  Copyright (c) 2009-2016 shivnerisystems
 * @license    GNU General Public License version 2, or later
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

// Include quick2cart models and helpers
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_quick2cart/models');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_quick2cart/models');
JLoader::register('comquick2cartHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
JLoader::register('storeHelper', JPATH_SITE . '/components/com_quick2cart/helpers/storeHelper.php');
JLoader::register('ProductHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
JLoader::import('promotion', JPATH_SITE . '/components/com_quick2cart/helpers');
JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
JLoader::import('category', JPATH_SITE . '/components/com_quick2cart/models');
JLoader::import('createorder', JPATH_SITE . '/components/com_quick2cart/helpers');

// Include the users model
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models');

// Include the ecomm models, tables and helpers
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_ecomm/models');
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_ecomm/tables');

/**
 * Content service class.
 *
 * @since  1.6
 */

class EcommService
{
    public function __construct()
    {
        $this->db                    = JFactory::getDbo();
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
        $this->storeHelper             = new storeHelper;
        $this->comquick2cartHelper     = new comquick2cartHelper;
        $this->Quick2cartModelCategory = new Quick2cartModelCategory;
    }

    /* User - CART
     * Function to get available coupon code list
     * return array containig status as true and the coupon code details
     */
    public function ecommGetCouponCodes()
    {
        $offers = $this->ecommGetShopOffers($shopId = 3, $published = 1);

        $promotionHelper = new PromotionHelper;

        $this->returnData = array();
        $offersData       = array();

        if ($offers['success'] = 'true' && count($offers['offers']) > 0) {
            foreach ($offers['offers'] as $offer) {
                $data['coupon_code'] = $offer['coupon_code'];
                $data['promoType']   = 1;
                $offerDetails        = $promotionHelper->getValidatePromotions($data)[0];

                if ($offerDetails) {
                    $offerObj                = new stdClass;
                    $offerObj->couponCode    = $offerDetails->coupon_code;
                    $offerObj->discount_type = $offerDetails->discount_type;
                    $offerObj->discount      = $offerDetails->discount;
                    $offerObj->max_discount  = empty($offerDetails->max_discount) ? $offerDetails->discount : $offerDetails->max_discount;

                    $conditionAmount           = json_decode($offerDetails->rules[0]->condition_attribute_value, true)['INR'];
                    $offerObj->conditionAmount = $conditionAmount;

                    $offersData[] = $offerObj;
                }
            }
        }

        if (!empty($offersData)) {
            $this->returnData['success'] = 'true';
            $this->returnData['offers']  = $offersData;
        } else {
            $this->returnData['success'] = 'false';
            $this->returnData['message'] = 'Please try again';
        }

        return $this->returnData;
    }

    /*  - COMMON
     * Function to get the date
     */
    public function getDate()
    {
        $timeZone = JFactory::getConfig()->get('offset');
        date_default_timezone_set($timeZone);
        return date("Y-m-d H:i:s");
    }

    /*  - COMMON
     * Function to save user feedback
     */
    public function ecommSaveFeedback($name, $email, $mobileNo, $rating, $feedback)
    {
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
        $this->returnData['message'] = 'Please try again';

        $userId = JFactory::getUser()->id;

        $feedbackTable = JTable::getInstance('Feedback', 'EcommTable', array('dbo', $this->db));
        $data          = array(
            'user_id'      => $userId,
            'name'         => $name,
            'email'        => $email,
            'mobile_no'    => $mobileNo,
            'rating'       => $rating,
            'feedback'     => $feedback,
            'created_date' => $this->getDate(),
        );

        if ($feedbackTable->save($data)) {
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Thank you for your valuable feedback';
        }

        return $this->returnData;
    }

    /*
     * Function to get the billing details
     * Returns the tax and ship details
     */
    public function ecommGetBillingDetails()
    {
        $plugin = JPluginHelper::getPlugin('qtctax', 'qtc_tax_default');
        $params = new JRegistry($plugin->params);

        $taxData            = new stdClass;
        $taxData->taxType   = 'percentage';
        $taxData->taxAmount = $params->get('tax_per', 0);

        $plugin = JPluginHelper::getPlugin('qtcshipping', 'qtc_shipping_default');
        $params = new JRegistry($plugin->params);

        $shipData                    = new stdClass;
        $shipData->shippingCondition = '<';
        $shipData->shippingLimit     = $params->get('shipping_limit', 0);
        $shipData->shippingAmount    = $params->get('shipping_per', 0);

        return array('tax' => $taxData, 'ship' => $shipData);
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
                $orderDetails      = $orderData['orderDetails'];
                $helperPath        = JPATH_SITE . '/components/com_quick2cart/helpers/createorder.php';
                $createOrderHelper = $this->comquick2cartHelper->loadqtcClass($helperPath, "CreateOrderHelper");

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
                            @$data                       = $this->comquick2cartHelper->sendordermail($orderDetails->orderId);
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

    /* - COMMON
     * Function to get environment details
     */
    public function getEnvironmentDetails()
    {
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $params        = JComponentHelper::getParams('com_ecomm');
        $localhostUrl  = $params->get('localhost_url');
        $staggingUrl   = $params->get('stagging_url');
        $productionUrl = $params->get('production_url');

        $localhostAdminKey  = $params->get('localhost_admin_key');
        $staggingAdminKey   = $params->get('stagging_admin_key');
        $productionAdminKey = $params->get('production_admin_key');

        $environments = array(
            '0' => array(
                'name' => 'Localhost',
                'url'  => $localhostUrl,
                'key'  => $localhostAdminKey,
            ),
            '1' => array(
                'name' => 'Stagging',
                'url'  => $staggingUrl,
                'key'  => $staggingAdminKey,
            ),
            '2' => array(
                'name' => 'Production',
                'url'  => $productionUrl,
                'key'  => $productionAdminKey,
            ),
        );

        if (!empty($localhostUrl) && !empty($localhostAdminKey) && !empty($staggingUrl) && !empty($staggingAdminKey) && !empty($productionUrl) && !empty($productionAdminKey)) {
            $this->returnData['success']      = 'true';
            $this->returnData['environments'] = $environments;
        } else {
            $this->returnData['message'] = 'Please configure the environments.';
        }

        return $this->returnData;
    }

    /* - USER
     * Function to signup the user using mobileNo
     */
    public function ecommGetOtp($mobileNo, $isUser)
    {
        // Clear data
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Check if user exists
        $userId = JUserHelper::getUserId($mobileNo);
        if (empty($userId)) {
            $this->returnData['message'] = 'This mobile no is not registered with us.';
            return $this->returnData;
        }

        // Check if user is already verified and has a userid
        $result = $this->verifyIfUserAlreadyExistsResetPassword($mobileNo);

        if ($result === true) {
            $this->ecommUpdateVerifiedOtpResetPassword($mobileNo);
            return $this->ecommGetOtp($mobileNo, $isUser);
        } else {
            // Check if mobile no has already requested for OTP
            $data = $this->ecommCheckIfAlreadyGeneratedOtpForMobileNoResetPassword($mobileNo);

            // If no then generate the OTP and return
            if (!$data) {
                $this->returnData = $this->ecommGenerateOtpForMobileNoResetPassword($mobileNo, $isUser);
            }
            // If yes then get that OTP and return
            else {
                // Check if the otp is expired or not
                if (!$this->ecommVerifyOtpIsExpired($data['expiration_time'])) {
                    $message = 'Your OTP for ' . $mobileNo . ' is ' . $data['otp'];

                    $dispatcher = JDispatcher::getInstance();
                    JPluginHelper::importPlugin('sms');
                    $dispatcher->trigger('onSmsSendMessage', array($mobileNo, $message));

                    // If otp is not expired
                    $this->returnData['success'] = "true";
                    $this->returnData            = array_merge($this->returnData, $data);
                } else {
                    // If otp is expired then regenerate
                    if ($this->ecommRegenerateOtpForMobileNoResetPassword($mobileNo)) {
                        return $this->ecommGetOtp($mobileNo, $isUser);
                    }
                }
            }
        }

        return $this->returnData;
    }

    /* - USER
     * Function to verify the user is already registered with this mobileNo
     */
    public function verifyIfUserAlreadyExistsResetPassword($mobileNo)
    {
        // Initialise the variables
        $return = false;

        // Get the table instance
        $mobileOtpMapTable = JTable::getInstance('MobileOtpMapResetPassword', 'EcommTable', array('dbo', $this->db));

        // Get the mobile_no and otp details for mobile_no
        $mobileOtpMapTable->load(array('mobile_no' => $mobileNo, 'verified' => '1'));

        // If user_id exists for given mobile_no
        if (!empty($mobileOtpMapTable->user_id)) {
            $return = true;
        }

        return $return;
    }

    /* - USER
     * Function to check if OTP is already generated for given MobileNo
     * If yes then return array containig mobile_no and otp
     * If no then return false
     */
    public function ecommCheckIfAlreadyGeneratedOtpForMobileNoResetPassword($mobileNo)
    {
        try
        {
            $query = $this->db->getQuery(true);
            $query->select($this->db->quoteName(array('id', 'mobile_no', 'otp', 'expiration_time')))
                ->from($this->db->quoteName('#__ecomm_mobile_otp_map_reset_password'))
                ->where($this->db->quoteName('mobile_no') . " = " . $this->db->quote($mobileNo) . " AND " .
                    $this->db->quoteName('verified') . " = " . $this->db->quote('0'));
            $this->db->setQuery($query);
            $otpDetails = $this->db->loadAssoc();

            if (empty($otpDetails)) {
                return false;
            }

            return $otpDetails;
        } catch (Exception $e) {
            return false;
        }
    }

    /* - USER
     * Function to generate OTP for given MobileNo
     * return array containig status as true and mobile_no as specified mobileNo and otp as newly generated otp
     */
    public function ecommGenerateOtpForMobileNoResetPassword($mobileNo, $isUser)
    {
        $params        = JComponentHelper::getParams('com_ecomm');
        $otpTimeout    = $params->get('otp_timeout');
        $otpDigitCount = $params->get('otpDigitCount') - 1;
        $otpStartRange = pow(10, $otpDigitCount);
        $otpEndRange   = pow(10, ($otpDigitCount + 1)) - 1;

        try
        {
            // Generate random number between 100000 and 999999
            $otp = mt_rand($otpStartRange, $otpEndRange);

            // Create the expiration time
            $currentTimestamp    = $this->getDate();
            $expirationTimestamp = strtotime($currentTimestamp) + $otpTimeout;
            $expirationTime      = date('Y-m-d H:i:s', $expirationTimestamp);

            // Initialise the variables
            $query   = $this->db->getQuery(true);
            $columns = array('mobile_no', 'otp', 'expiration_time');
            $values  = array($this->db->quote($mobileNo), $this->db->quote($otp), $this->db->quote($expirationTime));

            if ($isUser == 0) {
                $columns[] = 'is_user';
                $values[]  = $this->db->quote('0');
            }

            // Create the insert query
            $query
                ->insert($this->db->quoteName('#__ecomm_mobile_otp_map_reset_password'))
                ->columns($this->db->quoteName($columns))
                ->values(implode(',', $values));
            $this->db->setQuery($query);

            // If data is inserted successfully
            if ($this->db->execute()) {
                $message = 'Your OTP for ' . $mobileNo . ' is ' . $otp;

                $dispatcher = JDispatcher::getInstance();
                JPluginHelper::importPlugin('sms');
                $dispatcher->trigger('onSmsSendMessage', array($mobileNo, $message));

                $this->returnData['success']         = "true";
                $this->returnData['mobile_no']       = $mobileNo;
                $this->returnData['otp']             = (string) $otp;
                $this->returnData['expiration_time'] = $expirationTime;
            } else {
                $this->returnData['success'] = "false";
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['success'] = "false";
        }
    }

    /* - USER
     * Function to update the verified column
     */
    public function ecommUpdateVerifiedOtpResetPassword($mobileNo)
    {
        $query      = $this->db->getQuery(true);
        $conditions = array(
            $this->db->quoteName('mobile_no') . ' = ' . $this->db->quote($mobileNo),
        );
        $query->delete($this->db->quoteName('#__ecomm_mobile_otp_map_reset_password'));
        $query->where($conditions);
        $this->db->setQuery($query);
        return $this->db->execute();
    }

    /* - USER
     * Function to validate the Mobile No and OTP
     */
    public function ecommVerifyOtp($mobileNo, $otp)
    {
        // Get the generated OTP for given Mobile No
        $data = $this->ecommCheckIfAlreadyGeneratedOtpForMobileNoResetPassword($mobileNo);

        // Check if the otp is expired or not
        if (!$this->ecommVerifyOtpIsExpired($data['expiration_time'])) {
            // If otp is not expired then verify otp and mobile no
            if ($data['mobile_no'] === $mobileNo && $data['otp'] === $otp) {
                // If successfully verified the OTP
                if ($this->ecommUpdateVerifiedOtpResetPassword($mobileNo)) {
                    $this->returnData['success'] = "true";
                }
            }
        } else {
            // If otp is expired then return false and message
            $this->returnData['message'] = "OTP is expired";
        }

        return $this->returnData;
    }

    /*  - USER
     * Function to re-generate OTP for given MobileNo
     * return array containig status as true and mobile_no as specified mobileNo and otp as re-generated otp
     */
    public function ecommRegenerateOtpForMobileNoResetPassword($mobileNo)
    {
        $params        = JComponentHelper::getParams('com_ecomm');
        $otpTimeout    = $params->get('otp_timeout');
        $otpDigitCount = $params->get('otpDigitCount') - 1;
        $otpStartRange = pow(10, $otpDigitCount);
        $otpEndRange   = pow(10, ($otpDigitCount + 1)) - 1;

        try
        {
            // Generate random number between 100000 and 999999
            $otp = mt_rand($otpStartRange, $otpEndRange);

            // Create the expiration time
            $currentTimestamp    = $this->getDate();
            $expirationTimestamp = strtotime($currentTimestamp) + $otpTimeout;
            $expirationTime      = date('Y-m-d H:i:s', $expirationTimestamp);

            $query = $this->db->getQuery(true);

            // Fields to update.
            $fields = array(
                $this->db->quoteName('otp') . ' = ' . $this->db->quote($otp),
                $this->db->quoteName('expiration_time') . ' = ' . $this->db->quote($expirationTime),
            );

            // Conditions for which records should be updated.
            $conditions = array(
                $this->db->quoteName('mobile_no') . ' = ' . $this->db->quote($mobileNo),
            );

            $query->update($this->db->quoteName('#__ecomm_mobile_otp_map_reset_password'))
                ->set($fields)
                ->where($conditions);

            $this->db->setQuery($query);

            $result = $this->db->execute();

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    /* User - ORDER
     * Function to get update the payment details after user choose the payment method
     * return array containig status as true and the payment details
     */
    public function ecommUpdatePaymentDetailsForOrder($paymentDetails)
    {
        $paymentMode  = $paymentDetails['paymentMode'];
        $orderDetails = $paymentDetails['orderDetails'];
        $response     = isset($paymentDetails['response']) ? $paymentDetails['response'] : '';

        require JPATH_SITE . '/components/com_quick2cart/controller.php';
        JLoader::import('payment', JPATH_SITE . '/components/com_quick2cart/models');

        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $paymentModel = new Quick2cartModelpayment;
        if ($paymentMode == 'byorder' || $paymentMode == 'byordercard') {
            $data = $paymentModel->processpayment($orderDetails, $paymentMode, $orderDetails['order_id']);

            if ($data['status'] == 0) {
                $this->returnData['success'] = 'true';
                $this->returnData['message'] = JText::_('Thank you for placing the order! Your order will processed in a while.');
            }
        }

        if ($paymentMode == 'payumoney') {
            $response['udf1'] = $response['txnid'];
            $data             = $paymentModel->processpayment($response, $paymentMode, $response['txnid']);

            if ($data['status'] == 1) {
                $this->returnData['success'] = 'true';
                $this->returnData['message'] = JText::_('Thank you for placing the order! Your order will processed in a while.');
            }
        }

        return $this->returnData;
    }

    /* User - PRODUCT
     * Function to get single order details for given orderId and shopId
     * return array containig status as true and the order details
     */
    public function ecommGetCategoryAndStoreProductId($productId)
    {
        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('product_id, category, store_id')
                ->from($this->db->quoteName('#__kart_items'))
                ->where($this->db->quoteName('product_id') . " = " . (int) $productId);

            $this->db->setQuery($query);

            // Load the list of users found
            $product = $this->db->loadAssoc();

            if (!empty($product)) {
                return $product;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
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
                $productDetails = $this->ecommGetCategoryAndStoreProductId($product->productId);

                $productData = $this->ecommGetSingleProductDetails($productDetails['product_id'], $productDetails['category'], $productDetails['store_id']);

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
                $resultData['productImages']      = $this->ecommGetProductImages($product->productId);

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

    /* Common - BANNER
     * Function to get banner images based on category id
     * return array containig status as true and the payment methods
     */
    public function ecommGetBannerImages($categoryId)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Load the banners form model
        $bannersModel = JModelLegacy::getInstance('banners', 'EcommModel');
        $result       = $bannersModel->getItems();

        if ($result) {
            $data = array();

            foreach ($result as $banner) {
                if ($banner->category_id == $categoryId) {
                    $data[] = $banner;
                }
            }

            if (empty($data)) {
                $this->returnData['message'] = 'No banner images found.';
            } else {
                $this->returnData['success']      = 'true';
                $this->returnData['BannerImages'] = $data;
            }
        } else {
            $this->returnData['message'] = 'No banner images found.';
        }

        return $this->returnData;
    }

    /* - PRODUCT
     * Function to Search product by its title and category
     */
    public function ecommSearch($search)
    {
        $query = $this->db->getQuery(true);
        $query->select('DISTINCT' . ' *');
        $query->from($this->db->quoteName('#__kart_items') . 'AS k');
        $query->where('(' . $this->db->quoteName('k.name') . 'like' . "'%$search%'" . 'OR' . $this->db->quoteName('c.title') . 'like' . "'%$search%'" . ')', 'AND');
        $query->where($this->db->quoteName('k.state') . ' = ' . $this->db->quote('1'));

        $query->JOIN('LEFT', '`#__categories` AS c ON k.category=c.id');
        $query->JOIN('INNER', '`#__kart_base_currency` AS bc ON bc.item_id=k.item_id');

        $this->db->setQuery($query);

        $products = $this->db->loadAssocList();

        try
        {
            // If products found
            if (!empty($products)) {
                $productsDetails = array();

                // Iterate over each product and get details
                foreach ($products as $product) {
                    // id, name, price, image, stock, rating
                    $singleProduct               = array();
                    $singleProduct['name']       = $product['name'];
                    $singleProduct['product_id'] = $product['product_id'];
                    $singleProduct['price']      = $product['price'];
                    $singleProduct['stock']      = $product['stock'];

                    // change by hemant for selling price
                    if ($product['discount_price'] != null) {
                        $singleProduct['sellingPrice'] = $product['discount_price'];
                    } else {
                        $singleProduct['sellingPrice'] = $product['price'];
                    }
                    // end selling price change

                    $singleProduct['store_id']    = $product['store_id'];
                    $singleProduct['category_id'] = $product['category'];
                    $singleProduct['level']       = $product['level'];

                    // Get the product ratings
                    $singleProduct['ratings'] = $this->ecommGetProductRating($product['product_id']);

                    // Get the products available in options
                    $singleProduct['availableIn'] = $this->ecommGetAvailableUnitsForProduct($product['product_id'], $product['price'], $singleProduct['sellingPrice']);

                    // Get all the images
                    $images = json_decode($product['images']);

                    // Get the valid images
                    $images   = $this->getValidImages($images);
                    $imgArray = array();

                    if (isset($images[0]) && !empty($images[0])) {
                        $imgArray['image0'] = $images[0];
                    }

                    $singleProduct['images'][] = $imgArray;

                    $productsDetails[] = $singleProduct;
                }

                $this->returnData['success']  = 'true';
                $this->returnData['products'] = $productsDetails;
            } else {
                $this->returnData['message'] = 'No products found';
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
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

            $this->comquick2cartHelper->updatestatus($orderId, $status, $note, $notify_chk, $store_id);

            $orderDetails = $orderData['orderDetails'];
            $dispatcher   = JDispatcher::getInstance();
            JPluginHelper::importPlugin("system");
            $result = $dispatcher->trigger("ecommOnQuick2cartAfterOrderCancel", array($orderDetails));

            $this->returnData['success'] = "true";

        }

        return $this->returnData;
    }

    /* - PRODUCT
     * Function to get available units for product
     * return array of available units for product
     */
    public function ecommGetAvailableUnitsForProduct($productId, $productPrice, $sellingPrice)
    {
        $productHelper = new ProductHelper;
        $result        = $productHelper->getItemCompleteAttrDetail($productId);
        $attributeData = array('isAvailable' => 'false');

        if (!empty($result)) {
            foreach ($result as $attribute) {
                $attributeData['fieldName']  = $attribute->itemattribute_name;
                $attributeData['compulsory'] = ($attribute->attribute_compulsary) ? 'true' : 'false';

                if (!empty($attribute->optionDetails)) {
                    $optionData = array();

                    foreach ($attribute->optionDetails as $option) {
                        $availableOption['optionId']    = $option->itemattributeoption_id;
                        $availableOption['optionName']  = $option->itemattributeoption_name;
                        $availableOption['optionMRP']   = (string) $option->itemattributeoption_price_mrp;
                        $availableOption['optionPrice'] = (string) $option->itemattributeoption_price;
                        $optionData[]                   = $availableOption;
                    }
                    $attributeData['isAvailable'] = 'true';
                    $attributeData['options']     = $optionData;
                }
            }

        }

        return $attributeData;
    }

    /* - PAYMENT
     * Function to get hash key for payment gateway
     * return hask key
     */
    public function ecommGetHashKey($posted)
    {
        // Clear data
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $plugin = JPluginHelper::getPlugin('payment', 'payumoney');
        $params = new JRegistry($plugin->params);

        $key  = $params->get('key', '');
        $salt = $params->get('salt', '');

        if (empty($key) || empty($salt)) {
            $this->returnData['message'] = 'Unable to do transaction.';
        } else {
            $txnId = strtotime($this->getDate());

            $amount      = $posted["amount"];
            $productName = $posted["productInfo"];
            $firstName   = $posted["firstName"];
            $email       = $posted["email"];
            $udf1        = $posted["udf1"];
            $udf2        = $posted["udf2"];
            $udf3        = $posted["udf3"];
            $udf4        = $posted["udf4"];
            $udf5        = $posted["udf5"];

            $payhash_str = $key . '|' . $this->checkNull($txnId) . '|' . $this->checkNull($amount) . '|'
            . $this->checkNull($productName) . '|' . $this->checkNull($firstName) . '|'
            . $this->checkNull($email) . '|' . $this->checkNull($udf1) . '|' . $this->checkNull($udf2)
            . '|' . $this->checkNull($udf3) . '|' . $this->checkNull($udf4) . '|' . $this->checkNull($udf5) . '||||||' . $salt;

            $hash = strtolower(hash('sha512', $payhash_str));

            if (!empty($hash)) {
                $this->returnData['success'] = 'true';
                $this->returnData['hash']    = $hash;
            }
        }

        return $this->returnData;
    }
    /* - PAYMENT
    */
    public function checkNull($value)
    {
        if ($value == null) {
            return '';
        } else {
            return $value;
        }
    }

    /* - USER
     * Function to check if MobileNo is verified or not
     * If yes then return array containig mobile_no and user id
     * If no then return false
     */
    public function ecommCheckIfMobileNoIsVerified($mobileNo)
    {
        try
        {
            $query = $this->db->getQuery(true);
            $query->select($this->db->quoteName(array('mobile_no')))
                ->from($this->db->quoteName('#__ecomm_mobile_otp_map'))
                ->where($this->db->quoteName('mobile_no') . " = " . $this->db->quote($mobileNo) . " AND " .
                    $this->db->quoteName('verified') . " = " . $this->db->quote('1'));
            $this->db->setQuery($query);
            $result = $this->db->loadAssoc();

            if (isset($result['mobile_no'])) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /* - USER
     * Function to register mobile no and password
     */
    public function ecommRegister($mobileNo, $password)
    {
        // check if mobile no is verified or not
        $status = $this->ecommCheckIfMobileNoIsVerified($mobileNo);

        try
        {
            if ($status) {
                // If verified then add the entry of mobile no and password in the users table
                $userTable = JTable::getInstance('User', 'JTable', array('dbo', $this->db));

                $userModel = JModelLegacy::getInstance('User', 'UsersModel');

                // Default user group
                $params       = JComponentHelper::getParams('com_users');
                $newUserGroup = $params->get('new_usertype');

                // Email domain
                $params = JComponentHelper::getParams('com_ecomm');
                $domain = $params->get('emailDomain');

                // Build the data to be stored
                $data = array(
                    'password'  => $password,
                    'username'  => $mobileNo,
                    'name'      => $mobileNo,
                    'email'     => $mobileNo . '@' . $domain,
                    'groups'    => array($newUserGroup),
                    'profile'   => array('phone' => $mobileNo),
                    'sendEmail' => true,
                );

                // Save the data in the table
                if ($userModel->save($data)) {
                    // Get the userId and load the user details
                    $userId = $userModel->getState('user.id');
                    $userTable->load($userId);

                    // Load the table and save the data
                    $mobileOtpMapTable = JTable::getInstance('MobileOtpMap', 'EcommTable', array('dbo', $this->db));
                    $mobileOtpMapTable->load(array('mobile_no' => $mobileNo));

                    // Build the data to be stored
                    $data = array('id' => $mobileOtpMapTable->id, 'user_id' => $userTable->id);

                    // Save the userId in table
                    if ($mobileOtpMapTable->save($data)) {
                        $message = 'Welcome to the Motley family. Your account has been created successfully.';

                        $dispatcher = JDispatcher::getInstance();
                        JPluginHelper::importPlugin('sms');
                        $dispatcher->trigger('onSmsSendMessage', array($mobileNo, $message));

                        $http = new JHttp();
                        $data = array('username' => $mobileNo, 'password' => $password);
                        $url  = JUri::root() . 'index.php?option=com_api&app=users&resource=login';

                        $response       = $http->post($url, $data, array('Content-Type' => 'multipart/form-data'));
                        $data           = json_decode($response->body);
                        $data           = json_decode(json_encode($data->data), true);
                        $data['userId'] = $data['userDetails']['id'];

                        unset($data['id']);
                        unset($data['userid']);
                        unset($data['hash']);
                        unset($data['domain']);
                        unset($data['state']);
                        unset($data['checked_out']);
                        unset($data['checked_out_time']);
                        unset($data['created']);
                        unset($data['created_by']);
                        unset($data['last_used']);
                        unset($data['per_hour']);

                        return ($data);

                    } else {
                        $this->returnData['message'] = 'Failed to add user_id in mobile otp map table.';
                    }
                } else {
                    $this->returnData['message'] = 'Failed to register a user.';
                }
            } else {
                $this->returnData['message'] = 'Please verify your mobile no first.';
            }

            return $this->returnData;
        } catch (Exception $e) {
            return $this->returnData;
        }

    }

    /* - USER
     * Function to update the verified column
     */
    public function ecommVerifyOtpIsExpired($expirationTime)
    {
        $now = $this->getDate();

        // OTP is not yet expired
        if ($now <= $expirationTime) {
            return false;
        }

        return true;
    }

    /* - USER
     * Function to update the verified column
     */
    public function ecommUpdateVerifiedOtp($mobileNo)
    {
        $query = $this->db->getQuery(true);
        $query->update($this->db->quoteName('#__ecomm_mobile_otp_map'))
            ->set($this->db->quoteName('verified') . ' = ' . $this->db->quote('1'))
            ->where($this->db->quoteName('mobile_no') . ' = ' . $this->db->quote($mobileNo));

        $this->db->setQuery($query);

        return $this->db->execute();
    }

    /* - USER
     * Function to validate the Mobile No and OTP
     */
    public function ecommVerifyMobileNoAndOtp($mobileNo, $otp)
    {
        // Get the generated OTP for given Mobile No
        $data = $this->ecommCheckIfAlreadyGeneratedOtpForMobileNo($mobileNo);

        // Check if the otp is expired or not
        if (!$this->ecommVerifyOtpIsExpired($data['expiration_time'])) {
            // If otp is not expired then verify otp and mobile no
            if ($data['mobile_no'] === $mobileNo && $data['otp'] === $otp) {
                // If successfully verified the OTP
                if ($this->ecommUpdateVerifiedOtp($mobileNo)) {
                    $this->returnData['success'] = "true";
                }
            }
        } else {
            // If otp is expired then return false and message
            $this->returnData['message'] = "OTP is expired";
        }

        return $this->returnData;
    }

    /* - USER
     * Function to verify the user is already registered with this mobileNo
     */
    public function verifyIfUserAlreadyExists($mobileNo)
    {
        // Initialise the variables
        $return = false;

        // Get the table instance
        $mobileOtpMapTable = JTable::getInstance('MobileOtpMap', 'EcommTable', array('dbo', $this->db));

        // Get the mobile_no and otp details for mobile_no
        $mobileOtpMapTable->load(array('mobile_no' => $mobileNo, 'verified' => '1'));

        // If user_id exists for given mobile_no
        if (!empty($mobileOtpMapTable->user_id)) {
            $return = true;
        }

        return $return;
    }

    /* - USER
     * Function to signup the user using mobileNo
     */
    public function ecommSignup($mobileNo, $isUser)
    {
        // Clear data
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Check if user is already verified and has a userid
        $result = $this->verifyIfUserAlreadyExists($mobileNo);

        if ($result === true) {
            $this->returnData['message'] = 'You are already a user';
        } else {
            // Check if mobile no has already requested for OTP
            $data = $this->ecommCheckIfAlreadyGeneratedOtpForMobileNo($mobileNo);

            // If no then generate the OTP and return
            if (!$data) {
                $this->returnData = $this->ecommGenerateOtpForMobileNo($mobileNo, $isUser);
            }
            // If yes then get that OTP and return
            else {
                // Check if the otp is expired or not
                if (!$this->ecommVerifyOtpIsExpired($data['expiration_time'])) {
                    $message = 'Your OTP for ' . $mobileNo . ' is ' . $data['otp'];

                    $dispatcher = JDispatcher::getInstance();
                    JPluginHelper::importPlugin('sms');
                    $dispatcher->trigger('onSmsSendMessage', array($mobileNo, $message));

                    // If otp is not expired
                    $this->returnData['success'] = "true";
                    $this->returnData            = array_merge($this->returnData, $data);

                } else {
                    // If otp is expired then regenerate
                    if ($this->ecommRegenerateOtpForMobileNo($mobileNo)) {
                        return $this->ecommSignup($mobileNo, $isUser);
                    }
                }
            }
        }

        return $this->returnData;
    }

    /* - USER
     * Function to check if OTP is already generated for given MobileNo
     * If yes then return array containig mobile_no and otp
     * If no then return false
     */
    public function ecommCheckIfAlreadyGeneratedOtpForMobileNo($mobileNo)
    {
        try
        {
            $query = $this->db->getQuery(true);
            $query->select($this->db->quoteName(array('id', 'mobile_no', 'otp', 'expiration_time')))
                ->from($this->db->quoteName('#__ecomm_mobile_otp_map'))
                ->where($this->db->quoteName('mobile_no') . " = " . $this->db->quote($mobileNo) . " AND " .
                    $this->db->quoteName('verified') . " = " . $this->db->quote('0'));
            $this->db->setQuery($query);
            $otpDetails = $this->db->loadAssoc();

            if (empty($otpDetails)) {
                return false;
            }

            return $otpDetails;
        } catch (Exception $e) {
            return false;
        }
    }

    /* - USER
     * Function to re-generate OTP for given MobileNo
     * return array containig status as true and mobile_no as specified mobileNo and otp as re-generated otp
     */
    public function ecommRegenerateOtpForMobileNo($mobileNo)
    {
        $params        = JComponentHelper::getParams('com_ecomm');
        $otpTimeout    = $params->get('otp_timeout');
        $otpDigitCount = $params->get('otpDigitCount') - 1;
        $otpStartRange = pow(10, $otpDigitCount);
        $otpEndRange   = pow(10, ($otpDigitCount + 1)) - 1;

        try
        {
            // Generate random number between 100000 and 999999
            $otp = mt_rand($otpStartRange, $otpEndRange);

            // Create the expiration time
            $currentTimestamp    = $this->getDate();
            $expirationTimestamp = strtotime($currentTimestamp) + $otpTimeout;
            $expirationTime      = date('Y-m-d H:i:s', $expirationTimestamp);

            $query = $this->db->getQuery(true);

            // Fields to update.
            $fields = array(
                $this->db->quoteName('otp') . ' = ' . $this->db->quote($otp),
                $this->db->quoteName('expiration_time') . ' = ' . $this->db->quote($expirationTime),
            );

            // Conditions for which records should be updated.
            $conditions = array(
                $this->db->quoteName('mobile_no') . ' = ' . $this->db->quote($mobileNo),
            );

            $query->update($this->db->quoteName('#__ecomm_mobile_otp_map'))
                ->set($fields)
                ->where($conditions);

            $this->db->setQuery($query);

            $result = $this->db->execute();

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    /* - USER
     * Function to generate OTP for given MobileNo
     * return array containig status as true and mobile_no as specified mobileNo and otp as newly generated otp
     */
    public function ecommGenerateOtpForMobileNo($mobileNo, $isUser)
    {
        $params        = JComponentHelper::getParams('com_ecomm');
        $otpTimeout    = $params->get('otp_timeout');
        $otpDigitCount = $params->get('otpDigitCount') - 1;
        $otpStartRange = pow(10, $otpDigitCount);
        $otpEndRange   = pow(10, ($otpDigitCount + 1)) - 1;

        try
        {
            // Generate random number between 100000 and 999999
            $otp = mt_rand($otpStartRange, $otpEndRange);

            // Create the expiration time
            $currentTimestamp    = $this->getDate();
            $expirationTimestamp = strtotime($currentTimestamp) + $otpTimeout;
            $expirationTime      = date('Y-m-d H:i:s', $expirationTimestamp);

            // Initialise the variables
            $query   = $this->db->getQuery(true);
            $columns = array('mobile_no', 'otp', 'expiration_time');
            $values  = array($this->db->quote($mobileNo), $this->db->quote($otp), $this->db->quote($expirationTime));

            if ($isUser == 0) {
                $columns[] = 'is_user';
                $values[]  = $this->db->quote('0');
            }

            // Create the insert query
            $query
                ->insert($this->db->quoteName('#__ecomm_mobile_otp_map'))
                ->columns($this->db->quoteName($columns))
                ->values(implode(',', $values));
            $this->db->setQuery($query);

            // If data is inserted successfully
            if ($this->db->execute()) {
                $message = 'Your OTP for ' . $mobileNo . ' is ' . $otp;

                $dispatcher = JDispatcher::getInstance();
                JPluginHelper::importPlugin('sms');
                $dispatcher->trigger('onSmsSendMessage', array($mobileNo, $message));

                $this->returnData['success']         = "true";
                $this->returnData['mobile_no']       = $mobileNo;
                $this->returnData['otp']             = (string) $otp;
                $this->returnData['expiration_time'] = $expirationTime;

            } else {
                $this->returnData['success'] = "false";
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['success'] = "false";
        }
    }

    /* - CATEGORY
     * Function to get all the categories
     * return array containig status as true and the categories
     */
    public function ecommGetAllCategories($level = 1)
    {
        // Clear data
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
        $colors                      = array('#FFA68B', '#FFDE7D', '#96BDFD', '#F2AA60');

        try
        {
            // Load the list of categories found
            $categories = $this->ecommGetCategoriesByLevel($level);

            // If categories found
            if ($categories['success'] == 'true') {
                $categories = $categories['categories'];

                if (count($categories) == 1) {
                    $parentId = $categories[0]['parent_id'];
                    return $this->ecommGetCategoriesByLevel(1, $parentId);
                }

                for ($i = 0; $i < count($categories); $i++) {
                    $categories[$i]['backgroundColor'] = $colors[$i];

                    // This code is for adding the cooming soon message for main categories
                    if ($categories[$i]['title'] == 'Grocery') {
                        $categories[$i]['isAvailable']         = 'true';
                        $categories[$i]['notAvailableMessage'] = '';
                    } else {
                        $categories[$i]['isAvailable']         = 'false';
                        $categories[$i]['notAvailableMessage'] = 'Coming soon';
                    }
                }

                // Push all the categories in returnData
                $this->returnData['success']        = 'true';
                $this->returnData['categories']     = $categories;
                $this->returnData['billingDetails'] = $this->ecommGetBillingDetails();
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /* - CATEGORY
     * Function to get all the categories
     * return array containig status as true and the categories
     */
    public function ecommGetCategoriesByLevel($level, $parentId = 0)
    {
        // Clear data
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('*')
                ->from($this->db->quoteName('#__categories'))
                ->order('lft ASC');

            // If parentId is given
            if ($parentId != 0) {
                $query->where(
                    $this->db->quoteName('extension') . " = " . $this->db->quote('com_quick2cart') . ' AND ' .
                    $this->db->quoteName('published') . " = " . $this->db->quote('1') . ' AND ' .
                    $this->db->quoteName('level') . " = " . $this->db->quote($level) . ' AND ' .
                    $this->db->quoteName('parent_id') . " = " . $this->db->quote($parentId)
                );
            } else {
                $query->where(
                    $this->db->quoteName('extension') . " = " . $this->db->quote('com_quick2cart') . ' AND ' .
                    $this->db->quoteName('published') . " = " . $this->db->quote('1') . ' AND ' .
                    $this->db->quoteName('level') . " = " . $this->db->quote($level)
                );
            }

            $this->db->setQuery($query);

            // Load the list of categories found
            $categories = $this->db->loadAssocList();

            // If categories found
            if (!empty($categories)) {
                $this->returnData['success'] = 'true';
                $data                        = array();

                // Iterate over each category
                foreach ($categories as $category) {
                    // Push the single categories data in the
                    $data[] = $this->getSpecificCategoryDetails($category);
                }

                // Push all the categories in returnData
                $this->returnData['categories'] = $data;
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /* - CATEGORY
     * Function to get all sub-categories for the categoryId
     * return array containig status as true and the categories
     */
    public function ecommGetCategorySublevel($id)
    {
        // Clear data
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('path,level')
                ->from($this->db->quoteName('#__categories'))
                ->where($this->db->quoteName('extension') . " = " . $this->db->quote('com_quick2cart') . ' AND ' .
                    $this->db->quoteName('published') . " = " . $this->db->quote('1') . ' AND ' .
                    $this->db->quoteName('id') . " = " . $this->db->quote($id)
                );

            $this->db->setQuery($query);

            // Load the category found
            $category = $this->db->loadAssoc();

            if (!empty($category['path'])) {
                // Create db and query object
                $query = $this->db->getQuery(true);

                // Build the query
                $query->select('*')
                    ->from($this->db->quoteName('#__categories'))
                    ->where($this->db->quoteName('extension') . " = " . $this->db->quote('com_quick2cart') . ' AND ' .
                        $this->db->quoteName('published') . " = " . $this->db->quote('1') . ' AND ' .
                        $this->db->quoteName('path') . " LIKE " . $this->db->quote('%' . trim($category['path']) . '%') . ' AND ' .
                        $this->db->quoteName('level') . " = " . $this->db->quote(++$category['level'])
                    );

                $this->db->setQuery($query);

                // Load the category found
                $categories = $this->db->loadAssocList();

                // If categories found
                if (!empty($categories)) {
                    $this->returnData['success'] = 'true';
                    $data                        = array();

                    // Iterate over each category
                    foreach ($categories as $category) {
                        // Push the single categories data in the
                        $data[] = $this->getSpecificCategoryDetails($category);
                    }

                    // Push all the categories in returnData
                    $this->returnData['success']    = 'true';
                    $this->returnData['categories'] = $data;
                }
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /* CART
     * Function to get the cart details
     * return array containig status as true and the cart details
     */
    public function ecommApplyCouponCode($couponCode)
    {
        $this->returnData = array();

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin("system");
        $return = $dispatcher->trigger("ecommApplyCouponCode", array($couponCode));
        if ($return[0] == 'true') {
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Coupon code applied successfully';
        } else {
            $this->returnData['success'] = 'false';
            $this->returnData['message'] = 'Failed to apply coupon';
        }

        return $this->returnData;
    }

    /* - PRODUCT
     * Function to get images of the product in cart
     * return array containig images of the product in cart
     */
    public function ecommGetProductImages($productId)
    {
        // Get the product details
        $modelCart      = new Quick2cartModelcart;
        $productDetails = $modelCart->getItemRec($productId);

        // Get all the images
        $images = json_decode($productDetails->images);

        // Get the valid images
        $images = $this->getValidImages($images);

        $productImages = array();

        if (isset($images[0]) && !empty($images[0])) {
            $productImages['Img0'] = $images[0];
        }

        return $productImages;
    }

    /* - STORE
     * Function to get the shop offers
     * return array containig status as true and the shop offers
     */
    public function ecommGetShopOffers($shopId, $published)
    {
        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Columns to fetch from table
            $selectColumns = array('id', 'store_id', 'state', 'name', 'description', 'from_date', 'exp_date', 'coupon_required', 'coupon_code', 'discount_type', 'max_use', 'max_per_user');

            // Build the query
            $query->select('DISTINCT ' . implode(', ', $selectColumns))
                ->from($this->db->quoteName('#__kart_promotions') . 'AS a');

            // IF * then all shops, else specified shop
            if ($shopId != '*') {
                $query->where($this->db->quoteName('store_id') . " = " . $this->db->quote($shopId));
            }

            // If state(published) is * then return all
            if ($published != '*') {
                $query->where($this->db->quoteName('state') . " = " . $this->db->quote($published));
            }

            // Execute the query
            $this->db->setQuery($query);

            // Load the list of offers found
            $offers = $this->db->loadAssocList();

            // If offers found
            if (!empty($offers)) {
                $this->returnData['success'] = 'true';
                $this->returnData['offers']  = $offers;
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /* - STORE
     * Function to get the shop categories
     * return array containig status as true and the shop categories
     */
    public function ecommGetShopCategories($shopId)
    {
        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('DISTINCT (' . $this->db->quoteName('category') . ')')
                ->from($this->db->quoteName('#__kart_items'))
                ->where($this->db->quoteName('parent') . " = " . $this->db->quote('com_quick2cart') . ' AND ' .
                    $this->db->quoteName('store_id') . " = " . $this->db->quote($shopId) . ' AND ' .
                    $this->db->quoteName('state') . " = " . $this->db->quote('1')
                );
            $this->db->setQuery($query);

            // Load the list of categories found
            $categories = $this->db->loadAssocList();

            // If categories found
            if (!empty($categories)) {
                $categoryIds = array();

                // Iterate over each category and get its id
                foreach ($categories as $category) {
                    if ($category['category']) {
                        $categoryIds[] = $category['category'];
                    }
                }

                // Create query object
                $query = $this->db->getQuery(true);

                // Create the base select statement.
                $query->select('*')
                    ->from($this->db->quoteName('#__categories'))
                    ->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_quick2cart') . ' AND ' .
                        $this->db->quoteName('published') . ' = ' . $this->db->quote('1') . ' AND ' .
                        //$this->db->quoteName('level') . ' = ' . $this->db->quote('2') . ' AND ' .
                        $this->db->quoteName('id') . ' IN (' . implode(', ', $categoryIds) . ')');

                $this->db->setQuery($query);

                // Load the list of categories found
                $categories = $this->db->loadAssocList();

                // If categories found
                if (!empty($categories)) {
                    $this->returnData['success'] = 'true';
                    $data                        = array();

                    // Iterate over each category
                    foreach ($categories as $category) {
                        // Push the single categories data in the
                        $data[] = $this->getSpecificCategoryDetails($category);
                    }

                    // Push all the categories in returnData
                    $this->returnData['categories'] = $data;
                }
            } else {
                $this->returnData['success'] = 'false';
                $this->returnData['message'] = 'This shop does not belong to any of the category';
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /* - CATEGORY
     * Function to get joomla's user details
     * return array containig status as true and the user detials
     */
    public function getSpecificCategoryDetails($category)
    {
        $singleCategoryData = array();

        // Set the optional fields as blank
        $singleCategoryData['image']     = '';
        $singleCategoryData['image_alt'] = '';

        // Get and decode the params field in array format
        $params = json_decode($category['params'], true);

        // Get the id and titile
        $singleCategoryData['id']        = $category['id'];
        $singleCategoryData['parent_id'] = $category['parent_id'];
        $singleCategoryData['title']     = $category['title'];
        $singleCategoryData['level']     = $category['level'];

        // If image is set then get it
        if (isset($params['image']) && !empty($params['image'])) {
            $singleCategoryData['image'] = Juri::root() . $params['image'];
        }

        // If image alt text is set then get it
        if (isset($params['image_alt']) && !empty($params['image_alt'])) {
            $singleCategoryData['image_alt'] = $params['image_alt'];
        }

        return $singleCategoryData;
    }

    /* - USER
     * Function to get joomla's user details
     * return array containig status as true and the user detials
     */
    public function getUserDetails($userId)
    {
        // Load the joomla user model
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models');
        $userModel = JModelLegacy::getInstance('User', 'UsersModel');

        // Get the user details
        $userDetails = $userModel->getItem($userId);

        // If user is exists - check by username exists
        if (isset($userDetails->username) && !empty($userDetails->username)) {
            // Get the user's profile details
            $profileDetails = JUserHelper::getProfile($userId);

            // Bind the profile details
            $userDetails->profile = $profileDetails->profile;

            return $userDetails;
        } else {
            return false;
        }
    }

    /* - CATEGORY
     * Function to get joomla's category details
     * return array containig status as true and the user detials
     */
    public function getCategoryDetails($categoryId)
    {
        // Create db and query object
        $query = $this->db->getQuery(true);

        // Build the query
        $query->select('*')
            ->from($this->db->quoteName('#__categories'))
            ->where($this->db->quoteName('id') . " = " . (int) $categoryId);
        $this->db->setQuery($query);

        // Load the list of categories found
        $category = $this->db->loadAssoc();

        // If category is exists - check by title exists
        if (isset($category['title']) && !empty($category['title'])) {
            return $category;
        }

        return false;
    }

    /* - PRODUCT
     * Function to get the products of given sub-category and shop
     * return array containig status as true and the shop products
     */
    public function ecommGetProductsForShopAndCategory($shopId, $categoryId, $filter = array())
    {
        $model = new Quick2cartModelCategory;

        // Create db and query object
        $query = $this->db->getQuery(true);

        // Select the required fields from the table.
        $query->select($model->getState('list.select', 'a.*'));
        $query->select('CASE WHEN bc.discount_price IS NOT NULL THEN bc.discount_price
                        ELSE a.price
                        END as fprice');
        $query->from('`#__kart_items` AS a');
        $query->where('`category` = ' . $categoryId);
        $query->where('`store_id` = ' . $shopId);
        $query->where('`state` = ' . 1);

        // Adding the filter
        if (!empty($filter)) {
            $search = $filter['keyword'];
            $search = $this->db->Quote('%' . $this->db->escape($search, true) . '%');
            $query->where('( a.name LIKE ' . $search . ' )');
        }

        $query->JOIN('LEFT', '`#__categories` AS c ON c.id=a.category');
        $query->JOIN('INNER', '`#__kart_base_currency` AS bc ON bc.item_id=a.item_id');
        $this->db->setQuery($query);

        // Load the list of stores found
        $products = $this->db->loadAssocList();

        try
        {
            // If products found
            if (!empty($products)) {
                $productsDetails = array();
                $singleProduct   = array();

                // Iterate over each product and get details
                foreach ($products as $product) {
                    // Get the products available in options
                    $productsDetails[] = $this->ecommGetSingleProductDetails($product['product_id'], $$shopId, $categoryId)['productDetails'];
                }

                $this->returnData             = array();
                $this->returnData['success']  = 'true';
                $this->returnData['products'] = $productsDetails;
            } else {
                $this->returnData['message'] = 'No products found.';
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /* PRODUCT / - STORE
    */
    public function getValidImages($images)
    {
        // Initialise variable
        $validImages = array();

        // Iterate over each image
        foreach ($images as $image) {
            // Check all images are valid and present
            $image = $this->comquick2cartHelper->isValidImg($image);

            // If image is valid
            if (!empty($image)) {
                $validImages[] = $image;
            }
        }

        return $validImages;
    }

    /* - PRODUCT
     * Function to get the ratings for the given productId
     * return array containig status as true and the shop payment options
     */
    public function ecommGetProductRating($productId)
    {
        $productRating = '';
        try
        {
            // Get the new query instance
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('*')
                ->from($this->db->quoteName('#__ecomm_ratings'))
                ->where($this->db->quoteName('product_id') . " = " . (int) $productId);

            // Set the query and load the result as associative array
            $this->db->setQuery($query);
            $ratings = $this->db->loadAssocList();

            // If there are rating present
            if (!empty($ratings)) {
                // Get the no of ratings given
                $sumOfAllRatings = 0;
                $noOfRatings     = count($ratings);

                // Iterate over each rating and get the sumation of all rating
                foreach ($ratings as $rating) {
                    $sumOfAllRatings += $rating['rating'];
                }

                // Find the average rating and roundup the value upto 1 decimal places
                $productRating = round(($sumOfAllRatings / $noOfRatings), 1);
            }

            return (string) $productRating;
        } catch (Exception $e) {
            return "";
        }
    }

    /* - PRODUCT
     * Function to get single product details along with the applicable offers
     * return array containig status as true and the product details and the offers if applicatable
     */
    public function ecommGetSingleProductDetails($productId, $categoryId, $shopId)
    {
        // Get the product details
        $modelCart      = new Quick2cartModelcart;
        $productDetails = $modelCart->getItemRec($productId);

        // Get the selling price
        $sellingPrice                 = $modelCart->getPrice($productDetails->product_id, 1)['discount_price'];
        $productDetails->sellingPrice = (empty($sellingPrice)) ? '' : $sellingPrice;

        // If productDetails exits
        if (!empty($productDetails)) {
            // Get all the images
            $images = json_decode($productDetails->images);

            // Get the valid images
            $images = $this->getValidImages($images);

            $productDetails->images = array();

            for ($i = 0; $i < count($images); $i++) {
                $productImage             = new stdClass;
                $productImage->path       = $images[$i];
                $productDetails->images[] = $productImage;
            }

            // Load the promotion helper class and get the promotions
            $promotionHelper = new PromotionHelper;
            $offers          = $promotionHelper->getApplicablePromotionsForProduct($productId, $categoryId, $shopId);

            // If offers exits
            if (!empty($offers)) {
                $productDetails->offers = $offers;
            }

            // Get the product ratings
            $productDetails->ratings = $this->ecommGetProductRating($productId);

            // Get the products available in options
            $productDetails->availableIn = $this->ecommGetAvailableUnitsForProduct($productId, $productDetails->price, $productDetails->sellingPrice);

            // remove unneccessary data
            unset($productDetails->featured);
            unset($productDetails->item_length);
            unset($productDetails->item_width);
            unset($productDetails->item_height);
            unset($productDetails->item_length_class_id);
            unset($productDetails->display_in_product_catlog);
            unset($productDetails->item_weight_class_id);
            unset($productDetails->item_weight);
            unset($productDetails->ordering);
            unset($productDetails->params);
            unset($productDetails->parent);
            unset($productDetails->parent_id);
            unset($productDetails->cdate);
            unset($productDetails->mdate);
            unset($productDetails->state);
            unset($productDetails->sku);
            unset($productDetails->alias);
            unset($productDetails->item_id);
            unset($productDetails->product_type);
            unset($productDetails->min_quantity);
            unset($productDetails->max_quantity);
            unset($productDetails->video_link);
            unset($productDetails->metakey);
            unset($productDetails->metadesc);
            unset($productDetails->slab);
            unset($productDetails->taxprofile_id);
            unset($productDetails->shipProfileId);

            // Build the data to be return
            $this->returnData['success']        = 'true';
            $this->returnData['productDetails'] = $productDetails;
        } else {
            $this->returnData['message'] = 'Product details not found.';
        }

        return $this->returnData;
    }

    /* VENDOR - STORE
     * Function to Save the store
     * return array containig status as true and the store details
     */
    public function ecommUpdateStoreState($shopId, $status)
    {
        $query = $this->db->getQuery(true);

        $query->update($this->db->quoteName('#__kart_store'))
            ->set($this->db->quoteName('live') . ' = ' . $this->db->quote((int) $status))
            ->where($this->db->quoteName('id') . ' = ' . $this->db->quote($shopId));

        $this->db->setQuery($query);

        // If successfully updated the status
        if ($this->db->execute()) {
            $this->returnData['success'] = 'true';
        }

        return $this->returnData;
    }

    /* User - USER
     * Function to get specific user details
     * return array containig status as true and the sale details
     */
    public function ecommGetSpecificUserDetails($userDetails)
    {
        // Check if current person is a user or vendor
        $type = $this->ecommGetUserType($userDetails->id);

        // Return - id, username, name, email, mobile_no, latitude, longitude
        $userData = array();

        $userData['id']       = isset($userDetails->id) ? $userDetails->id : '';
        $userData['is_user']  = ($type != 'exception') ? (string) $type : '';
        $userData['username'] = isset($userDetails->username) ? $userDetails->username : '';
        $userData['name']     = isset($userDetails->name) ? $userDetails->name : '';
        $userData['email']    = isset($userDetails->email) ? $userDetails->email : '';
        $userData['mobileNo'] = isset($userDetails->profile['phone']) ? $userDetails->profile['phone'] : '';

        return $userData;
    }

    /* User - USER
     * Function to get total sale
     * return array containig status as true and the sale details
     */
    public function ecommGetUserType($userId)
    {
        try
        {
            $this->db->setQuery(
                'SELECT `is_user` FROM #__ecomm_mobile_otp_map WHERE `user_id` = ' . $userId
            );

            $result = $this->db->loadAssoc();

            return $result['is_user'];
        } catch (Exception $e) {
            return 'exception';
        }
    }

    /* VENDOR - USER
     * Function to get single user details for given orderId and shopId
     * return array containig status as true and the user details
     */
    public function ecommGetSingleUserDetails($userId)
    {
        // Get all the user details
        $userAllData = $this->getUserDetails($userId);

        // If user is present
        if ($userAllData) {
            // Get the specific user details
            $user = $this->ecommGetSpecificUserDetails($userAllData);

            $this->returnData['success'] = 'true';
            $this->returnData['user']    = $user;
        } else {
            $this->returnData['message'] = 'User with this id is not exists';
        }

        return $this->returnData;
    }

    /* VENDOR - CATEGORY
     * Function to get single category details for given categoryId
     * return array containig status as true and the category details
     */
    public function ecommGetSingleCategoryDetails($categoryId)
    {
        // Get all the category details
        $categoryAllData = $this->getCategoryDetails($categoryId);

        // If category is present
        if ($categoryAllData) {
            // Get the specific category details
            $category = $this->getSpecificCategoryDetails($categoryAllData);

            $this->returnData['success']  = 'true';
            $this->returnData['category'] = $category;
        } else {
            $this->returnData['message'] = 'Category with this id is not exists';
        }

        return $this->returnData;
    }

    /* - STORE
    */
    public function ecommGetSingleStoreDetails($shopId, $fields = '')
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
        $storeData                   = array();

        // Get the store details
        $result = $this->storeHelper->getStoreDetail($shopId);

        if (!empty($result) && isset($result['id']) && !empty($result['id'])) {
            // If needs only specified fields
            if (!empty($fields)) {
                // Get the fields as array
                $fieldsArray = explode(',', $fields);

                // Iterate over each of the field
                foreach ($fieldsArray as $field) {
                    // Check if the specified field exists, if not return blank
                    $storeData[$field] = (isset($result[$field])) ? $result[$field] : '';
                }
            } else {
                // Not mentioned any fields then return all data
                $storeData = $result;
            }

            // Return success as true and the specified fields of the store
            $this->returnData['success'] = 'true';
            $this->returnData['store']   = $storeData;
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

        $order = $this->comquick2cartHelper->getorderinfo($orderId, $shopId);

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
    public function ecommGetAllOrdersForShop($shopId)
    {
        // Initialise the variables
        $orders = array();

        // Load the model
        $modelOrders = JModelLegacy::getInstance('Orders', 'Quick2cartModel');

        // Get the order ids for the shop
        $orderIds = $modelOrders->getOrderIds($shopId);

        // If order id exists
        if (!empty($orderIds)) {
            $orderIds = explode(',', $orderIds);

            // Get the details of each order id
            foreach ($orderIds as $orderId) {
                $order    = $this->comquick2cartHelper->getorderinfo($orderId, $shopId);
                $order    = $this->getFormattedSingleOrderDetails($order['order_info'][0]);
                $orders[] = $order;
            }

            $this->returnData['success'] = 'true';
            $this->returnData['orders']  = $orders;
        }

        return $this->returnData;
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
                // Get the details of each order id
                foreach ($orderIds as $orderId) {
                    $order    = $this->comquick2cartHelper->getorderinfo($orderId['id'], $shopId = 0);
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

        return $singleOrderDetails;

    }

    /* VENDOR - ORDER
     * Function to get orders for shop and status
     * return array containig status as true and the order details
     */
    public function ecommGetOrdersForShopAndStatus($shopId, $status)
    {
        // Get all the orders for the shopId
        $orders = $this->ecommGetAllOrdersForShop($shopId);

        // If there are orders present and need only specific status orders
        if ($orders['success'] == 'true' && !empty($orders['orders'])) {
            $data     = array();
            $statuses = explode(',', $status);

            // Iterate over each order
            foreach ($orders['orders'] as $order) {
                if (in_array($order['order_info'][0]->status, $statuses)) {
                    $data[] = $this->getFormattedSingleOrderDetails($order['order_info'][0]);
                }
            }
        }

        // If there are orders present
        if (!empty($data)) {
            $this->returnData['success'] = 'true';
            $this->returnData['orders']  = $data;
        }

        return $this->returnData;
    }

    /* Common - USER
     * Function to update specific user details
     * return array containig status as true and the user details
     */
    public function ecommUpdateUserDetails($userData)
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models');
        $userModel = JModelLegacy::getInstance('User', 'UsersModel');

        //check if userId
        if (isset($userData['userId']) && !empty($userData['userId'])) {
            $userId = $userData['userId'];
        } else if (isset($userData['username']) && !empty($userData['username'])) {
            //check if username
            JLoader::register('JUserHelper', JPATH_LIBRARIES . '/joomla/user/helper');
            $userId = JUserHelper::getUserId($userData['username']);
        }
        $user = $this->ecommGetSingleUserDetails($userId);

        if ($user['success'] == true) {
            $user = $user['user'];

            $this->returnData = array();

            // Get the assigned user groups for the user
            $groups = $userModel->getAssignedGroups($userId);

            // If groups found, user is exists
            if (!empty($groups)) {
                $data = array();

                $data['id'] = $userId;

                if (isset($userData['name']) && !empty($userData['name'])) {
                    $data['name'] = $userData['name'];
                } else {
                    $data['name'] = $user['name'];
                }

                if (isset($userData['username']) && !empty($userData['username'])) {
                    $data['username'] = $userData['username'];
                } else {
                    $data['username'] = $user['username'];
                }

                if (isset($userData['password']) && !empty($userData['password'])) {
                    $data['password']  = $userData['password'];
                    $data['password2'] = $userData['password'];
                }

                if (isset($userData['address1']) && !empty($userData['address1'])) {
                    $data['profile']['latitude'] = $userData['address1'];
                } else {
                    $data['profile']['latitude'] = $user['address1'];
                }

                if (isset($userData['address2']) && !empty($userData['address2'])) {
                    $data['profile']['longitude'] = $userData['address2'];
                } else {
                    $data['profile']['longitude'] = $user['address2'];
                }

                if (isset($userData['dob']) && !empty($userData['dob'])) {
                    $data['profile']['dob'] = $userData['dob'];
                } else {
                    $data['profile']['dob'] = '1994-01-01';
                }

                // Default user group
                $params       = JComponentHelper::getParams('com_users');
                $newUserGroup = $params->get('new_usertype');

                if (isset($user['groups']) && !empty($user['groups'])) {
                    $data['groups'] = $user['groups'];
                } else {
                    $data['groups'] = array($newUserGroup);
                }

                // Save the data in the table
                if ($userModel->save($data)) {
                    return $this->ecommGetSingleUserDetails($userId);
                } else {
                    $this->returnData['message'] = 'Failed to update the user details.';
                }
            } else {
                $this->returnData['message'] = 'User is not found.';
            }
        }
        return $this->returnData;
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

        $promotionHelper = new PromotionHelper;
        $couponDetails   = $promotionHelper->getSessionCoupon();

        $user = JFactory::getUser();

        // Get the billing address details
        $address = $this->addressService->ecommGetSingleCustomerAddressDetails($billingAddressId);

        if ($address['success'] == 'true') {
            $billingAddress = $address['address'];
        } else {
            $this->returnData['message'] = 'Billing address not found';
            return $this->returnData;
        }

        // If shipping address is enabled
        if ($shippingAddressId) {
            // Get the shipping address details
            $address = $this->addressService->ecommGetSingleCustomerAddressDetails($shippingAddressId);

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

        $createOrderHelper = new CreateOrderHelper;

        //print_r($orderData);die;

        // Place the order
        $result = $createOrderHelper->qtc_place_order($orderData);

        // If order is successful
        if ($result->status == 'success' && isset($result->order_id) && !empty($result->order_id)) {
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

            $this->ecommUpdatePaymentDetailsForOrder($paymentDetails);
            // update payment details end

            $this->returnData = $orderData;
        }

        return $this->returnData;
    }

    /* VENDOR - STORE
     * Function to get total sale
     * return array containig status as true and the sale details
     */
    public function ecommGetTotalSale($vendorId, $storeId, $orderStatuses)
    {
    }

    /* Common - PAYMENT
     * Function to get payment methods
     * return array containig status as true and the payment methods
     */
    public function ecommGetPaymentMethods()
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Active payment methods
        $params = JComponentHelper::getParams('com_quick2cart');
        $result = $params->get('gateways');

        $gateways = array();

        foreach ($result as $value) {
            $obj = new stdClass;

            $data          = JPluginHelper::getPlugin("payment", $value);
            $pluginDetails = json_decode($data->params);

            if (!empty($pluginDetails->plugin_name)) {
                $obj->id    = $value;
                $obj->title = $pluginDetails->plugin_name;

                $gateways[] = $obj;
            }
        }

        if (!empty($gateways)) {
            $this->returnData['success']        = 'true';
            $this->returnData['paymentMethods'] = $gateways;
        } else {
            $this->returnData['message'] = 'No payment methods found.';
        }

        return $this->returnData;
    }

    /* VENDOR - STORE
     * Function to save new store
     * return array containig status as true and the message
     */
    public function ecommSaveStore($storeData)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $input  = new JInput();
        $token  = JHtml::_('form.token');
        $token  = JSession::getFormToken();
        $shopId = empty($storeData['shopId']) ? 0 : $storeData['shopId'];

        $input->set('id', $shopId);
        $input->set('store_creator_id', $storeData['storeOwner']);
        $input->set('title', $storeData['title']);

        $input->set('description', $storeData['description']);
        $input->set('companyname', $storeData['companyName']);

        $input->set('email', $storeData['email']);
        $input->set('phone', $storeData['mobileNo']);
        $input->set('address', $storeData['address']);
        $input->set('land_mark', $storeData['landMark']);
        $input->set('pincode', $storeData['pinCode']);
        $input->set('storecountry', $storeData['countryName']);
        $input->set('qtcstorestate', $storeData['stateName']);
        $input->set('city', $storeData['city']);

        $input->set('paymentMode', $storeData['paymentMode']);
        $input->set('paypalemail', $storeData['paypalEmail']);
        $input->set('otherPayMethod', $storeData['otherPayMethod']);

        // Hard coded for now
        $input->set('option', 'com_quick2cart');
        $input->set('task', 'vendor.save');
        $input->set('btnAction', 'vendor.save');
        $input->set('view', 'vendor');
        $input->set('check', $token);
        $input->set($token, '1');

        // Generate by code - must be unique
        $input->set('storeVanityUrl', 'Motley-Store-' . $shopId);

        // Require helper file
        JLoader::register('storeHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
        $this->storeHelper = new storeHelper;
        $result            = $this->storeHelper->saveVendorDetails($input);

        if ($result['store_id']) {
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Store details saved successfully';
        } else {
            $this->returnData['message'] = 'Failed to save the store details';
        }

        return $this->returnData;
    }

    /* VENDOR - PRODUCT
     * Function to save new product
     * return array containig status as true and the message
     */
    public function ecommSaveProduct($productData)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $input     = new JInput();
        $token     = JHtml::_('form.token');
        $token     = JSession::getFormToken();
        $productId = empty($productData['productId']) ? 0 : $productData['productId'];

        $input->set('pid', $productId);

        $input->set('item_name', $productData['productName']);
        $input->set('item_alias', $productData['productAlias']);
        $input->set('qtc_product_type', 1);
        $input->set('prod_cat', $productData['productCategory']);
        $input->set('description', array('data' => $productData['productDescription']));
        $input->set('sku', $productData['productSku']);
        $input->set('stock', $productData['stock']);
        $input->set('state', $productData['state']);
        $input->set('store_id', $productData['storeId']);
        $input->set('multi_cur', array('INR' => $productData['productPrice']));
        $input->set('multi_dis_cur', array('INR' => $productData['discountPrice']));

        $input->set('option', 'com_quick2cart');
        $input->set('client', 'com_quick2cart');
        $input->set('task', 'product.save');
        $input->set('view', 'product');
        $input->set('check', 'post');
        $input->set($token, '1');

        // Require helper file
        $productId = $this->comquick2cartHelper->saveProduct($input);

        if ($productId > 0) {
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Product details saved successfully';
        } else {
            $this->returnData['message'] = 'Failed to save the product details';
        }

        return $this->returnData;
    }
}
