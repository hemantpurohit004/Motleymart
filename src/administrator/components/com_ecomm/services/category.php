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
 * Ecomm Category service class.
 *
 * @since  1.0
 */

class EcommCategoryService
{
    public function __construct()
    {
        $this->db                    = JFactory::getDbo();
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
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
    }

    /* - CATEGORY
     * Function to get all the categories
     * return array containig status as true and the categories
     */
    public function ecommGetCategoriesByLevel($level, $parentId = 0)
    {
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
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }

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

    /* - CATEGORY
     * Function to get joomla's category details
     * return array containig status as true and the user detials
     */
    public function getCategoryDetails($categoryId)
    {
        try
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
        } catch (Exception $e) {
            return false;
        }

        // If category is exists - check by title exists
        if (isset($category['title']) && !empty($category['title'])) {
            return $category;
        }

        return false;
    }

    /* - CATEGORY
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
}
