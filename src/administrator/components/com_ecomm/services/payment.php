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
 * Ecomm Payment service class.
 *
 * @since  1.0
 */

class EcommPaymentService
{
    public function __construct()
    {
        $this->db                    = JFactory::getDbo();
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
    }

    /*  - PAYMENT
     * Function to get the date
     * return date in the format Y-m-d H:i:s
     */
    public function getDate()
    {
        $timeZone = JFactory::getConfig()->get('offset');
        date_default_timezone_set($timeZone);
        return date("Y-m-d H:i:s");
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
     * Function to check value is null or not
     * return blank or inputed value
     */
    public function checkNull($value)
    {
        if ($value == null) {
            return '';
        } else {
            return $value;
        }
    }

    /* - PAYMENT
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

        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_quick2cart/models');
        $paymentModel = JModelLegacy::getInstance('payment', 'Quick2cartModel');

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
}
