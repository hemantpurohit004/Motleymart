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
class EcommApiResourceEcommGetProductsForShopAndCategory extends ApiResource
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
        JLoader::register('EcommProductService', JPATH_ADMINISTRATOR . '/components/com_ecomm/services/product.php');

        $service = new EcommProductService();

        // Get the request body and convert it into array
        $inputData = json_decode(file_get_contents('php://input'), true);

        $shopId     = $inputData['shopId'];
        $categoryId = $inputData['categoryId'];
        $keyword    = $inputData['keyword'];

        $filter = array('keyword' => $keyword);

        $data = $service->ecommGetProductsForShopAndCategory($shopId, $categoryId, $filter);

        $this->plugin->setResponse($data);
        return true;
    }
}
