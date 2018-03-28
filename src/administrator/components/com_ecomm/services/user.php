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
 * Ecomm Address service class.
 *
 * @since  1.0
 */

class EcommUserService
{
    public function __construct()
    {
        $this->db                    = JFactory::getDbo();
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
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
            $userDetails->profile = JUserHelper::getProfile($userId)->profile;

            return $userDetails;
        }

        return false;
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
}
