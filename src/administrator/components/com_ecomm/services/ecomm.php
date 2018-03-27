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
        $this->db                      = JFactory::getDbo();
        $this->returnData              = array();
        $this->returnData['success']   = 'false';
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
}
