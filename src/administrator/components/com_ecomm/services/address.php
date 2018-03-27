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

class EcommAddressService
{
    public function __construct()
    {
        $this->db                    = JFactory::getDbo();
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
    }

    /* - ADDRESS
     * Function to get countries for address
     * return array of countries
     */
    public function ecommGetCountriesForAddress()
    {
        // Create the instance of zone model & call getCountry
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_quick2cart/models');
        $Quick2cartModelZone = JModelLegacy::getInstance('Zone', 'Quick2cartModel');
        $countries           = $Quick2cartModelZone->getCountry();

        // If we have the countries present
        if (!empty($countries)) {
            $this->returnData['success']   = 'true';
            $this->returnData['countries'] = $countries;
        }

        return $this->returnData;
    }

    /* - ADDRESS
     * Function to get states for country
     * return array of states
     */
    public function ecommGetStatesForCountry($countryId)
    {
        // Create the instance of zone model & call getRegionList
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_quick2cart/models');
        $Quick2cartModelZone = JModelLegacy::getInstance('Zone', 'Quick2cartModel');
        $states              = $Quick2cartModelZone->getRegionList($countryId);

        // If we have the states present
        if (!empty($states)) {
            $this->returnData['success'] = 'true';

            // Iterate over each state and get its id and name
            for ($i = 0; $i < count($states); $i++) {
                $data[$i]['id']        = $states[$i]->region_id;
                $data[$i]['stateName'] = $states[$i]->region;
            }

            // Save it in returnData
            $this->returnData['states'] = $data;
        }

        return $this->returnData;
    }

    /* - ADDRESS
     * Function to get single address detaill
     * return array containig status as true
     */
    public function ecommGetSingleCustomerAddressDetails($addressId)
    {
        // Get the address model and save the address
        $addressModel = JModelLegacy::getInstance('Customer_AddressForm', 'Quick2cartModel');
        $result       = $addressModel->getAddress($addressId);

        // Load the address form model
        $cartCheckoutModel    = JModelLegacy::getInstance('cartcheckout', 'Quick2cartModel');
        $result->country_name = $cartCheckoutModel->getCountryName($result->country_code);
        $result->state_name   = $cartCheckoutModel->getStateName($result->state_code);

        // If successfully saved the address then return true
        if ($result) {
            $this->returnData['success'] = 'true';
            $this->returnData['address'] = $result;
        }

        return $this->returnData;
    }

    /* - ADDRESS
     * Function to get country code
     * return country id/code
     */
    public function getCountryCode($countryName)
    {
        try
        {
            // Get the query instance
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('id');
            $query->from('#__tj_country');
            $query->where('country=' . $this->db->quote($countryName));

            // Set the query and load result
            $this->db->setQuery($query);
            return $this->db->loadAssoc()['id'];
        } catch (Exception $e) {
            return '0';
        }
    }

    /* - ADDRESS
     * Function to get state code
     * return state id/code
     */
    public function getStateCode($stateName)
    {
        try
        {
            // Get the query instance
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('id');
            $query->from('#__tj_region');
            $query->where('region=' . $this->db->quote($stateName));

            // Set the query and load result
            $this->db->setQuery($query);
            return $this->db->loadAssoc()['id'];
        } catch (Exception $e) {
            return '0';
        }
    }

    /* - ADDRESS
     * Function to save customer address
     * return array containig status as true
     */
    public function ecommSaveCustomerAddress($address)
    {
        // Get the address model and save the address
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_quick2cart/models');
        $addressModel = JModelLegacy::getInstance('Customer_AddressForm', 'Quick2cartModel');
        $result       = $addressModel->save($address);

        // If address save successful
        if ($result) {
            $this->returnData['success'] = 'true';
        }

        return $this->returnData;
    }

    /* - ADDRESS
     * Function to delete the customer address
     * return array containig status as true
     */
    public function ecommDeleteCustomerAddress($addressId)
    {
        // Load the address form model
        $addressFormModel = JModelLegacy::getInstance('Customer_AddressForm', 'Quick2cartModel');
        $result           = $addressFormModel->delete($addressId);

        // If address delete successful
        if ($result) {
            $this->returnData['success'] = 'true';
        }

        return $this->returnData;
    }

    /* - ADDRESS
     * Function to get all the customer addresses used in past
     * return array containig status as true and the addresses
     */
    public function ecommGetUserAddressList($userId = 0)
    {
        // If userId not provided then get logged in user's id
        if (!$userId) {
            $userId = JFactory::getUser()->id;
        }

        try
        {
            // Get the query Object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('*');
            $query->from('#__kart_customer_address');
            $query->where('user_id = ' . $userId);
            $query->order('id DESC');

            // Set the query and load result.
            $this->db->setQuery($query);
            $address = $this->db->loadObjectList();
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }

        if (!empty($userId)) {
            // Load the checkout model
            $cartCheckoutModel = JModelLegacy::getInstance('cartcheckout', 'Quick2cartModel');
            $userAddresses     = array();

            // Check if address is used as billing or shipping order
            if (!empty($address)) {
                foreach ($address as $item) {
                    $item->country_name = $cartCheckoutModel->getCountryName($item->country_code);
                    $item->state_name   = $cartCheckoutModel->getStateName($item->state_code);

                    $userAddresses[] = $item;
                }

                if (!empty($userAddresses)) {
                    $this->returnData['success']   = 'true';
                    $this->returnData['addresses'] = $userAddresses;
                }
            }
        }

        return $this->returnData;
    }

    /* - ADDRESS
     * Function to get store distance from users location
     * return array containig status as true and the distance details
     * google map api key = AIzaSyD2Glj1K120tqnUvw629PiK_SjNdSi83aU
     */
    public function getDistance($storeLocation, $userLocation, $unit = 'K')
    {
        //Get latitude and longitude from geo data
        $latitudeFrom  = $storeLocation['latitude'];
        $longitudeFrom = $storeLocation['longitude'];
        $latitudeTo    = $userLocation['latitude'];
        $longitudeTo   = $userLocation['longitude'];

        // Calculate distance from latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist  = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) + cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist  = acos($dist);
        $dist  = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit  = strtoupper($unit);
        $data  = array();

        if ($unit == "K") {
            $data['value'] = round(($miles * 1.609344), 2);
            $data['unit']  = 'KM';
        }

        return $data;
    }

    /* - ADDRESS
     * Function to save the address for the user/vendor
     * return array containig status as true/false
     */
    public function ecommSaveAddress($latitude, $longitude)
    {
        // Default user group
        $params             = JComponentHelper::getParams('com_ecomm');
        $googleAddressKey   = $params->get('googleAddressKey');
        $defaultAddressType = $params->get('addressType');

        if (empty($googleAddressKey)) {
            $this->returnData['success'] = 'false';
            $this->returnData['message'] = 'Please configure google address key.';
            return $this->returnData;
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($latitude) . ',' . trim($longitude) . '&sensor=false&key=' . $googleAddressKey;

        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        $jsonData = curl_exec($curlSession);
        curl_close($curlSession);

        $arrayData = json_decode($jsonData);

        $status = $arrayData->status;

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

            $userId             = JFactory::getUser()->id;
            $userDetails        = $userModel->getItem($userId);
            $userProfileDetails = JUserHelper::getProfile($userId);

            $addressData['phone']         = trim($userProfileDetails->profile['phone']);
            $addressData['user_email']    = trim($userDetails->email);
            $addressData['address_title'] = $defaultAddressType;
            $addressData['latitude']      = $latitude;
            $addressData['longitude']     = $longitude;
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

            $addressStatus = $this->ecommSaveCustomerAddress($addressData);
            if ($addressStatus['success'] == 'true') {
                $this->returnData['success'] = 'true';
            } else {
                $this->returnData['message'] = 'Failed to add the address in your addresses.';
            }
        } else {
            $this->returnData['message']    = 'Google api error';
            $this->returnData['debug_data'] = $jsonData;
        }
        return $this->returnData;
    }

    /* - ADDRESS
     * Function to get the shops for given address
     * return array containig status as true and the shop near given address
     */
    public function ecommGetShopsNearMe($latitude, $longitude)
    {
        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select(array('id', 'owner'))
                ->from($this->db->quoteName('#__kart_store'))
                ->where(
                    $this->db->quoteName('live') . " = " . $this->db->quote('1')
                );
            $this->db->setQuery($query);

            // Load the list of stores found
            $stores = $this->db->loadAssocList();
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }

        $i = $shopId = 0;

        // Default user group
        $params      = JComponentHelper::getParams('com_ecomm');
        $minDistance = $params->get('maxStoreDistance');

        // If stores found
        foreach ($stores as $store) {
            // Get the store's latitude, longitude
            $result = $this->ecommGetUserAddressList($store['owner']);

            if ($result['success'] == 'true') {
                $shopLocation              = array();
                $shopLocation['latitude']  = $result['addresses'][0]->latitude;
                $shopLocation['longitude'] = $result['addresses'][0]->longitude;
            }

            // Get the user's latitude, longitude
            $userLocation              = array();
            $userLocation['latitude']  = $latitude;
            $userLocation['longitude'] = $longitude;

            $distance = $this->getDistance($shopLocation, $userLocation, $unit = 'K')['value'];

            // Check if store near user address, If yes then save store id
            if ($distance < $minDistance) {
                $shopId = $stores[$i]['id'];
            }

            $i++;
        }

        $this->returnData            = array();
        $this->returnData['success'] = 'false';
        $this->returnData['shopId']  = '0';

        if ($shopId > 0) {
            $this->returnData['success'] = 'true';
            $this->returnData['shopId']  = (string) $shopId;
        }

        return $this->returnData;
    }
}
