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

// Include the models
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models');
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_ecomm/models');

// Include the tables
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
                    $this->returnData = $this->sendOtpToMobileNo($mobileNo, $data['otp']);
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
        $data = $this->ecommGenerateOtp();

        try
        {
            // Initialise the variables
            $query   = $this->db->getQuery(true);
            $columns = array('mobile_no', 'otp', 'expiration_time');
            $values  = array($this->db->quote($mobileNo), $this->db->quote($data['otp']), $this->db->quote($data['expirationTime']));

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
                $this->returnData = $this->sendOtpToMobileNo($mobileNo, $data['otp']);
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
    public function ecommGenerateOtp()
    {
        $params        = JComponentHelper::getParams('com_ecomm');
        $otpTimeout    = $params->get('otp_timeout');
        $otpDigitCount = $params->get('otpDigitCount') - 1;
        $otpStartRange = pow(10, $otpDigitCount);
        $otpEndRange   = pow(10, ($otpDigitCount + 1)) - 1;

        // Generate random number between 100000 and 999999
        $otp = mt_rand($otpStartRange, $otpEndRange);

        // Create the expiration time
        $currentTimestamp    = $this->getDate();
        $expirationTimestamp = strtotime($currentTimestamp) + $otpTimeout;
        $expirationTime      = date('Y-m-d H:i:s', $expirationTimestamp);

        return array(
            'otp'            => (string) $otp,
            'otpTimeout'     => $otpTimeout,
            'expirationTime' => $expirationTime,
        );
    }

    /* - USER
     * Function to update the verified column
     */
    public function ecommUpdateVerifiedOtpResetPassword($mobileNo)
    {
        try
        {
            $query      = $this->db->getQuery(true);
            $conditions = array(
                $this->db->quoteName('mobile_no') . ' = ' . $this->db->quote($mobileNo),
            );
            $query->delete($this->db->quoteName('#__ecomm_mobile_otp_map_reset_password'));
            $query->where($conditions);
            $this->db->setQuery($query);
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
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
        $data = $this->ecommGenerateOtp();

        try
        {
            $query = $this->db->getQuery(true);

            // Fields to update.
            $fields = array(
                $this->db->quoteName('otp') . ' = ' . $this->db->quote($data['otp']),
                $this->db->quoteName('expiration_time') . ' = ' . $this->db->quote($data['expirationTime']),
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
        try
        {
            $query = $this->db->getQuery(true);
            $query->update($this->db->quoteName('#__ecomm_mobile_otp_map'))
                ->set($this->db->quoteName('verified') . ' = ' . $this->db->quote('1'))
                ->where($this->db->quoteName('mobile_no') . ' = ' . $this->db->quote($mobileNo));

            $this->db->setQuery($query);

            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
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
        // Get the table instance
        $mobileOtpMapTable = JTable::getInstance('MobileOtpMap', 'EcommTable', array('dbo', $this->db));

        // Get the mobile_no and otp details for mobile_no
        $mobileOtpMapTable->load(array('mobile_no' => $mobileNo, 'verified' => '1'));

        // If user_id exists for given mobile_no
        if (!empty($mobileOtpMapTable->user_id)) {
            return true;
        }

        return false;
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
                    $this->returnData = $this->sendOtpToMobileNo($mobileNo, $data['otp']);
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
        $data = $this->ecommGenerateOtp();

        try
        {
            $query = $this->db->getQuery(true);

            // Fields to update.
            $fields = array(
                $this->db->quoteName('otp') . ' = ' . $this->db->quote($data['otp']),
                $this->db->quoteName('expiration_time') . ' = ' . $this->db->quote($data['expirationTime']),
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
        $data = $this->ecommGenerateOtp();

        try
        {
            // Initialise the variables
            $query   = $this->db->getQuery(true);
            $columns = array('mobile_no', 'otp', 'expiration_time');
            $values  = array($this->db->quote($mobileNo), $this->db->quote($data['otp']), $this->db->quote($data['expirationTime']));

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
                $this->returnData = $this->sendOtpToMobileNo($mobileNo, $data['otp']);
            } else {
                $this->returnData['success'] = "false";
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['success'] = "false";
        }
    }

    /* - USER
     * Function to get joomla's user details
     * return array containig status as true and the user detials
     */
    public function sendOtpToMobileNo($mobileNo, $otp)
    {
        $message = 'Your OTP for ' . $mobileNo . ' is ' . $otp;

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('sms');
        $dispatcher->trigger('onSmsSendMessage', array($mobileNo, $message));

        return array(
            'success'   => 'true',
            'mobile_no' => $mobileNo,
            'otp'       => $otp,
        );
    }
}
