<?php
/**
 * @package     Joomla.API.Plugin
 * @subpackage  com_tjlms-API
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.user.helper');

/**
 * API Plugin
 *
 * @package     Joomla_API_Plugin
 * @subpackage  com_tjlms-API-create course
 * @since       1.0
 */
class EcommApiResourceEcommGetPaymentMethods extends ApiResource
{
    /**
     * API Plugin for get method
     *
     * @return  avoid.
     */
    public function get()
    {
        $this->plugin->setResponse("Please Use Post method");
    }

    /**
     * API Plugin for post method
     *
     * @return  avoid.
     */
    public function post()
    {
        // Require helper file
        JLoader::register('EcommPaymentService', JPATH_ADMINISTRATOR . '/components/com_ecomm/services/payment.php');

        $service = new EcommPaymentService();

        // Get the request body and convert it into array
        $inputData = json_decode(file_get_contents('php://input'), true);

        $data = $service->ecommGetPaymentMethods();

        $this->plugin->setResponse($data);
        return true;
    }
}
