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
 * Ecomm Store service class.
 *
 * @since  1.0
 */

class EcommStoreService
{
    public function __construct()
    {
        $this->db                    = JFactory::getDbo();
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
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
     * Function to get available coupon code list
     * return array containig status as true and the coupon code details
     */
    public function ecommGetCouponCodes()
    {
        $offers = $this->ecommGetShopOffers($shopId = 3, $published = 1);

        // Include helpers
        JLoader::import('promotion', JPATH_SITE . '/components/com_quick2cart/helpers');
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

    /* VENDOR - STORE
     * Function to Save the store
     * return array containig status as true and the store details
     */
    public function ecommUpdateStoreState($shopId, $status)
    {
        try
        {
            // Get the query instance
            $query = $this->db->getQuery(true);

            // Build the update query
            $query->update($this->db->quoteName('#__kart_store'))
                ->set($this->db->quoteName('live') . ' = ' . $this->db->quote((int) $status))
                ->where($this->db->quoteName('id') . ' = ' . $this->db->quote($shopId));

            $this->db->setQuery($query);

            // If successfully updated the status
            if ($this->db->execute()) {
                $this->returnData['success'] = 'true';
            }

            return $this->returnData;
        } catch (Exception $e) {
            return $this->returnData;
        }
    }

    /* TODO - STORE
     */
    public function ecommGetSingleShopDetails($shopId, $fields = '')
    {
        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
        $storeData                   = array();

        // Get the store details
        JLoader::register('storeHelper', JPATH_SITE . '/components/com_quick2cart/helpers/storeHelper.php');
        $storeHelper = new storeHelper;
        $result      = $storeHelper->getStoreDetail($shopId);

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
        $storeHelper = new storeHelper;
        $result      = $storeHelper->saveVendorDetails($input);

        if ($result['store_id']) {
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Store details saved successfully';
        } else {
            $this->returnData['message'] = 'Failed to save the store details';
        }

        return $this->returnData;
    }

    /* VENDOR - STORE
     * Function to get all orders income
     * return array containig status as true and icome details
     */
    public function getAllOrderIncome($shopId)
    {
         // Create db and query object
        $query = "SELECT FORMAT(SUM(amount), 2) FROM `ichal_kart_orders` WHERE `id` IN ( SELECT DISTINCT `order_id` FROM `ichal_kart_order_item` WHERE `store_id`='" . $shopId ."' ) AND `status` IN ('C', 'S', 'D')";

         try {
            $this->db->setQuery($query);
            $result = $this->db->loadResult();

            $this->returnData['success'] = 'true';
            $this->returnData['amount'] = $result;
            return $this->returnData;
         } catch (Exception $e) {
            return $this->returnData;
         }
    }
}
