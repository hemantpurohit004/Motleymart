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

    /* User
     * Function to get available coupon code list
     * return array containig status as true and the coupon code details
     */
    public function ecommGetCouponCodes()
    {
        $offers = $this->ecommGetShopOffers($shopId = 3, $published = 1);

        $promotionHelper = new PromotionHelper;

        $this->returnData = array();
        $offersData = array();

        if($offers['success'] = 'true' && count($offers['offers']) > 0)
        {
            foreach ($offers['offers'] as $offer)
            {
                $data['coupon_code'] = $offer['coupon_code'];
                $data['promoType'] = 1;
                $offerDetails          = $promotionHelper->getValidatePromotions($data)[0];

                if($offerDetails)
                {
                    $offerObj = new stdClass;
                    $offerObj->couponCode = $offerDetails->coupon_code;
                    $offerObj->discount_type = $offerDetails->discount_type;
                    $offerObj->discount = $offerDetails->discount;
                    $offerObj->max_discount = empty($offerDetails->max_discount)? $offerDetails->discount : $offerDetails->max_discount;

                    $offersData[] = $offerObj;
                }
            }
        }

        if(!empty($offersData))
        {
            $this->returnData['success'] = 'true';
            $this->returnData['offers'] = $offersData;
        }
        else
        {
            $this->returnData['success'] = 'false';
            $this->returnData['message'] = 'Please try again';
        }

        return $this->returnData;
    }

     /*
     * Function to get the date
     */
    public function getDate()
    {
        $timeZone = JFactory::getConfig()->get('offset');
        date_default_timezone_set($timeZone);
        return date("Y-m-d H:i:s");
    }

    /*
     * Function to save user feedback
     */
    public function ecommSaveFeedback($name, $email, $mobileNo, $rating, $feedback)
    {
        $this->returnData = array();
        $this->returnData['success'] = 'false';
        $this->returnData['message'] = 'Please try again';

        $userId = JFactory::getUser()->id;

        $feedbackTable = JTable::getInstance('Feedback', 'EcommTable', array('dbo', $this->db));
        $data = array(
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'mobile_no' => $mobileNo,
            'rating' => $rating,
            'feedback' => $feedback,
            'created_date' => $this->getDate()
        );

        if($feedbackTable->save($data))
        {
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Thank you for your valuable feedback';
        }

        return $this->returnData;
    }

    /*
     * Function to get the billing details
     */
    public function ecommGetBillingDetails()
    {
        $plugin = JPluginHelper::getPlugin('qtctax', 'qtc_tax_default');
        $params = new JRegistry($plugin->params);

        $taxData = new stdClass;
        $taxData->taxType = 'percentage';
        $taxData->taxAmount = $params->get('tax_per', 0);


        $plugin = JPluginHelper::getPlugin('qtcshipping', 'qtc_shipping_default');
        $params = new JRegistry($plugin->params);

        $shipData = new stdClass;
        $shipData->shippingCondition = '<';
        $shipData->shippingLimit = $params->get('shipping_limit', 0);
        $shipData->shippingAmount = $params->get('shipping_per', 0);

        return array('tax' => $taxData, 'ship' => $shipData);
    }


    /*
     * Function to send sms and email
     */
    public function ecommSendOrderNotification($sendEmail, $sendSms, $orderId)
    {
        $order_obj = array();

        if (!empty($orderId))
        {
            $orderData = $this->ecommGetSingleOrderDetails(0, $orderId);
            $this->returnData = array();

            if($orderData['success'] == 'true')
            {
                $orderDetails = $orderData['orderDetails'];
                $helperPath = JPATH_SITE . '/components/com_quick2cart/helpers/createorder.php';
                $createOrderHelper = $this->comquick2cartHelper->loadqtcClass($helperPath, "CreateOrderHelper");

                $dispatcher = JDispatcher::getInstance();
                JPluginHelper::importPlugin("system");
                $result = $dispatcher->trigger("ecommOnQuick2cartAfterOrderPlace", array($orderDetails));

                $params   = JComponentHelper::getParams('com_quick2cart');
                $send_email_to_customer = $params->get('send_email_to_customer', 0);
                $after_order_placed = $params->get('send_email_to_customer_after_order_placed', 0);

                if ($send_email_to_customer  == 1)
                {
                    if ($after_order_placed  == 1)
                    {
                        // We are assuming that empty status as pending
                        if (empty($orderDetails->status) || $orderDetails->status == 'P')
                        {
                            @$data = $this->comquick2cartHelper->sendordermail($orderDetails->orderId);
                            $this->returnData['success'] = 'true';
                        }
                    }
                }
                else
                {
                    $this->returnData['success'] = 'false';
                    $this->returnData['message'] = JText::_('Sending email is disabled.');
                }
            }
            else
            {
                $this->returnData['success'] = 'false';
                $this->returnData['message'] = JText::_('Order details not found.');
            }
        }

        return $this->returnData;
    }

    /*
     * Function to get environment details
     */
    public function getEnvironmentDetails()
    {
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $params     = JComponentHelper::getParams('com_ecomm');
        $localhostUrl = $params->get('localhost_url');
        $staggingUrl = $params->get('stagging_url');
        $productionUrl = $params->get('production_url');

        $localhostAdminKey = $params->get('localhost_admin_key');
        $staggingAdminKey = $params->get('stagging_admin_key');
        $productionAdminKey = $params->get('production_admin_key');

        $environments = array(
            '0' => array(
                'name' => 'Localhost',
                'url' => $localhostUrl,
                'key' => $localhostAdminKey
            ),
            '1' => array(
                'name' => 'Stagging',
                'url' => $staggingUrl,
                'key' => $staggingAdminKey
            ),
            '2' => array(
                'name' => 'Production',
                'url' => $productionUrl,
                'key' => $productionAdminKey
            )
        );

        if(!empty($localhostUrl) && !empty($localhostAdminKey) && !empty($staggingUrl) && !empty($staggingAdminKey) && !empty($productionUrl) && !empty($productionAdminKey))
        {
            $this->returnData['success'] = 'true';
            $this->returnData['environments'] = $environments;
        }
        else
        {
            $this->returnData['message'] = 'Please configure the environments.';
        }

        return $this->returnData;
    }

    /*
     * Function to signup the user using mobileNo
     */
    public function ecommGetOtp($mobileNo, $isUser)
    {
        // Clear data
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Check if user exists
        $userId = JUserHelper::getUserId($mobileNo);
        if(empty($userId))
        {
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
                    $result  = $this->ecommSendSms($mobileNo, $message);

                    //if ($result['success'] == 'true')
                    {
                        // If otp is not expired
                        $this->returnData['success'] = "true";
                        $this->returnData            = array_merge($this->returnData, $data);
                    }
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

    /*
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

    /*
     * Function to check if OTP is already generated for given MobileNo
     * If yes then return array containig mobile_no and otp
     * If no then return false
     */
    public function ecommCheckIfAlreadyGeneratedOtpForMobileNoResetPassword($mobileNo)
    {
        $currentTimestamp = date('Y-m-d H:i:s');

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

    /*
     * Function to generate OTP for given MobileNo
     * return array containig status as true and mobile_no as specified mobileNo and otp as newly generated otp
     */
    public function ecommGenerateOtpForMobileNoResetPassword($mobileNo, $isUser)
    {
        $params     = JComponentHelper::getParams('com_ecomm');
        $otpTimeout = $params->get('otp_timeout');
        $otpDigitCount = $params->get('otpDigitCount') - 1;
        $otpStartRange = pow(10,$otpDigitCount);
        $otpEndRange = pow(10,($otpDigitCount + 1)) - 1;

        try
        {
            // Generate random number between 100000 and 999999
            $otp = mt_rand($otpStartRange, $otpEndRange);

            // Create the expiration time
            $currentTimestamp    = date('Y-m-d H:i:s');
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
                $result  = $this->ecommSendSms($mobileNo, $message);

                //if ($result['success'] == 'true')
                {

                    $this->returnData['success']         = "true";
                    $this->returnData['mobile_no']       = $mobileNo;
                    $this->returnData['otp']             = (string) $otp;
                    $this->returnData['expiration_time'] = $expirationTime;
                }
            } else {
                $this->returnData['success'] = "false";
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['success'] = "false";
        }
    }

    /*
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

    /*
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

    /*
     * Function to re-generate OTP for given MobileNo
     * return array containig status as true and mobile_no as specified mobileNo and otp as re-generated otp
     */
    public function ecommRegenerateOtpForMobileNoResetPassword($mobileNo)
    {
        $params     = JComponentHelper::getParams('com_ecomm');
        $otpTimeout = $params->get('otp_timeout');
        $otpDigitCount = $params->get('otpDigitCount') - 1;
        $otpStartRange = pow(10,$otpDigitCount);
        $otpEndRange = pow(10,($otpDigitCount + 1)) - 1;

        try
        {
            // Generate random number between 100000 and 999999
            $otp = mt_rand($otpStartRange, $otpEndRange);

            // Create the expiration time
            $currentTimestamp    = date('Y-m-d H:i:s');
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

    /* User
     * Function to get update the payment details after user choose the payment method
     * return array containig status as true and the payment details
     */
    public function ecommUpdatePaymentDetailsForOrder($paymentDetails)
    {
        $paymentMode = $paymentDetails['paymentMode'];
        $orderDetails = $paymentDetails['orderDetails'];
        $response = isset($paymentDetails['response'])? $paymentDetails['response'] : '' ;

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

    /* User
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

    /* User
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
                            $price  = $options['optionPrice'];
                        }
                    }
                }

                if (!$ifOptionsPresent) {
                    $productData['productDetails']->optionId          = "";
                    $productData['productDetails']->availableInOption = "";
                    $price = $productData['productDetails']->price;
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

                $resultData['price']              = $productData['productDetails']->price;
                $resultData['sellingPrice']       = $productData['productDetails']->sellingPrice;
                $optionData                       = $productData['productDetails']->availableIn;

                foreach ($optionData['options'] as $option)
                {
                    if($productData['productDetails']->optionId == $option['optionId'])
                    {
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

    /* Common
     * Function to get banner images based on category id
     * return array containig status as true and the payment methods
     */
    public function ecommGetBannerImages($categoryId)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Load the address form model
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

    /*
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
                    //$singleProduct['sellingPrice'] = $product['discount_price'];

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
                    $singleProduct['availableIn'] = $this->ecommGetAvailableUnitsForProduct($product['product_id'], $product['price'],$singleProduct['sellingPrice']);

                    // Get all the images
                    $images = json_decode($product['images']);

                    // Get the valid images
                    $images   = $this->getValidImages($images);
                    $imgArray = array();

                    if(isset($images[0]) && !empty($images[0]))
                    {
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

    /**
     * Function getCountryName.
     */
    public function getCountryCode($countryName)
    {
        $query = $this->db->getQuery(true);
        $query->select('id');
        $query->from('#__tj_country');
        $query->where('country=' . $this->db->quote($countryName));
        $this->db->setQuery($query);

        return $this->db->loadAssoc()['id'];
    }


    /**
     * Function ecommCancelOrder.
     */
    public function ecommCancelOrder($orderId)
    {
        $orderDetails = $this->ecommGetSingleOrderDetails('', $orderId);

        $this->returnData = array();
        $this->returnData['success']   = 'false';

        if($orderDetails['success'] == 'true')
        {
            // Remove hardcoded store_id afterwards
            $store_id = 0;
            $note = '';
            $notify_chk = 1;
            $status = 'E';

            if($this->updateOrderStatus($orderId, $status, $note, $notify_chk, $store_id))
            {
                $this->returnData['success']   = "true";
            }
        }

        return $this->returnData;
    }

    /**
     * Function getCountryName.
     */
    public function updateOrderStatus($orderid, $status, $note, $notify_chk, $store_id)
    {
         // Update item status
        $this->comquick2cartHelper->updatestatus($orderid, $status, $note, $notify_chk, $store_id);

        /* Save order history
        $orderItems = $this->getOrderItems($orderid);

        foreach ($orderItems as $oitemId)
        {
            // Save order item status history
            $this->comquick2cartHelper->saveOrderStatusHistory($orderid, $oitemId, $status, $note, $notify_chk);
        } */

        return true;
    }

    /**
     * Function getOrderItems.
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
     * Function getCountryName.
     */
    public function getStateCode($stateName)
    {
        $query = $this->db->getQuery(true);
        $query->select('id');
        $query->from('#__tj_region');
        $query->where('region=' . $this->db->quote($stateName));
        $this->db->setQuery($query);

        return $this->db->loadAssoc()['id'];
    }

    /*
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

                    foreach ($attribute->optionDetails as $option)
                    {
                        $availableOption['optionId']   = $option->itemattributeoption_id;
                        $availableOption['optionName'] = $option->itemattributeoption_name;
                        $availableOption['optionMRP'] = (string) $option->itemattributeoption_price_mrp;
                        $availableOption['optionPrice'] = (string) $option->itemattributeoption_price;

                        //$availableOption['optionOrdering'] = $option->ordering;
                        //$availableOption['optionState'] = $option->state;

                        $optionData[] = $availableOption;
                    }
                    $attributeData['isAvailable'] = 'true';
                    $attributeData['options']     = $optionData;
                }
            }

        }

        return $attributeData;
    }

    /*
     * Function to get countries for address
     * return array of countries
     */
    public function ecommGetCountriesForAddress()
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $Quick2cartModelZone = new Quick2cartModelZone;
        $countries           = $Quick2cartModelZone->getCountry();

        if (!empty($countries)) {
            $this->returnData['success']   = 'true';
            $this->returnData['countries'] = $countries;
        }

        return $this->returnData;
    }

    /*
     * Function to get states for country
     * return array of states
     */
    public function ecommGetStatesForCountry($countryId)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
        $data                        = array();

        $Quick2cartModelZone = new Quick2cartModelZone;
        $states              = $Quick2cartModelZone->getRegionList($countryId);

        if (!empty($states)) {
            $this->returnData['success'] = 'true';

            for ($i = 0; $i < count($states); $i++) {
                $data[$i]['id']        = $states[$i]->region_id;
                $data[$i]['stateName'] = $states[$i]->region;
            }

            $this->returnData['states'] = $data;
        }

        return $this->returnData;
    }

    /*
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
            $txnId = strtotime(date('Y-m-d H:i:s'));

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

    public function checkNull($value)
    {
        if ($value == null) {
            return '';
        } else {
            return $value;
        }
    }

    /*
     * Function to get hash key for payment gateway
     * return hask key
     */
    /*public function ecommGetHashKey($orderId)
    {
    // Clear data
    $this->returnData = array();
    $this->returnData['success'] = 'false';

    $plugin = JPluginHelper::getPlugin('payment', 'payumoney');
    $params = new JRegistry($plugin->params);

    $key=$params->get('key','');
    $salt=$params->get('salt','');

    if (empty($key) || empty($salt))
    {
    $this->returnData['success'] = 'false';
    $this->returnData['message'] = 'Unable to do transaction.';
    }
    else
    {

    $orderAllData = $this->ecommGetSingleOrderDetails(0, $orderId);
    if($orderAllData['success'] == 'true')
    {
    $orderFormattedData = $this->getFormattedSingleOrderDetails($orderAllData['order']['order_info'][0]);
    $otherDetails = '';

    $data = array();
    $data['txnid'] = $orderFormattedData['prefix'].$orderFormattedData['order_id'];
    $data['amount'] = $orderFormattedData['amount'];
    $data['productinfo'] = 'Payment For Order ' . $data['txnid'];
    $data['firstname'] = $orderFormattedData['firstname'];
    $data['email'] = $orderFormattedData['user_email'];
    $data['phone'] = $orderFormattedData['phone'];
    $data['udf1'] = $data['txnid'];
    $data['udf2'] = '';
    $data['udf3'] = '';
    $data['udf4'] = '';
    $data['udf5'] = '';
    $data['udf6'] = '';
    $data['udf7'] = '';
    $data['udf8'] = '';
    $data['udf9'] = '';
    $data['udf10'] = '';

    $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
    $hashVarsSeq = explode('|', $data);
    $hash_string = '';

    foreach ($hashVarsSeq as $hash_var)
    {
    $hash_string .= isset($data[$hash_var]) ? $data[$hash_var] : '';
    $hash_string .= '|';
    }

    $hash_string .= $salt;
    $hash = strtolower(hash('sha512', $hash_string));

    unset($this->returnData['order']);

    $this->returnData['success'] = 'true';
    $this->returnData['hash'] = $hash;
    $this->returnData['key'] = $key;
    $this->returnData['salt'] = $salt;
    $this->returnData['service_provider'] = 'payu_paisa';
    $this->returnData['payumoneyDetails'] = $data;
    }
    else
    {
    $this->returnData['success'] = 'false';
    $this->returnData['message'] = 'Failed to get the order details.';
    }
    }

    return  $this->returnData;
    }*/

    /*
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

    /*
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

                $params     = JComponentHelper::getParams('com_ecomm');
                $domain = $params->get('emailDomain');

                // Build the data to be stored
                $data = array(
                    'password' => $password,
                    'username' => $mobileNo,
                    'name'     => $mobileNo,
                    'email'    => $mobileNo .'@'. $domain,
                    'groups'   => array($newUserGroup),
                    'profile'  => array('phone' => $mobileNo),
                    'sendEmail'  => true
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
                        //$message = 'Your account details are as follows, username : ' . $mobileNo . ' and password ' . $password;
                        $message = 'Welcome to the Motley family. Your account has been successfully created.';
                        $result  = $this->ecommSendSms($mobileNo, $message);

                        //if ($result['success'] == 'true')
                        {
                            /*$this->returnData['success'] = 'true';
                            $this->returnData['userId'] = $userTable->id;*/
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
                        }
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
            //$this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }

    }

    /*
     * Function to update the verified column
     */
    public function ecommVerifyOtpIsExpired($expirationTime)
    {
        $now = date('Y-m-d H:i:s');

        // OTP is not yet expired
        if ($now <= $expirationTime) {
            return false;
        }

        return true;
    }

    /*
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

    /*
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

    /*
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

    /*
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
                    $result  = $this->ecommSendSms($mobileNo, $message);

                    //if ($result['success'] == 'true')
                    {
                        // If otp is not expired
                        $this->returnData['success'] = "true";
                        $this->returnData            = array_merge($this->returnData, $data);
                    }
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

    /*
     * Function to get all the signup request with mobile no and otp
     */
    public function ecommGetAllMobileNosAndOtps()
    {
        try
        {
            $query = $this->db->getQuery(true);

            $query->select('*');
            $query->from($this->db->quoteName('#__ecomm_mobile_otp_map'));

            $this->db->setQuery($query);
            $data = $this->db->loadAssocList();

            return $data;
        } catch (Exception $e) {
            $this->returnData['success'] = "false";
        }
    }

    /*
     * Function to check if OTP is already generated for given MobileNo
     * If yes then return array containig mobile_no and otp
     * If no then return false
     */
    public function ecommCheckIfAlreadyGeneratedOtpForMobileNo($mobileNo)
    {
        $currentTimestamp = date('Y-m-d H:i:s');

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

    /*
     * Function to re-generate OTP for given MobileNo
     * return array containig status as true and mobile_no as specified mobileNo and otp as re-generated otp
     */
    public function ecommRegenerateOtpForMobileNo($mobileNo)
    {
        $params     = JComponentHelper::getParams('com_ecomm');
        $otpTimeout = $params->get('otp_timeout');
        $otpDigitCount = $params->get('otpDigitCount') - 1;
        $otpStartRange = pow(10,$otpDigitCount);
        $otpEndRange = pow(10,($otpDigitCount + 1)) - 1;

        try
        {
            // Generate random number between 100000 and 999999
            $otp = mt_rand($otpStartRange, $otpEndRange);

            // Create the expiration time
            $currentTimestamp    = date('Y-m-d H:i:s');
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

    /*
     * Function to generate OTP for given MobileNo
     * return array containig status as true and mobile_no as specified mobileNo and otp as newly generated otp
     */
    public function ecommGenerateOtpForMobileNo($mobileNo, $isUser)
    {
        $params     = JComponentHelper::getParams('com_ecomm');
        $otpTimeout = $params->get('otp_timeout');
        $otpDigitCount = $params->get('otpDigitCount') - 1;
        $otpStartRange = pow(10,$otpDigitCount);
        $otpEndRange = pow(10,($otpDigitCount + 1)) - 1;

        try
        {
            // Generate random number between 100000 and 999999
            $otp = mt_rand($otpStartRange, $otpEndRange);

            // Create the expiration time
            $currentTimestamp    = date('Y-m-d H:i:s');
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
                $result  = $this->ecommSendSms($mobileNo, $message);

                //if ($result['success'] == 'true')
                {

                    $this->returnData['success']         = "true";
                    $this->returnData['mobile_no']       = $mobileNo;
                    $this->returnData['otp']             = (string) $otp;
                    $this->returnData['expiration_time'] = $expirationTime;
                }
            } else {
                $this->returnData['success'] = "false";
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['success'] = "false";
        }
    }

    /*
     * Function to save the address for the user/vendor
     * return array containig status as true/false
     */
    public function ecommSaveAddress($lattitude, $longitude, $userId)
    {
        // Default user group
        $params       = JComponentHelper::getParams('com_ecomm');
        $googleAddressKey = $params->get('googleAddressKey');
        $defaultAddressType = $params->get('addressType');

        if(empty($googleAddressKey)) {
            $this->returnData['success'] = 'false';
            $this->returnData['message'] = 'Please configure google address key.';
            return $this->returnData;
        }

        $url       = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lattitude) . ',' . trim($longitude) . '&sensor=false&key=' . $googleAddressKey;

        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        $jsonData = curl_exec($curlSession);
        curl_close($curlSession);

        $arrayData = json_decode($jsonData);

        $status    = $arrayData->status;

        if ($status == "OK") {
            $address = $arrayData->results[0]->formatted_address;
        } else {
            $address = false;
        }

        if ($address) {
            $arrayOfAddress = explode(',', $address);

            $addressData = array();
            $length      = count($arrayOfAddress);

            $addressData['country_code'] = (string) $this->getCountryCode(trim($arrayOfAddress[$length - 1])); //99 // $arrayOfAddress[$length-1];

            $addr                      = explode(' ', $arrayOfAddress[$length - 2]);
            $addressData['state_code'] = (string) $this->getStateCode(trim($addr[1])); // 1344 ; //$addr[1];
            $addressData['zipcode']    = trim($addr[2]);
            $addressData['city']       = trim($arrayOfAddress[$length - 3]);
            $addressData['land_mark']  = trim($arrayOfAddress[$length - 4]);
            $addressData['address']    = trim($arrayOfAddress[$length - 5]);

            if (array_key_exists($length - 6, $arrayOfAddress)) {
                $addressData['address'] = trim(trim($arrayOfAddress[$length - 6]) . ', ' . trim($addressData['address']));
            }

            if (array_key_exists($length - 7, $arrayOfAddress)) {
                $addressData['address'] = trim(trim($arrayOfAddress[$length - 7]) . ', ' . trim($addressData['address']));
            }

            // Get the user details
            JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models');
            $userModel = JModelLegacy::getInstance('User', 'UsersModel');

            if(empty($userId)){
                $userId             = JFactory::getUser()->id;
            }

            $userDetails        = $userModel->getItem($userId);
            $groups             = $userModel->getAssignedGroups($userId);
            $userProfileDetails = JUserHelper::getProfile($userId);

            $addressData['phone']         = trim($userProfileDetails->profile['phone']);
            $addressData['user_email']    = trim($userDetails->email);
            $addressData['address_title'] = $defaultAddressType;
            $addressData['vat_number']    = '';

            if (!empty($userDetails->name)) {
                if (is_numeric($userDetails->name)) {
                    $addressData['firstname']  = '';
                    $addressData['middlename'] = '';
                    $addressData['lastname']   = '';
                } else {
                    $arrayName                 = explode(' ', $userDetails->name);
                    $addressData['firstname']  = trim($arrayName[0]);
                    $addressData['middlename'] = '';
                    $addressData['lastname']   = trim($arrayName[1]);
                }
            }

            if(empty($groups)){
                // Default user group
                $params = JComponentHelper::getParams('com_users');
                $groups = array($params->get('new_usertype'));
            }

            if (!empty($userDetails->username)) {
                // Build the data to be stored
                $data = array(
                    'id'      => $userDetails->id,
                    'groups'  => $groups,
                    'profile' => array(
                        'lattitude' => $lattitude,
                        'longitude' => $longitude,
                    ),
                );


                // Save the data in the table
                if ($userModel->save($data)) {
                    $addressStatus = $this->ecommSaveCustomerAddress($addressData);
                    if ($addressStatus['success'] == 'true') {
                        $this->returnData['success'] = 'true';
                    } else {
                        $this->returnData['message'] = 'Failed to add the address in your addresses.';
                    }
                } else {
                    $this->returnData['message'] = 'Failed to add the address in your profile.';
                }
            }
        } else {
             $this->returnData['message'] = 'Google api error';
             $this->returnData['debug_data'] = $jsonData;
        }
        return $this->returnData;
    }

    /*
     * Function to send sms to specified receiver
     * return array containig status as true and the subscriptions
     */
    public function ecommSendSms($receiver, $message)
    {
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('sms');
        $result = $dispatcher->trigger('onSmsSendMessage', array($receiver, $message));

        return $result;
    }

    /*
     * Function to get all the subscriptions
     * return array containig status as true and the subscriptions
     */
    public function ecommGetAllSubscriptions()
    {
        try
        {
            $query = $this->db->getQuery(true);

            $query->select('*')
                ->from($this->db->quoteName('#__ecomm_subscriptions'))
                ->where($this->db->quoteName('state') . ' = ' . $this->db->quote('1'));

            $this->db->setQuery($query);
            $subscriptions = $this->db->loadObjectlist();

            if (!empty($subscriptions)) {
                $this->returnData['success']       = 'true';
                $this->returnData['subscriptions'] = $subscriptions;
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /*
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
                $this->returnData['success']    = 'true';
                $this->returnData['categories'] = $categories;
                $this->returnData['billingDetails'] = $this->ecommGetBillingDetails();
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /*
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

    /*
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

    /*
     * Function to add the item in the cart
     * return array containig status as true and the shops for given category
     */
    public function ecommAddToCart($items, $userData)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        foreach($items as $item) {
             // Add to cart
             $data = $this->comquick2cartHelper->addToCartAPI($item, $userData);
        }

        // If successfully added to the cart
        if ($data['status']) {
            $this->returnData['success'] = 'true';
        }

        return $this->returnData;
    }

    /*
     * Function to delete the item in the cart
     * return array containig status as true and the shops for given category
     */
    public function ecommDeleteFromCart($cartItemId)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Load the cart model
        $cartModel = JModelLegacy::getInstance('cart', 'Quick2cartModel');
        $cartModel->remove_cartItem($cartItemId);

        // Does not return any value, so consider its removed successfully
        $this->returnData['success'] = 'true';

        return $this->returnData;
    }


    /*
     * Function to get the cart details
     * return array containig status as true and the cart details
     */
    public function ecommApplyCouponCode($couponCode)
    {
        $this->returnData = array();

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin("system");
        $return = $dispatcher->trigger("ecommApplyCouponCode", array($couponCode));
        if($return[0] == 'true')
        {
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Coupon code applied successfully';
        }
        else
        {
            $this->returnData['success'] = 'false';
            $this->returnData['message'] = 'Failed to apply coupon';
        }

        return $this->returnData;
    }

    /*
     * Function to get mrp of optionId
     * return mrp of option else 0
     */
    public function ecommGetMrpOfOption($productId, $optionId)
    {
        $data = $this->ecommGetAvailableUnitsForProduct($productId, 0, 0);
        if(isset($data['isAvailable']) && $data['isAvailable'] == true)
        {
            if(isset($data['options']) && !empty($data['options']))
            {
                foreach ($data['options'] as $option) {
                   if($optionId ==  $option['optionId'])
                   {
                        return $option['optionMRP'];
                   }
                }
            }
        }

        return 0;
    }

    /*
     * Function to get the cart details
     * return array containig status as true and the cart details
     */
    public function ecommGetCartDetails()
    {
        // Load the cart model
        $cartModel = JModelLegacy::getInstance('cart', 'Quick2cartModel');
        $cart      = $cartModel->getCartitems();

        // Load the promotion helper class and get the promotions
        $discount        = 0;
        $productDiscount = 0;
        $productMrpTotal = 0;
        $promotionHelper = new PromotionHelper;
        $coupon          = $promotionHelper->getSessionCoupon();
        $promotions      = $promotionHelper->getCartPromotionDetail($cart, $coupon);

        // Calculate product mrp total
        foreach($cart as $product)
        {
            $optionId = explode(',', $product['product_attributes']);
            $mrp = $this->ecommGetMrpOfOption($product['item_id'],$optionId[0]);
            if(!empty($mrp) && $mrp > 0)
            {
                $productMrpTotal+=($mrp * $product['qty']);
            }
        }

        // Get the promotion details that has maximum discount
        $maxDiscountPromoUsed = $promotions->maxDisPromo;

        // Format the cart details
        $formattedCart     = $this->ecommGetFormattedCartDetails($cart);
        $formattedDiscount = $this->ecommGetFormattedDiscountDetails($maxDiscountPromoUsed);

        $billAmount    = $formattedCart['totalBillAmount'];
        if(isset($formattedDiscount->applicableMaxDiscount))
        {
            $discount      = $formattedDiscount->applicableMaxDiscount;
        }

        // calculate tax
        $tax = 0;
        $tax = $this->getTaxAmount($billAmount);

        // calculate delivery charges
        $delivery = 0;
        $delivery = $this->getDeliveryAmount($billAmount);

        $totalPayableAmount = ($billAmount + $tax + $delivery) - $discount;

        // Calculate product discount
        if(!empty($productMrpTotal) && $productMrpTotal > 0)
        {
            $productDiscount = $productMrpTotal - $billAmount;
        }

        if($productDiscount < 0)
        {
            $productDiscount = 0;
        }

        $billingDetails = array(
            'totalBillAmount' => (string)round($billAmount,2),
            'discountAmount' => (string) round($discount, 2),
            'productDiscountAmount' => (string) round($productDiscount,2),
            'totalPayableAmount' => (string) round($totalPayableAmount,2),
            'taxAmount'  => (string) round($tax,2),
            'deliveryAmount'  => (string) round($delivery,2)
        );

        unset($formattedCart['totalBillAmount']);
        unset($this->returnData['store']);

        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        if (!empty($formattedCart)) {
            $this->returnData['success']         = 'true';
            $this->returnData['cartDetails']     = $formattedCart;
            $this->returnData['discountDetails'] = $formattedDiscount;
            $this->returnData['billingDetails']  = $billingDetails;
        } else {
            $this->returnData['success'] = 'false';
            $this->returnData['message'] = 'Your cart is empty.';
        }

        return $this->returnData;
    }

    /*public function getMotleyDiscountAmount($cart)
    {
        // Get the product details
        $modelCart      = new Quick2cartModelcart;
        $total = 0;
        foreach($cart as $product)
        {
            $productDetails = $this->ecommGetSingleProductDetails($product['item_id'], $product['category'], $product['store_id']);
            $mrp = $productDetails['productDetails']->price;
            $sellingPrice = $productDetails['productDetails']->sellingPrice;
            $amount = $mrp - $sellingPrice;
            $total += $amount;
        }

        return $total;
    } */

    public function getTaxAmount($billAmount)
    {
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('qtctax');
        $result = $dispatcher->trigger('addTax', array($billAmount));

        return $result[0]['charges'];
    }

    public function getDeliveryAmount($billAmount)
    {
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('qtcshipping');
        $result = $dispatcher->trigger('qtcshipping', array($billAmount));

        return $result[0]['charges'];
    }

    public function ecommGetShippingDetails($addressId)
    {
        // Clear the previous responses
        $this->returnData  = array();
        $this->returnData['success'] = 'false';

        $path = JPATH_SITE . '/components/com_quick2cart/helpers/qtcshiphelper.php';

        if (!class_exists('qtcshiphelper'))
        {
            JLoader::register('qtcshiphelper', $path);
            JLoader::load('qtcshiphelper');
        }

        $qtcshiphelper              = new qtcshiphelper;
        $createOrderHelper          = new CreateOrderHelper;
        $shippingDetails            = new stdClass;

        // Get the address model and save the address
        $customer_addressform_model = JModelLegacy::getInstance('Customer_AddressForm', 'Quick2cartModel');

        $shippingDetails->ship = $customer_addressform_model->getAddress($addressId);
        $shippingDetails->ship = $createOrderHelper->mapUserAddress($shippingDetails->ship);

        $shippingDetails->bill = $customer_addressform_model->getAddress($addressId);
        $shippingDetails->bill = $createOrderHelper->mapUserAddress($shippingDetails->bill);

        $itemWiseShipDetail = $qtcshiphelper->getCartItemsShiphDetail($shippingDetails);

        if(!empty($itemWiseShipDetail))
        {
            // Format the cart details
            $formattedCart = array();
            foreach ($itemWiseShipDetail as $item)
            {
                $temp = array();
                $temp['productDetails'] = $this->ecommGetFormattedCartDetails(array($item['itemDetail']))[0];
                foreach ($item['shippingMeths'] as $shipMethod)
                {
                    $method = array(
                        'name'=> ($shipMethod['name'] != null) ? $shipMethod['name'] : '',
                        'totalShipCost' => (string) $shipMethod['totalShipCost']);
                    $temp['shippingMethods'] = $method;
                }
                $formattedCart[] = $temp;
            }

            unset($this->returnData['store']);

            $this->returnData['success'] = "true";
            $this->returnData['shippingDetails'] = $formattedCart;
        }
        else
        {
            $this->returnData['message'] = 'Unable to get the shipping details';
        }

        return $this->returnData;
    }

    public function ecommGetSingleCartItemDetails($cartItemId)
    {
        // Create db and query object
        $query = $this->db->getQuery(true);

        // Build the query
        $query->select('*')
            ->from($this->db->quoteName('#__kart_cartitems'))
            ->where($this->db->quoteName('cart_item_id') . " = " . $cartItemId);
        $this->db->setQuery($query);

        return $this->db->loadAssoc();
    }

    public function getData($optionId, $productId)
    {
        $productHelper = new ProductHelper;
        $result        = $productHelper->getItemCompleteAttrDetail($productId);
        $optionData    = array();

        $optionData['itemattribute_id']     = $result[0]->itemattribute_id;
        $optionData['item_id']              = $result[0]->item_id;
        $optionData['store_id']             = $result[0]->store_id;
        $optionData['itemattribute_name']   = $result[0]->itemattribute_name;
        $optionData['ordering']             = $result[0]->ordering;
        $optionData['attribute_compulsary'] = $result[0]->attribute_compulsary;
        $optionData['attributeFieldType']   = $result[0]->attributeFieldType;
        $optionData['global_attribute_id']  = $result[0]->global_attribute_id;
        $optionData['is_stock_keeping']     = $result[0]->is_stock_keeping;

        foreach ($result[0]->optionDetails as $attr) {
            if ($attr->itemattributeoption_id == $optionId) {
                $attribute['itemattributeoption_id']     = $attr->itemattributeoption_id;
                $attribute['global_option_id']           = $attr->global_option_id;
                $attribute['itemattribute_id']           = $attr->itemattribute_id;
                $attribute['child_product_item_id']      = $attr->child_product_item_id;
                $attribute['itemattributeoption_name']   = $attr->itemattributeoption_name;
                $attribute['itemattributeoption_price']  = $attr->itemattributeoption_price;
                $attribute['itemattributeoption_price_mrp']  = $attr->itemattributeoption_price_mrp;
                $attribute['itemattributeoption_code']   = $attr->itemattributeoption_code;
                $attribute['itemattributeoption_prefix'] = $attr->itemattributeoption_prefix;
                $attribute['ordering']                   = $attr->ordering;
                $attribute['state']                      = $attr->state;
                $attribute['optioncurrency']             = "INR";
                $attribute['optionprice']                = $attr->itemattributeoption_price;
                $arribData                               = $attribute;
            }
        }

        return array('optionData' => $optionData, 'attribute' => $attribute);
    }

    public function ecommUpdateCartDetails($productData, $cartItemId)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $data = $this->getData($productData[0]['optionId'], $productData[0]['item_id'], $productData[0]['item_id']);

        unset($productData[0]['optionId']);

        $newData[] = $productData[0];
        $newData[] = array($data['attribute']['itemattribute_id'] => $data['attribute']);
        $newData[] = array($data['optionData']);

        // Load the cart model
        $cartModel = JModelLegacy::getInstance('cart', 'Quick2cartModel');
        $result    = $cartModel->putCartitem('', $newData, $cartItemId);

        if ($result == 1) {
            $this->returnData['success'] = 'true';
            $result                      = $this->ecommGetSingleCartItemDetails($cartItemId);

            $singleCartItemDetails               = array();
            $singleCartItemDetails['totalPrice'] = $result['product_final_price'];
            $singleCartItemDetails['count']      = $result['product_quantity'];

            $this->returnData['cartItemDetails'] = $singleCartItemDetails;
            unset($this->returnData['store']);
        }

        return $this->returnData;
    }

    /*
     * Function to empty the cart
     * return array containig status as true
     */
    public function ecommEmptyCart()
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Load the cart model
        $cartModel = JModelLegacy::getInstance('cart', 'Quick2cartModel');
        $cartModel->empty_cart();

        // Does not return any value, so consider its empty successfully
        $this->returnData['success'] = 'true';

        return $this->returnData;
    }

    public function ecommGetFormattedDiscountDetails($discount)
    {
        $data = new stdClass;

        if(empty($discount))
        {
            $data->hasDiscount = "false";
        }
        else
        {
            $data->hasDiscount = "true";
            $data->couponCode = $discount->coupon_code;
            $data->name = $discount->name;
            $data->description = $discount->description;
            $data->applicableMaxDiscount = (string) $discount->applicableMaxDiscount;
        }

        return $data;
    }

    public function ecommGetFormattedCartDetails($cart)
    {
        $cartData   = array();
        $singleItem = array();
        $total      = 0;

        foreach ($cart as $singleCartItem) {
            // Get the id,item_id, title,store_id, qty, category,amt,tamt
            $singleItem['cartId']             = $singleCartItem['cart_id'];
            $singleItem['cartItemId']         = $singleCartItem['id'];
            $singleItem['productId']          = $singleCartItem['item_id'];
            $singleItem['productTitle']       = $singleCartItem['title'];
            $singleItem['shopId']             = $singleCartItem['store_id'];
            $singleItem['quantity']           = $singleCartItem['qty'];
            $singleItem['categoryId']         = $singleCartItem['category'];
            $singleItem['productAmount']      = $singleCartItem['product_attributes_price'];
            $singleItem['productTotalAmount'] = $singleCartItem['tamt'];

            if($singleItem['productTotalAmount'] == 0)
            {
                $singleItem['productTotalAmount'] = (string) $singleItem['productAmount'] * $singleItem['quantity'] ;
            }
            else
            {
                $singleItem['productTotalAmount'] = (string) $singleCartItem['tamt'];
            }

            $singleItem['shopTitle']          = '';
            $singleItem['productImages']      = $this->ecommGetProductImages($singleCartItem['item_id']);

            $options   = $singleCartItem['options'];
            $optionIds = $singleCartItem['product_attributes'];

            // if not empty
            if (!empty($options) && !empty($optionIds)) {
                $options                         = explode(':', $options);
                $optionIds                       = explode(',', $optionIds);
                $ops                             = explode(',', $options[1]);
                $singleItem['availableInOption'] = trim($ops[0]);
                $singleItem['optionId']          = trim($optionIds[0]);
            } else {
                $singleItem['availableInOption'] = '';
                $singleItem['optionId']          = '';
            }

            // Get the shop title
            $storeData = $this->ecommGetSingleStoreDetails($singleCartItem['store_id'], 'title');

            // Check if title present
            if ($storeData['success'] == 'true' && $storeData['store']) {
                $singleItem['shopTitle'] = $storeData['store']['title'];
            }

            $total += $singleItem['productTotalAmount'];

            $cartData[] = $singleItem;
        }

        $cartData['totalBillAmount'] = $total;

        return $cartData;
    }

    /*
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

        if(isset($images[0]) && !empty($images[0]))
        {
            $productImages['Img0'] = $images[0];
        }

        return $productImages;
    }

    /*
     * Function to get all the shops for given category
     * return array containig status as true and the shops for given category
     */
    public function ecommGetAllShopsForCategory($categoryId)
    {
        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('DISTINCT (' . $this->db->quoteName('store_id') . ')')
                ->from($this->db->quoteName('#__kart_items'))
                ->where($this->db->quoteName('parent') . " = " . $this->db->quote('com_quick2cart') . ' AND ' .
                    $this->db->quoteName('category') . " = " . $this->db->quote($categoryId) . ' AND ' .
                    $this->db->quoteName('state') . " = " . $this->db->quote('1')
                );
            $this->db->setQuery($query);

            // Load the list of stores found
            $stores = $this->db->loadAssocList();

            // If stores found
            if (!empty($stores)) {
                $storeIds = array();

                // Iterate over each store and get its id
                foreach ($stores as $store) {
                    if ($store['store_id']) {
                        $storeIds[] = $store['store_id'];
                    }
                }

                // Get the new query instance
                $query = $this->db->getQuery(true);

                // Build the query
                $query->select('*')
                    ->from($this->db->quoteName('#__kart_store'))
                    ->where($this->db->quoteName('id') . " IN (" . implode(',', $storeIds) . ')');

                $this->db->setQuery($query);

                // Load the list of Stores found
                $storeDetails = $this->db->loadAssocList();
                $data         = array();

                // If found the store details
                if (!empty($storeDetails)) {
                    // Iterate over each store
                    foreach ($storeDetails as $store) {
                        // Get the live store details
                        if ($store['live']) {
                            $image = $this->comquick2cartHelper->isValidImg($store['store_avatar']);

                            if (empty($image)) {
                                $image = $this->storeHelper->getDefaultStoreImage();
                            }

                            // If atleast one store is live then set this flag
                            $isStore            = true;
                            $singleStoreDetails = array();

                            // id, title, address[address+city], distance, store_avatar
                            $singleStoreDetails['id']           = $store['id'];
                            $singleStoreDetails['title']        = $store['title'];
                            $singleStoreDetails['store_avatar'] = $image;
                            $singleStoreDetails['address']      = $store['address'] . ', ' . $store['city'];

                            // Get the logged in users id
                            $userId = JFactory::getUser()->id;

                            // Get the user and shop owner
                            $user = JUserHelper::getProfile($userId);
                            $shop = JUserHelper::getProfile($store['owner']);

                            // Build the users location
                            $userLocation = array(
                                'lattitude' => $user->profile['lattitude'],
                                'longitude' => $user->profile['longitude'],
                            );

                            // Build the store location
                            $shopLocation = array(
                                'lattitude' => $shop->profile['lattitude'],
                                'longitude' => $shop->profile['longitude'],
                            );

                            // Get the distance between user and the shop
                            $singleStoreDetails['distance'] = $this->getDistance($shopLocation, $userLocation);

                            $data[] = $singleStoreDetails;
                        }
                    }
                }
            }

            // If we have atleast one live store details
            if ($isStore) {
                $this->returnData['success'] = 'true';
                $this->returnData['stores']  = $data;
            } else {
                $this->returnData['message'] = 'No shops found for given category';
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /*
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
            if($shopId != '*')
            {
                $query->where($this->db->quoteName('store_id') . " = " . $this->db->quote($shopId));
            }

            // If state(published) is * then return all
            if ($published != '*')
            {
                $query->where($this->db->quoteName('state') . " = " . $this->db->quote($published) );
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

    /*
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

    /*
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

    /*
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

    /*
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

    /*
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
        {   /*
            // If products found
            if (!empty($products)) {
                $productsDetails = array();

                // Iterate over each product and get details
                foreach ($products as $product) {
                    // id, name, price, image, stock, rating
                    $singleProduct                 = array();
                    $singleProduct['name']         = $product['name'];
                    $singleProduct['product_id']   = $product['product_id'];
                    $singleProduct['price']        = $product['price'];
                    $singleProduct['stock']        = $product['stock'];
                    $singleProduct['sellingPrice'] = $product['fprice'];

                    // Get the product ratings
                    $singleProduct['ratings'] = $this->ecommGetProductRating($product['product_id']);

                    // Get all the images
                    $images = json_decode($product['images']);

                    // Get the valid images
                    $images   = $this->getValidImages($images);
                    $imgArray = array();

                    if(isset($images[0]) && !empty($images[0]))
                    {
                        $imgArray['image0'] = $images[0];
                    }

                    $singleProduct['images'][] = $imgArray;
                    $productsDetails[] = $singleProduct;
                }

                $this->returnData['success']  = 'true';
                $this->returnData['products'] = $productsDetails;
            } */

            // If products found
            if (!empty($products))
            {
                $productsDetails = array();
                $singleProduct = array();

                // Iterate over each product and get details
                foreach ($products as $product)
                {
                    // Get the products available in options
                    $productsDetails[] = $this->ecommGetSingleProductDetails($product['product_id'], $$shopId, $categoryId) ['productDetails'];
                }

                $this->returnData = array();
                $this->returnData['success']  = 'true';
                $this->returnData['products'] = $productsDetails;
            }

            else {
                $this->returnData['message'] = 'No products found.';
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

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

    /*
     * Function to get avaliable payment options for given shop
     * return array containig status as true and the shop payment options
     */
    public function ecommGetPaymentOptionsForShop($shopId)
    {

    }

    /*
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

    /*
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
                $productImage = new stdClass;
                $productImage->path = $images[$i];
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

    /* VENDOR
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

    /* VENDOR
     * Function to save the subscription
     * return array containig status as true and the subscription details
     */
    public function ecommSaveSubscription($userId, $subscriptionId)
    {
        // Store the data
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_ecomm/models');
        $subscriptionModel = JModelLegacy::getInstance('UserSubscription', 'EcommModel');

        $todaysDate = date('Y-m-d');

        // Build the data to be stored
        $data = array(
            'user_id'         => $userId,
            'subscription_id' => $subscriptionId,
            'purchase_date'   => $todaysDate,
        );

        // Save the data in the table
        if ($subscriptionModel->save($data)) {
            $this->returnData['success'] = 'true';
        } else {
            $this->returnData['message'] = 'Failed to save the subscription';
        }

        return $this->returnData;
    }

    /* VENDOR
     * Function to save the new product
     * return array containig status as true and the product details
     */
    public function ecommSaveNewProduct($subscriptionDetails)
    {
    }

    /* VENDOR
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

    /* VENDOR
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

    /* VENDOR
     * Function to get single order ddetails for given orderId and shopId
     * return array containig status as true and the order details
     */
    public function ecommGetSingleOrderDetails($shopId, $orderId)
    {
        $orders = array();

        $order = $this->comquick2cartHelper->getorderinfo($orderId, $shopId);

        if (!empty($order) && isset($order['order_info'][0]->id))
        {
            $orderData = $order['order_info'][0];

            $orderDetails = new stdClass;
            $orderDetails->orderId = $orderData->order_id;
            $orderDetails->prefix = $orderData->prefix;
            $orderDetails->status = $orderData->status;
            $orderDetails->createdOn = $orderData->cdate;
            $orderDetails->createdBy = $orderData->user_id;
            $orderDetails->tax = $orderData->order_tax;
            $orderDetails->shippingCharges = $orderData->order_shipping;

            $totalItemShipCharges = 0;
            $totalItemTaxCharges = 0;
            $totalItemDiscount = 0;
            $totalItemPrice = 0;
            $productsDetails = array();
            $totalTax = 0;
            $totalShippingCharges = 0;
            $couponCode = '';
            //$discountDetail = '';

            foreach ($order['items'] as $item)
            {
                $product = new stdClass;

                $product->productId = $item->item_id;
                $product->storeId = $item->store_id;
                $product->productName = $item->order_item_name;
                $product->quantity = $item->product_quantity;
                $product->productAmount = $item->product_item_price;
                $product->totalAmount = $item->product_attributes_price * $item->product_quantity;

                // Commented For now
                // $product->shippingCharges = (string) round($item->item_shipcharges, 2);
                // $product->taxCharges = (string) round($item->item_tax, 2);

                $product->optionDetails = new stdClass;
                $product->optionDetails->optionId = $item->product_attributes;
                $product->optionDetails->optionName = $item->product_attribute_names;

                $product->optionDetails->optionAmount = (string) $item->product_attributes_price;

                $productsDetails[] = $product;

                // Commented For now
                // $totalItemShipCharges += !empty($item->item_shipcharges) ? $item->item_shipcharges : 0.00;
                // $totalItemTaxCharges += !empty($item->item_tax) ? $item->item_tax : 0.00;
                $totalItemDiscount += !empty($item->discount) ? $item->discount : 0.00;
                $totalItemPrice += !empty($product->totalAmount) ? $product->totalAmount : 0.00;

                if(!empty($item->coupon_code) && empty($couponCode))
                {
                    $couponCode = $item->coupon_code;
                }

                // if(!empty($item->discount_detail) && empty($discountDetail))
                // {
                //     $discountDetail = json_decode($item->discount_detail);
                // }
            }

            $orderDetails->amount = (string) round($orderData->amount, 2);
            $orderDetails->subTotal = (string) round($totalItemPrice, 2);

            // Commented For now
            // $orderDetails->tax = (string) round($totalItemTaxCharges, 2);
            // $orderDetails->shippingCharges = (string) round($totalItemShipCharges, 2);

            $orderDetails->discount = (string) round($totalItemDiscount, 2);
            $orderDetails->couponCode = $couponCode;
            //$orderDetails->discountDetail = $discountDetail;

            $orderDetails->productDetails = $productsDetails;

            // Address Details
            $orderDetails->userAddressDetails = new stdClass;
            $orderDetails->userAddressDetails->firstName = $orderData->firstname;
            $orderDetails->userAddressDetails->middleName = $orderData->middlename;
            $orderDetails->userAddressDetails->lastName = $orderData->lastname;
            $orderDetails->userAddressDetails->email = $orderData->user_email;
            $orderDetails->userAddressDetails->mobileNo = $orderData->phone;
            $orderDetails->userAddressDetails->address = $orderData->address;
            $orderDetails->userAddressDetails->landMark = $orderData->land_mark;
            $orderDetails->userAddressDetails->city = $orderData->city;
            $orderDetails->userAddressDetails->zipCode = $orderData->zipcode;
            $orderDetails->userAddressDetails->stateName = $orderData->state_name;
            $orderDetails->userAddressDetails->countryName = $orderData->country_name;


            $this->returnData['success'] = 'true';
            $this->returnData['orderDetails']   = $orderDetails;
        }
        else
        {
            $this->returnData['message'] = 'Failed to get the order details';
        }

        return $this->returnData;
    }

    /* VENDOR
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

    /* User
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

    /* VENDOR
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

    /* VENDOR
     * Function to get all users
     * return array containig status as true and the all users details
     */
    public function ecommGetAllUsers($isUser = true)
    {
        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('user_id')
                ->from($this->db->quoteName('#__ecomm_mobile_otp_map'))
                ->where($this->db->quoteName('is_user') . " = " . (int) $isUser);
            $this->db->setQuery($query);

            // Load the list of users found
            $users = $this->db->loadAssocList();

            // If users found
            if (!empty($users)) {
                $this->returnData['success'] = 'true';
                $data                        = array();

                // Iterate over each user
                foreach ($users as $user) {
                    $userId = $user['user_id'];

                    // If user is registred successfully
                    if ($userId) {
                        $userAllDetails = $this->getUserDetails($userId);
                        $userData       = $this->ecommGetSpecificUserDetails($userAllDetails);

                        // Push the single user data in the
                        $data[] = $userData;
                    }
                }

                // Push all the users in returnData
                $this->returnData['users'] = $data;
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /* Common
     * Function to update specific user details
     * return array containig status as true and the user details
     */
    public function ecommUpdateUserDetails($userData)
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models');
        $userModel = JModelLegacy::getInstance('User', 'UsersModel');

        //check if userId
        if(isset($userData['userId']) && !empty($userData['userId'])){
            $userId = $userData['userId'];
        } else if(isset($userData['username']) && !empty($userData['username'])){
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

                if (isset($userData['lattitude']) && !empty($userData['lattitude'])) {
                    $data['profile']['lattitude'] = $userData['lattitude'];
                } else {
                    $data['profile']['lattitude'] = $user['lattitude'];
                }

                if (isset($userData['longitude']) && !empty($userData['longitude'])) {
                    $data['profile']['longitude'] = $userData['longitude'];
                } else {
                    $data['profile']['longitude'] = $user['longitude'];
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

    /* Common
     * Function to get specific user details
     * return array containig status as true and the sale details
     */
    public function ecommGetSpecificUserDetails($userDetails)
    {
        // Check if current person is a user or vendor
        $type = $this->ecommGetUserType($userDetails->id);

        // Return - id, username, name, email, mobile_no, lattitude, longitude
        $userData = array();

        $userData['id']        = isset($userDetails->id) ? $userDetails->id : '';
        $userData['is_user']   = ($type != 'exception') ? (string) $type : '';
        $userData['username']  = isset($userDetails->username) ? $userDetails->username : '';
        $userData['name']      = isset($userDetails->name) ? $userDetails->name : '';
        $userData['email']     = isset($userDetails->email) ? $userDetails->email : '';
        $userData['mobileNo']  = isset($userDetails->profile['phone']) ? $userDetails->profile['phone'] : '';
        $userData['lattitude'] = isset($userDetails->profile['lattitude']) ? (string) $userDetails->profile['lattitude'] : '';
        $userData['longitude'] = isset($userDetails->profile['longitude']) ? (string) $userDetails->profile['longitude'] : '';

        return $userData;
    }

    /* User
     * Function to get store distance from users location
     * return array containig status as true and the distance details
     * google map api key = AIzaSyD2Glj1K120tqnUvw629PiK_SjNdSi83aU
     */
    public function getDistance($storeLocation, $userLocation, $unit = 'K')
    {
        //Get latitude and longitude from geo data
        $latitudeFrom  = $storeLocation['lattitude'];
        $longitudeFrom = $storeLocation['longitude'];
        $latitudeTo    = $userLocation['lattitude'];
        $longitudeTo   = $userLocation['longitude'];

        // Calculate distance from latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist  = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) + cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist  = acos($dist);
        $dist  = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit  = strtoupper($unit);

        if ($unit == "K") {
            return round(($miles * 1.609344), 2) . ' KM';
        } else if ($unit == "N") {
            return round(($miles * 0.8684)) . ' NM';
        } else {
            return round($miles) . ' MI';
        }
    }

    /* VENDOR
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

    /*
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
        $dispatcher->trigger("ecommApplyCouponCode", array($couponCode));

        $promotionHelper = new PromotionHelper;
        $couponDetails   = $promotionHelper->getSessionCoupon();

        $user = JFactory::getUser();

        // Get the billing address details
        $address = $this->ecommGetSingleCustomerAddressDetails($billingAddressId);

        if ($address['success'] == 'true') {
            $billingAddress = $address['address'];
        } else {
            $this->returnData['message'] = 'Billing address not found';
            return $this->returnData;
        }

        // If shipping address is enabled
        if ($shippingAddressId) {
            // Get the shipping address details
            $address = $this->ecommGetSingleCustomerAddressDetails($shippingAddressId);

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
        $orderData                   = new stdClass;
        $orderData->address          = new stdClass;
        $orderData->userId           = $user->id;
        $orderData->products_data    = $products;
        $orderData->address->billing = $billingAddress;
        $orderData->address->shipping = $shippingAddress;
        $orderData->coupon_code      = $couponDetails;

        $createOrderHelper = new CreateOrderHelper;

        //print_r($orderData);die;

        // Place the order
        $result = $createOrderHelper->qtc_place_order($orderData);

        // If order is successful
        if ($result->status == 'success' && isset($result->order_id) && !empty($result->order_id))
        {
            $cartModel = JModelLegacy::getInstance('cart', 'Quick2cartModel');
            $cartModel->empty_cart();

            // Update payment details start
            $data = $this->ecommGetSingleOrderDetails(0, $result->order_id);
            $orderData = $data['orderDetails'];

            $orderDetails = array();
            $orderDetails['total'] = $orderData->amount;
            $orderDetails['mail_addr'] = $orderData->userAddressDetails->email;
            $orderDetails['order_id'] = $orderData->prefix . $orderData->orderId;
            $orderDetails['user_id'] = $orderData->createdBy;
            $orderDetails['comment'] = '';
            $paymentDetails['orderDetails'] = $orderDetails;

            if(isset($paymentDetails) && isset($paymentDetails['response']) && !empty($paymentDetails['response']))
            {
                $paymentDetails['response']['txnid'] = $orderData->prefix . $orderData->orderId;
            }

            $this->ecommUpdatePaymentDetailsForOrder($paymentDetails);
            // update payment details end

            $this->returnData = $orderData;
        }

        return $this->returnData;
    }

    /*
     * Function to get single address detaill
     * return array containig status as true
     */
    public function ecommGetSingleCustomerAddressDetails($addressId)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Get the address model and save the address
        $addressModel = JModelLegacy::getInstance('Customer_AddressForm', 'Quick2cartModel');
        $result       = $addressModel->getAddress($addressId);

        // If successfully saved the address then return true
        if ($result) {
            $this->returnData['success'] = 'true';
            $this->returnData['address'] = $result;
        }

        return $this->returnData;
    }

    /*
     * Function to save customer address
     * return array containig status as true
     */
    public function ecommSaveCustomerAddress($address)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Get the address model and save the address
        $addressModel = JModelLegacy::getInstance('Customer_AddressForm', 'Quick2cartModel');
        $result       = $addressModel->save($address);

        // If successfully saved the address then return true
        if ($result) {
            $this->returnData['success'] = 'true';
        }

        return $this->returnData;
    }

    /*
     * Function to delete the customer address
     * return array containig status as true
     */
    public function ecommDeleteCustomerAddress($addressId)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        // Load the address form model
        $addressFormModel = JModelLegacy::getInstance('Customer_AddressForm', 'Quick2cartModel');
        $result           = $addressFormModel->delete($addressId);

        if ($result) {
            $this->returnData['success'] = 'true';
        }

        return $this->returnData;
    }

    /*
     * Function to get all the customer addresses used in past
     * return array containig status as true and the addresses
     */
    public function ecommGetUserAddressList()
    {
        $uid = JFactory::getUser()->id;

        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        try
        {
            $query = $this->db->getQuery(true);

            $query->select('*');
            $query->from('#__kart_customer_address');
            $query->where('user_id = ' . $uid);
            $query->order('id DESC');

            // Get the options.
            $this->db->setQuery($query);
            $address = $this->db->loadObjectList();

            if (!empty($uid)) {
                // Load the address form model
                $cartCheckoutModel = JModelLegacy::getInstance('cartcheckout', 'Quick2cartModel');

                $userCountry = array();
                $userState   = array();

                $billing_flag  = 0;
                $shipping_flag = 0;
                $length        = count($address);
                $userAddresses = array();
                $i             = 0;

                // Check if address is used as billing or shipping order
                if (!empty($address)) {
                    foreach ($address as $item) {
                        if (!empty($item->last_used_for_shipping)) {
                            $shipping_flag = 1;
                        }

                        if (!empty($item->last_used_for_billing)) {
                            $billing_flag = 1;
                        }

                        if (!array_key_exists($item->country_code, $userCountry)) {
                            if (!empty($item->country_code)) {
                                $userCountry[$item->country_code] = $cartCheckoutModel->getCountryName($item->country_code);
                            }
                        }

                        $item->country_name = $userCountry[$item->country_code];

                        if (!array_key_exists($item->state_code, $userState)) {
                            if (!empty($item->state_code)) {
                                $userState[$item->state_code] = $cartCheckoutModel->getStateName($item->state_code);
                            }
                        }

                        if (isset($userState[$item->state_code])) {
                            $item->state_name = $userState[$item->state_code];
                        } else {
                            $item->state_name = '';
                        }

                        if ($i == ($length - 1)) {
                            // Pre select first address as shipping address
                            if (empty($shipping_flag)) {
                                $address[0]->last_used_for_shipping = 1;
                            }

                            // Pre select first address as billing address
                            if (empty($billing_flag)) {
                                $address[0]->last_used_for_billing = 1;
                            }
                        }

                        $userAddresses[] = $item;
                        $i++;
                    }

                    if (!empty($userAddresses)) {
                        $this->returnData['success']   = 'true';
                        $this->returnData['addresses'] = $userAddresses;
                    }

                }
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /* VENDOR
     * Function to get total sale
     * return array containig status as true and the sale details
     */
    public function ecommGetTotalSale($vendorId, $storeId, $orderStatuses)
    {
    }

    /* Common
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

        foreach ($result as $value)
        {
            $obj     = new stdClass;

            $data = JPluginHelper::getPlugin("payment", $value);
            $pluginDetails = json_decode($data->params);

            if (!empty($pluginDetails->plugin_name))
            {
                $obj->id = $value;
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

    /* VENDOR
     * Function to save new store
     * return array containig status as true and the message
     */
    public function ecommSaveStore($storeData)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $input    = new JInput();
        $token = JHtml::_('form.token');
        $token = JSession::getFormToken();
        $shopId = empty($storeData['shopId'])? 0 : $storeData['shopId'];

        $input->set('id', $shopId);
        $input->set('store_creator_id', $storeData['storeOwner']);
        $input->set('title', $storeData['title']);

        $input->set('description', $storeData['description']);
        $input->set('companyname', $storeData['companyName']);

        $input->set('email', $storeData['email']);
        $input->set('phone' ,$storeData['mobileNo']);
        $input->set('address', $storeData['address']);
        $input->set('land_mark', $storeData['landMark']);
        $input->set('pincode', $storeData['pinCode']);
        $input->set('storecountry', $storeData['countryName']);
        $input->set('qtcstorestate', $storeData['stateName']);
        $input->set('city', $storeData['city']);

        $input->set('paymentMode',$storeData['paymentMode']);
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
        JLoader::register('storeHelper', JPATH_SITE. '/components/com_quick2cart/helpers');
        $this->storeHelper = new storeHelper;
        $result      = $this->storeHelper->saveVendorDetails($input);

        if($result['store_id'])
        {
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Store details saved successfully';
        }
        else
        {
            $this->returnData['message'] = 'Failed to save the store details';
        }

        return $this->returnData;
    }

    /* VENDOR
     * Function to save new product
     * return array containig status as true and the message
     */
    public function ecommSaveProduct($productData)
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $input    = new JInput();
        $token = JHtml::_('form.token');
        $token = JSession::getFormToken();
        $productId = empty($productData['productId'])? 0 : $productData['productId'];

        $input->set('pid', $productId);

        $input->set('item_name', $productData['productName']);
        $input->set('item_alias', $productData['productAlias']);
        $input->set('qtc_product_type', 1);
        $input->set('prod_cat', $productData['productCategory']);
        $input->set('description', array('data' => $productData['productDescription']) );
        $input->set('sku', $productData['productSku']);
        $input->set('stock', $productData['stock']);
        $input->set('state', $productData['state']);
        $input->set('store_id', $productData['storeId']);
        $input->set('multi_cur', array('INR' => $productData['productPrice'] ));
        $input->set('multi_dis_cur', array('INR' => $productData['discountPrice'] ));

        $input->set('option', 'com_quick2cart');
        $input->set('client', 'com_quick2cart');
        $input->set('task', 'product.save');
        $input->set('view', 'product');
        $input->set('check', 'post');
        $input->set($token, '1');

        // Require helper file
        $productId      = $this->comquick2cartHelper->saveProduct($input);

        if($productId > 0)
        {
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Product details saved successfully';
        }
        else
        {
            $this->returnData['message'] = 'Failed to save the product details';
        }

        return $this->returnData;
    }
}
